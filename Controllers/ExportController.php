<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Core\Database;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Medicamento;
use App\Models\Empleado;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportController extends Controller
{
    /**
     * Exportar consultas a PDF, Excel o CSV
     */
    public function exportarConsultas()
    {
        Session::requireAuth();
        
        $formato = $_GET['formato'] ?? 'pdf';
        $periodo = $_GET['periodo'] ?? 'todos';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        // Obtener datos
        $consultaModel = new Consulta();
        
        if ($periodo === 'rango') {
            $consultas = $consultaModel->getByDateRange($fechaInicio, $fechaFin);
            $titulo = "Consultas del {$fechaInicio} al {$fechaFin}";
        } elseif ($periodo === 'semanal') {
            $fechaInicio = date('Y-m-d', strtotime('-7 days'));
            $fechaFin = date('Y-m-d');
            $consultas = $consultaModel->getByDateRange($fechaInicio, $fechaFin);
            $titulo = "Consultas de la Última Semana";
        } elseif ($periodo === 'mensual') {
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            $fechaFin = date('Y-m-d');
            $consultas = $consultaModel->getByDateRange($fechaInicio, $fechaFin);
            $titulo = "Consultas del Último Mes";
        } else {
            $consultas = $consultaModel->getAll();
            $titulo = "Todas las Consultas";
        }
        
        switch ($formato) {
            case 'pdf':
                $this->exportarConsultasPDF($consultas, $titulo);
                break;
            case 'excel':
                $this->exportarConsultasExcel($consultas, $titulo);
                break;
            case 'csv':
                $this->exportarConsultasCSV($consultas, $titulo);
                break;
            default:
                Security::jsonResponse(['success' => false, 'message' => 'Formato no válido'], 400);
        }
    }

    /**
     * Exportar consultas a PDF profesional
     */
    private function exportarConsultasPDF($consultas, $titulo)
    {
        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
            ]);

            // Encabezado
            $html = $this->generarHTMLConsultasPDF($consultas, $titulo);
            
            $mpdf->WriteHTML($html);
            
            $filename = 'Consultas_' . date('Y-m-d_H-i-s') . '.pdf';
            $mpdf->Output($filename, 'D');
            exit;
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al generar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generar HTML para PDF de consultas
     */
    private function generarHTMLConsultasPDF($consultas, $titulo)
    {
        $fecha = date('d/m/Y H:i:s');
        $totalConsultas = count($consultas);
        
        $html = <<<HTML
        <style>
            body {
                font-family: Arial, sans-serif;
                color: #333;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: bold;
            }
            .header p {
                margin: 5px 0 0 0;
                font-size: 12px;
                opacity: 0.9;
            }
            .info-box {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
                font-size: 11px;
                color: #666;
            }
            .info-item {
                flex: 1;
            }
            .info-item strong {
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            thead {
                background-color: #f8f9fa;
                border-bottom: 2px solid #667eea;
            }
            th {
                padding: 12px;
                text-align: left;
                font-weight: bold;
                color: #333;
                font-size: 11px;
            }
            td {
                padding: 10px 12px;
                border-bottom: 1px solid #e9ecef;
                font-size: 10px;
            }
            tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .status {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-weight: bold;
                font-size: 9px;
            }
            .status-pendiente {
                background-color: #fff3cd;
                color: #856404;
            }
            .status-en_proceso {
                background-color: #cfe2ff;
                color: #084298;
            }
            .status-completada {
                background-color: #d1e7dd;
                color: #0f5132;
            }
            .status-cancelada {
                background-color: #f8d7da;
                color: #842029;
            }
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #e9ecef;
                text-align: center;
                font-size: 10px;
                color: #999;
            }
            .footer p {
                margin: 5px 0;
            }
        </style>

        <div class="header">
            <h1>📋 {$titulo}</h1>
            <p>SISGECLIN - Sistema de Gestión Clínica</p>
        </div>

        <div class="info-box">
            <div class="info-item">
                <strong>Fecha de Generación:</strong> {$fecha}
            </div>
            <div class="info-item">
                <strong>Total de Registros:</strong> {$totalConsultas}
            </div>
            <div class="info-item">
                <strong>Usuario:</strong> {$_SESSION['nombre_completo']}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Médico</th>
                    <th>Fecha/Hora</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
HTML;

        $i = 1;
        foreach ($consultas as $c) {
            $estado = $c->estado ?? 'pendiente';
            $estadoLabel = [
                'pendiente' => 'Pendiente',
                'en_proceso' => 'En Proceso',
                'completada' => 'Completada',
                'cancelada' => 'Cancelada'
            ][$estado] ?? ucfirst($estado);
            
            $motivo = htmlspecialchars(substr($c->motivo, 0, 50));
            $medico = htmlspecialchars($c->medico ?? '-');
            $paciente = htmlspecialchars($c->nombre . ' ' . $c->apellido);
            $cedula = htmlspecialchars($c->cedula);
            $fecha = date('d/m/Y H:i', strtotime($c->fecha_hora));
            
            $html .= <<<HTML
                <tr>
                    <td>{$i}</td>
                    <td>{$paciente}</td>
                    <td>{$cedula}</td>
                    <td>{$medico}</td>
                    <td>{$fecha}</td>
                    <td>{$motivo}</td>
                    <td><span class="status status-{$estado}">{$estadoLabel}</span></td>
                </tr>
HTML;
            $i++;
        }

        $html .= <<<HTML
            </tbody>
        </table>

        <div class="footer">
            <p><strong>SISGECLIN v1.0.0</strong> - Sistema de Gestión Clínica</p>
            <p>Este documento fue generado automáticamente por el sistema. Contiene información confidencial.</p>
            <p>© 2026 - Todos los derechos reservados</p>
        </div>
HTML;

        return $html;
    }

    /**
     * Exportar consultas a Excel profesional
     */
    private function exportarConsultasExcel($consultas, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Consultas');

            // Encabezado
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', $titulo);
            $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimensions(1)->setRowHeight(30);

            // Información
            $sheet->setCellValue('A2', 'Fecha de Generación:');
            $sheet->setCellValue('B2', date('d/m/Y H:i:s'));
            $sheet->setCellValue('D2', 'Total de Registros:');
            $sheet->setCellValue('E2', count($consultas));
            $sheet->getStyle('A2:E2')->getFont()->setSize(10)->setItalic(true);

            // Encabezados de tabla
            $headers = ['#', 'Paciente', 'Cédula', 'Médico', 'Fecha/Hora', 'Motivo', 'Estado'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '4', $header);
                $sheet->getStyle($col . '4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle($col . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
                $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $col++;
            }

            // Datos
            $row = 5;
            $i = 1;
            foreach ($consultas as $c) {
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValue('B' . $row, $c->nombre . ' ' . $c->apellido);
                $sheet->setCellValue('C' . $row, $c->cedula);
                $sheet->setCellValue('D' . $row, $c->medico ?? '-');
                $sheet->setCellValue('E' . $row, date('d/m/Y H:i', strtotime($c->fecha_hora)));
                $sheet->setCellValue('F' . $row, substr($c->motivo, 0, 50));
                $sheet->setCellValue('G' . $row, ucfirst(str_replace('_', ' ', $c->estado)));
                
                // Estilos alternados
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
                }
                
                $row++;
                $i++;
            }

            // Ajustar ancho de columnas
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(15);

            // Bordes
            $sheet->getStyle('A4:G' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Descargar
            $writer = new Xlsx($spreadsheet);
            $filename = 'Consultas_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al generar Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar consultas a CSV mejorado
     */
    private function exportarConsultasCSV($consultas, $titulo)
    {
        $filename = 'Consultas_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'ID',
            'Paciente',
            'Cédula',
            'Médico',
            'Fecha/Hora',
            'Motivo',
            'Diagnóstico',
            'Tratamiento',
            'Estado',
            'Fecha Registro'
        ]);
        
        // Datos
        foreach ($consultas as $c) {
            fputcsv($output, [
                $c->id,
                $c->nombre . ' ' . $c->apellido,
                $c->cedula,
                $c->medico ?? '-',
                $c->fecha_hora,
                $c->motivo,
                $c->diagnostico ?? '',
                $c->tratamiento ?? '',
                ucfirst(str_replace('_', ' ', $c->estado)),
                date('d/m/Y H:i', strtotime($c->created_at ?? $c->fecha_hora))
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exportar pacientes a PDF
     */
    public function exportarPacientes()
    {
        Session::requireAuth();
        
        $formato = $_GET['formato'] ?? 'pdf';
        $periodo = $_GET['periodo'] ?? 'todos';
        
        $pacienteModel = new Paciente();
        
        if ($periodo === 'semanal') {
            $pacientes = $pacienteModel->getByDateRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
            $titulo = "Pacientes Registrados en la Última Semana";
        } elseif ($periodo === 'mensual') {
            $pacientes = $pacienteModel->getByDateRange(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
            $titulo = "Pacientes Registrados en el Último Mes";
        } else {
            $pacientes = $pacienteModel->getAll();
            $titulo = "Todos los Pacientes";
        }
        
        switch ($formato) {
            case 'pdf':
                $this->exportarPacientesPDF($pacientes, $titulo);
                break;
            case 'excel':
                $this->exportarPacientesExcel($pacientes, $titulo);
                break;
            case 'csv':
                $this->exportarPacientesCSV($pacientes, $titulo);
                break;
            default:
                Security::jsonResponse(['success' => false, 'message' => 'Formato no válido'], 400);
        }
    }

    /**
     * Exportar pacientes a PDF
     */
    private function exportarPacientesPDF($pacientes, $titulo)
    {
        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
            ]);

            $html = $this->generarHTMLPacientesPDF($pacientes, $titulo);
            $mpdf->WriteHTML($html);
            
            $filename = 'Pacientes_' . date('Y-m-d_H-i-s') . '.pdf';
            $mpdf->Output($filename, 'D');
            exit;
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al generar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generar HTML para PDF de pacientes
     */
    private function generarHTMLPacientesPDF($pacientes, $titulo)
    {
        $fecha = date('d/m/Y H:i:s');
        $totalPacientes = count($pacientes);
        
        $html = <<<HTML
        <style>
            body {
                font-family: Arial, sans-serif;
                color: #333;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: bold;
            }
            .header p {
                margin: 5px 0 0 0;
                font-size: 12px;
                opacity: 0.9;
            }
            .info-box {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
                font-size: 11px;
                color: #666;
            }
            .info-item {
                flex: 1;
            }
            .info-item strong {
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            thead {
                background-color: #f8f9fa;
                border-bottom: 2px solid #667eea;
            }
            th {
                padding: 12px;
                text-align: left;
                font-weight: bold;
                color: #333;
                font-size: 11px;
            }
            td {
                padding: 10px 12px;
                border-bottom: 1px solid #e9ecef;
                font-size: 10px;
            }
            tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #e9ecef;
                text-align: center;
                font-size: 10px;
                color: #999;
            }
            .footer p {
                margin: 5px 0;
            }
        </style>

        <div class="header">
            <h1>👥 {$titulo}</h1>
            <p>SISGECLIN - Sistema de Gestión Clínica</p>
        </div>

        <div class="info-box">
            <div class="info-item">
                <strong>Fecha de Generación:</strong> {$fecha}
            </div>
            <div class="info-item">
                <strong>Total de Registros:</strong> {$totalPacientes}
            </div>
            <div class="info-item">
                <strong>Usuario:</strong> {$_SESSION['nombre_completo']}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Tipo Sangre</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
HTML;

        $i = 1;
        foreach ($pacientes as $p) {
            $nombre = htmlspecialchars($p->nombre . ' ' . $p->apellido);
            $cedula = htmlspecialchars($p->cedula);
            $telefono = htmlspecialchars($p->telefono ?? '-');
            $email = htmlspecialchars($p->email ?? '-');
            $tipoSangre = htmlspecialchars($p->tipo_sangre ?? '-');
            $fecha = date('d/m/Y', strtotime($p->created_at));
            
            $html .= <<<HTML
                <tr>
                    <td>{$i}</td>
                    <td>{$nombre}</td>
                    <td>{$cedula}</td>
                    <td>{$telefono}</td>
                    <td>{$email}</td>
                    <td>{$tipoSangre}</td>
                    <td>{$fecha}</td>
                </tr>
HTML;
            $i++;
        }

        $html .= <<<HTML
            </tbody>
        </table>

        <div class="footer">
            <p><strong>SISGECLIN v1.0.0</strong> - Sistema de Gestión Clínica</p>
            <p>Este documento fue generado automáticamente por el sistema. Contiene información confidencial.</p>
            <p>© 2026 - Todos los derechos reservados</p>
        </div>
HTML;

        return $html;
    }

    /**
     * Exportar pacientes a Excel
     */
    private function exportarPacientesExcel($pacientes, $titulo)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Pacientes');

            // Encabezado
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', $titulo);
            $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimensions(1)->setRowHeight(30);

            // Información
            $sheet->setCellValue('A2', 'Fecha de Generación:');
            $sheet->setCellValue('B2', date('d/m/Y H:i:s'));
            $sheet->setCellValue('D2', 'Total de Registros:');
            $sheet->setCellValue('E2', count($pacientes));
            $sheet->getStyle('A2:E2')->getFont()->setSize(10)->setItalic(true);

            // Encabezados de tabla
            $headers = ['#', 'Nombre', 'Cédula', 'Teléfono', 'Email', 'Tipo Sangre', 'Fecha Registro'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '4', $header);
                $sheet->getStyle($col . '4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet->getStyle($col . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
                $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $col++;
            }

            // Datos
            $row = 5;
            $i = 1;
            foreach ($pacientes as $p) {
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValue('B' . $row, $p->nombre . ' ' . $p->apellido);
                $sheet->setCellValue('C' . $row, $p->cedula);
                $sheet->setCellValue('D' . $row, $p->telefono ?? '-');
                $sheet->setCellValue('E' . $row, $p->email ?? '-');
                $sheet->setCellValue('F' . $row, $p->tipo_sangre ?? '-');
                $sheet->setCellValue('G' . $row, date('d/m/Y', strtotime($p->created_at)));
                
                // Estilos alternados
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
                }
                
                $row++;
                $i++;
            }

            // Ajustar ancho de columnas
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(15);

            // Bordes
            $sheet->getStyle('A4:G' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Descargar
            $writer = new Xlsx($spreadsheet);
            $filename = 'Pacientes_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al generar Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar pacientes a CSV
     */
    private function exportarPacientesCSV($pacientes, $titulo)
    {
        $filename = 'Pacientes_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'ID',
            'Nombre',
            'Apellido',
            'Cédula',
            'Teléfono',
            'Email',
            'Fecha Nacimiento',
            'Género',
            'Tipo Sangre',
            'Alergias',
            'Fecha Registro'
        ]);
        
        // Datos
        foreach ($pacientes as $p) {
            fputcsv($output, [
                $p->id,
                $p->nombre,
                $p->apellido,
                $p->cedula,
                $p->telefono ?? '',
                $p->email ?? '',
                $p->fecha_nacimiento ?? '',
                $p->genero ?? '',
                $p->tipo_sangre ?? '',
                $p->alergias ?? '',
                date('d/m/Y', strtotime($p->created_at))
            ]);
        }
        
        fclose($output);
        exit;
    }
}
