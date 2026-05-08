<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\Paciente;
use App\Models\Bitacora;

class PacientesController extends Controller
{
    private Paciente $model;

    public function __construct()
    {
        $this->model = new Paciente();
    }

    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        $csrfToken = Security::generateCSRFToken();
        $search    = Security::sanitize($_GET['search'] ?? '');
        
        // Paginación
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 30, 50]) ? $perPage : 15;
        $offset = ($page - 1) * $perPage;

        if ($search) {
            $pacientes = $this->model->searchPaginated($search, $perPage, $offset);
            $total = $this->model->countSearch($search);
        } else {
            $pacientes = $this->model->getAllPaginated($perPage, $offset);
            $total = $this->model->count();
        }
        
        $totalPages = ceil($total / $perPage);

        $this->view('dashboard', [
            'username'        => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol'             => $user['rol'],
            'csrf_token'      => $csrfToken,
            'module'          => 'pacientes',
            'pacientes'       => $pacientes,
            'search'          => $search,
            'pagination'      => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => $totalPages
            ]
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
            'nombre'           => trim($_POST['nombre'] ?? ''),
            'apellido'         => trim($_POST['apellido'] ?? ''),
            'cedula'           => trim($_POST['cedula'] ?? ''),
            'telefono'         => trim($_POST['telefono'] ?? '') ?: null,
            'email'            => trim($_POST['email'] ?? '') ?: null,
            'direccion'        => trim($_POST['direccion'] ?? '') ?: null,
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'genero'           => $_POST['genero'] ?? 'M',
            'tipo_sangre'      => trim($_POST['tipo_sangre'] ?? '') ?: null,
            'alergias'         => trim($_POST['alergias'] ?? '') ?: null,
        ];

        // Validaciones
        if (empty($data['nombre'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 422);
        }
        if (empty($data['apellido'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El apellido es requerido'], 422);
        }
        if (empty($data['cedula'])) {
            Security::jsonResponse(['success' => false, 'message' => 'La cédula es requerida'], 422);
        }

        try {
            $id = $this->model->create($data);
            Security::jsonResponse(['success' => true, 'message' => 'Paciente registrado correctamente', 'id' => $id]);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'La cédula ya está registrada' : 'Error al guardar el paciente';
            Security::jsonResponse(['success' => false, 'message' => $msg], 500);
        }
    }

    public function show($id)
    {
        Session::requireAuth();
        $paciente = $this->model->findById((int)$id);
        if (!$paciente) {
            Security::jsonResponse(['success' => false, 'message' => 'Paciente no encontrado'], 404);
        }
        Security::jsonResponse(['success' => true, 'data' => $paciente]);
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
            'nombre'           => trim($_POST['nombre'] ?? ''),
            'apellido'         => trim($_POST['apellido'] ?? ''),
            'cedula'           => trim($_POST['cedula'] ?? ''),
            'telefono'         => trim($_POST['telefono'] ?? '') ?: null,
            'email'            => trim($_POST['email'] ?? '') ?: null,
            'direccion'        => trim($_POST['direccion'] ?? '') ?: null,
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
            'genero'           => $_POST['genero'] ?? 'M',
            'tipo_sangre'      => trim($_POST['tipo_sangre'] ?? '') ?: null,
            'alergias'         => trim($_POST['alergias'] ?? '') ?: null,
        ];

        try {
            $this->model->update((int)$id, $data);
            Security::jsonResponse(['success' => true, 'message' => 'Paciente actualizado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al actualizar el paciente'], 500);
        }
    }

    public function delete($id)
    {
        Session::requireAuth();

        try {
            $this->model->delete((int)$id);
            Security::jsonResponse(['success' => true, 'message' => 'Paciente eliminado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al eliminar el paciente'], 500);
        }
    }

    /**
     * Exportar pacientes a CSV
     */
    public function exportar()
    {
        Session::requireAuth();
        
        // Exportar todos los pacientes
        $pacientes = $this->model->getAll();
        $filename = "pacientes_" . date('Y-m-d') . ".csv";
        
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
            'Teléfono',
            'Email',
            'Fecha Nacimiento',
            'Género',
            'Tipo Sangre',
            'Dirección',
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
                $p->genero === 'M' ? 'Masculino' : ($p->genero === 'F' ? 'Femenino' : 'Otro'),
                $p->tipo_sangre ?? '',
                $p->direccion ?? '',
                $p->alergias ?? '',
                date('d/m/Y H:i', strtotime($p->created_at ?? 'now'))
            ]);
        }
        
        fclose($output);
        exit;
    }
}
