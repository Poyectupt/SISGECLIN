<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Empleado;
use App\Models\Bitacora;

class ConsultasController extends Controller
{
    private Consulta $model;

    public function __construct()
    {
        $this->model = new Consulta();
    }

    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        $csrfToken = Security::generateCSRFToken();

        // Paginación
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 30, 50]) ? $perPage : 15;
        $offset  = ($page - 1) * $perPage;

        $consultas  = $this->model->getAllPaginated($perPage, $offset);
        $total      = $this->model->count();
        $totalPages = (int)ceil($total / $perPage);

        $pacientes = (new Paciente())->getAll();
        $medicos   = (new Empleado())->getMedicos();

        $this->view('dashboard', [
            'username'        => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol'             => $user['rol'],
            'csrf_token'      => $csrfToken,
            'module'          => 'consultas',
            'consultas'       => $consultas,
            'pacientes'       => $pacientes,
            'medicos'         => $medicos,
            'pagination'      => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => $totalPages,
            ],
        ]);
    }

    public function store()
    {
        Session::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $data = [
            'paciente_id' => (int)($_POST['paciente_id'] ?? 0),
            'fecha_hora'  => $_POST['fecha_hora'] ?? '',
            'motivo'      => trim($_POST['motivo'] ?? ''),
            'estado'      => $_POST['estado'] ?? 'pendiente',
        ];

        // Verificar si viene empleado_id o medico_id
        if (!empty($_POST['empleado_id'])) {
            $data['empleado_id'] = (int)$_POST['empleado_id'];
        } elseif (!empty($_POST['medico_id'])) {
            $data['medico_id'] = (int)$_POST['medico_id'];
        }

        if ($data['paciente_id'] <= 0) {
            Security::jsonResponse(['success' => false, 'message' => 'Seleccione un paciente'], 422);
        }
        if (empty($data['empleado_id']) && empty($data['medico_id'])) {
            Security::jsonResponse(['success' => false, 'message' => 'Seleccione un médico'], 422);
        }
        if (empty($data['fecha_hora'])) {
            Security::jsonResponse(['success' => false, 'message' => 'La fecha y hora son requeridas'], 422);
        }
        if (empty($data['motivo'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El motivo es requerido'], 422);
        }

        try {
            $id = $this->model->create($data);
            Security::jsonResponse(['success' => true, 'message' => 'Consulta registrada correctamente', 'id' => $id]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al guardar la consulta: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        Session::requireAuth();
        $consulta = $this->model->findById((int)$id);
        if (!$consulta) {
            Security::jsonResponse(['success' => false, 'message' => 'Consulta no encontrada'], 404);
        }
        Security::jsonResponse(['success' => true, 'data' => $consulta]);
    }

    public function update($id)
    {
        Session::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $data = [
            'paciente_id'  => (int)($_POST['paciente_id'] ?? 0),
            'fecha_hora'   => $_POST['fecha_hora'] ?? '',
            'motivo'       => trim($_POST['motivo'] ?? ''),
            'diagnostico'  => trim($_POST['diagnostico'] ?? '') ?: null,
            'tratamiento'  => trim($_POST['tratamiento'] ?? '') ?: null,
            'estado'       => $_POST['estado'] ?? 'pendiente',
        ];

        // Verificar si viene empleado_id o medico_id
        if (!empty($_POST['empleado_id'])) {
            $data['empleado_id'] = (int)$_POST['empleado_id'];
        } elseif (!empty($_POST['medico_id'])) {
            $data['medico_id'] = (int)$_POST['medico_id'];
        }

        try {
            $this->model->update((int)$id, $data);
            Security::jsonResponse(['success' => true, 'message' => 'Consulta actualizada correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al actualizar la consulta'], 500);
        }
    }

    public function delete($id)
    {
        Session::requireAuth();

        try {
            $this->model->delete((int)$id);
            Security::jsonResponse(['success' => true, 'message' => 'Consulta eliminada correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al eliminar la consulta'], 500);
        }
    }

    /**
     * Exportar consultas a CSV
     */
    public function exportar()
    {
        Session::requireAuth();
        
        $periodo = $_GET['periodo'] ?? 'todos';
        
        // Obtener consultas según el período
        if ($periodo === 'rango') {
            // Exportación por rango de fechas personalizado
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
            
            $consultas = $this->model->getByDateRange($fechaInicio, $fechaFin);
            $filename = "consultas_" . $fechaInicio . "_a_" . $fechaFin . ".csv";
        } elseif ($periodo === 'semanal') {
            $fechaInicio = date('Y-m-d', strtotime('-7 days'));
            $fechaFin = date('Y-m-d');
            $consultas = $this->model->getByDateRange($fechaInicio, $fechaFin);
            $filename = "consultas_semanal_" . date('Y-m-d') . ".csv";
        } elseif ($periodo === 'mensual') {
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            $fechaFin = date('Y-m-d');
            $consultas = $this->model->getByDateRange($fechaInicio, $fechaFin);
            $filename = "consultas_mensual_" . date('Y-m-d') . ".csv";
        } else {
            $consultas = $this->model->getAll();
            $filename = "consultas_todas_" . date('Y-m-d') . ".csv";
        }
        
        // Generar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
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
                $c->medico,
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
}
