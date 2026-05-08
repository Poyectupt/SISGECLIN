<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\InventarioMovimiento;
use App\Models\Medicamento;
use App\Models\Bitacora;

class InventarioMovimientosController extends Controller
{
    private InventarioMovimiento $model;
    private Medicamento $medicamentoModel;

    public function __construct()
    {
        $this->model = new InventarioMovimiento();
        $this->medicamentoModel = new Medicamento();
    }

    /**
     * Vista principal de movimientos
     */
    public function index()
    {
        Session::requireAuth();
        $user = Session::getUser();
        $csrfToken = Security::generateCSRFToken();
        
        // Filtros
        $periodo = $_GET['periodo'] ?? 'semanal';
        $tipo = $_GET['tipo'] ?? '';
        
        // Paginación
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 30, 50]) ? $perPage : 15;
        $offset = ($page - 1) * $perPage;
        
        $movimientos = $this->model->getAll($perPage, $offset);
        $total = $this->model->count($tipo ?: null);
        $totalPages = ceil($total / $perPage);
        
        // Estadísticas
        $estadisticas = $this->model->getEstadisticas($periodo);
        
        $this->view('dashboard', [
            'username' => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol' => $user['rol'],
            'csrf_token' => $csrfToken,
            'module' => 'inventario_movimientos',
            'movimientos' => $movimientos,
            'estadisticas' => $estadisticas,
            'periodo' => $periodo,
            'tipo_filtro' => $tipo,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages
            ]
        ]);
    }

    /**
     * Registrar entrada de medicamento
     */
    public function registrarEntrada()
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $medicamentoId = (int)($_POST['medicamento_id'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Entrada de inventario');

        if ($medicamentoId <= 0) {
            Security::jsonResponse(['success' => false, 'message' => 'Medicamento inválido'], 422);
        }

        if ($cantidad <= 0) {
            Security::jsonResponse(['success' => false, 'message' => 'La cantidad debe ser mayor a 0'], 422);
        }

        try {
            $movimientoId = $this->model->registrarEntrada($medicamentoId, $cantidad, $motivo, $user['id']);
            
            $medicamento = $this->medicamentoModel->findById($medicamentoId);
            Bitacora::registrar(
                $user['id'], 
                'ENTRADA INVENTARIO', 
                'inventario_movimientos', 
                $movimientoId, 
                "Entrada de {$cantidad} unidades de {$medicamento->nombre_medicamento}"
            );
            
            Security::jsonResponse([
                'success' => true, 
                'message' => 'Entrada registrada correctamente',
                'id' => $movimientoId
            ]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar salida de medicamento
     */
    public function registrarSalida()
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $medicamentoId = (int)($_POST['medicamento_id'] ?? 0);
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Salida de inventario');
        $consultaId = !empty($_POST['consulta_id']) ? (int)$_POST['consulta_id'] : null;

        if ($medicamentoId <= 0) {
            Security::jsonResponse(['success' => false, 'message' => 'Medicamento inválido'], 422);
        }

        if ($cantidad <= 0) {
            Security::jsonResponse(['success' => false, 'message' => 'La cantidad debe ser mayor a 0'], 422);
        }

        try {
            $movimientoId = $this->model->registrarSalida($medicamentoId, $cantidad, $motivo, $user['id'], $consultaId);
            
            $medicamento = $this->medicamentoModel->findById($medicamentoId);
            Bitacora::registrar(
                $user['id'], 
                'SALIDA INVENTARIO', 
                'inventario_movimientos', 
                $movimientoId, 
                "Salida de {$cantidad} unidades de {$medicamento->nombre_medicamento}"
            );
            
            Security::jsonResponse([
                'success' => true, 
                'message' => 'Salida registrada correctamente',
                'id' => $movimientoId
            ]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener movimientos por medicamento (AJAX)
     */
    public function getByMedicamento($id)
    {
        Session::requireAuth();
        
        try {
            $movimientos = $this->model->getByMedicamento((int)$id, 20);
            Security::jsonResponse(['success' => true, 'data' => $movimientos]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al obtener movimientos'], 500);
        }
    }

    /**
     * Obtener estadísticas (AJAX)
     */
    public function getEstadisticas()
    {
        Session::requireAuth();
        
        $periodo = $_GET['periodo'] ?? 'semanal';
        
        try {
            $estadisticas = $this->model->getEstadisticas($periodo);
            Security::jsonResponse(['success' => true, 'data' => $estadisticas]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Exportar movimientos a CSV
     */
    public function exportar()
    {
        Session::requireAuth();
        
        $periodo = $_GET['periodo'] ?? 'todos';
        $tipo = $_GET['tipo'] ?? '';
        
        // Obtener movimientos según el período
        if ($periodo === 'rango') {
            // Exportación por rango de fechas personalizado
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
            
            $movimientos = $this->model->getByDateRange($fechaInicio, $fechaFin, $tipo ?: null);
            $filename = "movimientos_" . $fechaInicio . "_a_" . $fechaFin . ".csv";
        } elseif ($periodo === 'semanal') {
            $fechaInicio = date('Y-m-d', strtotime('-7 days'));
            $fechaFin = date('Y-m-d');
            $movimientos = $this->model->getByDateRange($fechaInicio, $fechaFin, $tipo ?: null);
            $filename = "movimientos_semanal_" . date('Y-m-d') . ".csv";
        } elseif ($periodo === 'mensual') {
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            $fechaFin = date('Y-m-d');
            $movimientos = $this->model->getByDateRange($fechaInicio, $fechaFin, $tipo ?: null);
            $filename = "movimientos_mensual_" . date('Y-m-d') . ".csv";
        } else {
            $movimientos = $this->model->getAll(null, 0);
            $filename = "movimientos_todos_" . date('Y-m-d') . ".csv";
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
            'Fecha/Hora',
            'Medicamento',
            'Tipo Movimiento',
            'Cantidad',
            'Stock Anterior',
            'Stock Nuevo',
            'Motivo',
            'Usuario',
            'Consulta ID'
        ]);
        
        // Datos
        foreach ($movimientos as $m) {
            fputcsv($output, [
                $m->id,
                date('d/m/Y H:i', strtotime($m->created_at)),
                $m->nombre_medicamento,
                ucfirst($m->tipo_movimiento),
                $m->cantidad,
                $m->cantidad_anterior,
                $m->cantidad_nueva,
                $m->motivo ?? '',
                $m->usuario_nombre,
                $m->consulta_id ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}
