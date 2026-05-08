<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\Medicamento;
use App\Models\Bitacora;

class MedicinaController extends Controller
{
    private Medicamento $model;

    public function __construct()
    {
        $this->model = new Medicamento();
    }

    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        $csrfToken = Security::generateCSRFToken();
        $search    = trim($_GET['search'] ?? '');
        
        // Paginación
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 30, 50]) ? $perPage : 15;
        $offset = ($page - 1) * $perPage;

        if ($search) {
            $medicamentos = $this->model->searchPaginated($search, $perPage, $offset);
            $total = $this->model->countSearch($search);
        } else {
            $medicamentos = $this->model->getAllPaginated($perPage, $offset);
            $total = $this->model->count();
        }
        
        $totalPages = ceil($total / $perPage);

        $this->view('dashboard', [
            'username'        => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol'             => $user['rol'],
            'csrf_token'      => $csrfToken,
            'module'          => 'medicina',
            'medicamentos'    => $medicamentos,
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
            'nombre_medicamento' => trim($_POST['nombre_medicamento'] ?? ''),
            'descripcion'        => trim($_POST['descripcion'] ?? '') ?: null,
            'cantidad'           => (int)($_POST['cantidad'] ?? 0),
            'unidad'             => trim($_POST['unidad'] ?? 'unidades'),
            'precio'             => (float)($_POST['precio'] ?? 0),
            'fecha_vencimiento'  => $_POST['fecha_vencimiento'] ?? null,
            'proveedor'          => trim($_POST['proveedor'] ?? '') ?: null,
            'stock_minimo'       => (int)($_POST['stock_minimo'] ?? 10),
        ];

        if (empty($data['nombre_medicamento'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El nombre del medicamento es requerido'], 422);
        }

        try {
            $id = $this->model->create($data);
            Security::jsonResponse(['success' => true, 'message' => 'Medicamento registrado correctamente', 'id' => $id]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al guardar el medicamento'], 500);
        }
    }

    public function show($id)
    {
        Session::requireAuth();
        $medicamento = $this->model->findById((int)$id);
        if (!$medicamento) {
            Security::jsonResponse(['success' => false, 'message' => 'Medicamento no encontrado'], 404);
        }
        Security::jsonResponse(['success' => true, 'data' => $medicamento]);
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
            'nombre_medicamento' => trim($_POST['nombre_medicamento'] ?? ''),
            'descripcion'        => trim($_POST['descripcion'] ?? '') ?: null,
            'cantidad'           => (int)($_POST['cantidad'] ?? 0),
            'unidad'             => trim($_POST['unidad'] ?? 'unidades'),
            'precio'             => (float)($_POST['precio'] ?? 0),
            'fecha_vencimiento'  => $_POST['fecha_vencimiento'] ?? null,
            'proveedor'          => trim($_POST['proveedor'] ?? '') ?: null,
            'stock_minimo'       => (int)($_POST['stock_minimo'] ?? 10),
        ];

        try {
            $this->model->update((int)$id, $data);
            Security::jsonResponse(['success' => true, 'message' => 'Medicamento actualizado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al actualizar el medicamento'], 500);
        }
    }

    public function delete($id)
    {
        Session::requireAuth();

        try {
            $this->model->delete((int)$id);
            Security::jsonResponse(['success' => true, 'message' => 'Medicamento eliminado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al eliminar el medicamento'], 500);
        }
    }
}
