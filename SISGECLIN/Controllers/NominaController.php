<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\Empleado;
use App\Models\Bitacora;

class NominaController extends Controller
{
    private Empleado $model;

    public function __construct()
    {
        $this->model = new Empleado();
    }

    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        
        // Solo admin puede acceder
        if ($user['rol'] !== 'admin') {
            Security::redirect('index.php?module=dashboard');
        }
        
        $csrfToken = Security::generateCSRFToken();
        $search    = trim($_GET['search'] ?? '');

        $empleados = $search
            ? $this->model->search($search)
            : $this->model->getAll();

        $this->view('dashboard', [
            'username'        => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol'             => $user['rol'],
            'csrf_token'      => $csrfToken,
            'module'          => 'nomina',
            'empleados'       => $empleados,
            'search'          => $search,
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
            'nombre'        => trim($_POST['nombre'] ?? ''),
            'apellido'      => trim($_POST['apellido'] ?? ''),
            'cedula'        => trim($_POST['cedula'] ?? ''),
            'cargo'         => trim($_POST['cargo'] ?? ''),
            'departamento'  => trim($_POST['departamento'] ?? ''),
            'salario'       => (float)($_POST['salario'] ?? 0),
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? '',
            'telefono'      => trim($_POST['telefono'] ?? '') ?: null,
            'email'         => trim($_POST['email'] ?? '') ?: null,
            'estado'        => $_POST['estado'] ?? 'activo',
        ];

        if (empty($data['nombre'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 422);
        }
        if (empty($data['apellido'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El apellido es requerido'], 422);
        }
        if (empty($data['cedula'])) {
            Security::jsonResponse(['success' => false, 'message' => 'La cédula es requerida'], 422);
        }
        if (empty($data['cargo'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El cargo es requerido'], 422);
        }
        if (empty($data['departamento'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El departamento es requerido'], 422);
        }

        try {
            $id = $this->model->create($data);
            Security::jsonResponse(['success' => true, 'message' => 'Empleado registrado correctamente', 'id' => $id]);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'La cédula ya está registrada' : 'Error al guardar el empleado';
            Security::jsonResponse(['success' => false, 'message' => $msg], 500);
        }
    }

    public function show($id)
    {
        Session::requireAuth();
        $empleado = $this->model->findById((int)$id);
        if (!$empleado) {
            Security::jsonResponse(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }
        Security::jsonResponse(['success' => true, 'data' => $empleado]);
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
            'nombre'        => trim($_POST['nombre'] ?? ''),
            'apellido'      => trim($_POST['apellido'] ?? ''),
            'cedula'        => trim($_POST['cedula'] ?? ''),
            'cargo'         => trim($_POST['cargo'] ?? ''),
            'departamento'  => trim($_POST['departamento'] ?? ''),
            'salario'       => (float)($_POST['salario'] ?? 0),
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? '',
            'telefono'      => trim($_POST['telefono'] ?? '') ?: null,
            'email'         => trim($_POST['email'] ?? '') ?: null,
            'estado'        => $_POST['estado'] ?? 'activo',
        ];

        try {
            $this->model->update((int)$id, $data);
            Security::jsonResponse(['success' => true, 'message' => 'Empleado actualizado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al actualizar el empleado'], 500);
        }
    }

    public function delete($id)
    {
        Session::requireAuth();

        try {
            $this->model->delete((int)$id);
            Security::jsonResponse(['success' => true, 'message' => 'Empleado eliminado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al eliminar el empleado'], 500);
        }
    }

    /**
     * Exportar nómina a CSV
     */
    public function exportar()
    {
        Session::requireAuth();
        
        // Exportar todos los empleados
        $empleados = $this->model->getAll();
        $filename = "nomina_" . date('Y-m-d') . ".csv";
        
        // Generar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'ID',
            'Nombre',
            'Apellido',
            'Cédula',
            'Cargo',
            'Departamento',
            'Salario',
            'Fecha Ingreso',
            'Teléfono',
            'Email',
            'Estado',
            'Fecha Registro'
        ]);
        
        // Datos
        foreach ($empleados as $e) {
            fputcsv($output, [
                $e->id,
                $e->nombre,
                $e->apellido,
                $e->cedula,
                $e->cargo,
                $e->departamento,
                number_format($e->salario, 2, '.', ''),
                $e->fecha_ingreso ?? '',
                $e->telefono ?? '',
                $e->email ?? '',
                $e->estado === 'activo' ? 'Activo' : 'Inactivo',
                date('d/m/Y H:i', strtotime($e->created_at ?? 'now'))
            ]);
        }
        
        fclose($output);
        exit;
    }
}
