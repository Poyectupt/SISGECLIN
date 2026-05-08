<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\User;
use App\Models\Bitacora;

class UsuariosController extends Controller
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index()
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        // Solo admin puede acceder
        if ($user['rol'] !== 'admin') {
            Security::redirect('index.php?module=dashboard');
        }
        
        $csrfToken = Security::generateCSRFToken();
        
        // Paginación
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 30, 50]) ? $perPage : 15;
        $offset = ($page - 1) * $perPage;
        
        $usuarios = $this->model->getAllPaginated($perPage, $offset);
        $total = $this->model->count();
        $totalPages = ceil($total / $perPage);

        $this->view('dashboard', [
            'username'        => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol'             => $user['rol'],
            'csrf_token'      => $csrfToken,
            'module'          => 'usuarios',
            'usuarios'        => $usuarios,
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
        $user = Session::getUser();
        
        if ($user['rol'] !== 'admin') {
            Security::jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $data = [
            'username'        => trim($_POST['username'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'password'        => $_POST['password'] ?? '',
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'rol'             => $_POST['rol'] ?? 'recepcionista',
            'activo'          => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        if (empty($data['username'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El usuario es requerido'], 422);
        }
        if (empty($data['email'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El email es requerido'], 422);
        }
        if (empty($data['password'])) {
            Security::jsonResponse(['success' => false, 'message' => 'La contraseña es requerida'], 422);
        }
        if (empty($data['nombre_completo'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El nombre completo es requerido'], 422);
        }

        try {
            $id = $this->model->create($data);
            Bitacora::registrar($user['id'], 'CREAR USUARIO', 'usuarios', $id, "Creó el usuario '{$data['username']}' con rol {$data['rol']}");
            Security::jsonResponse(['success' => true, 'message' => 'Usuario creado correctamente', 'id' => $id]);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'El usuario o email ya existe' : 'Error al crear el usuario';
            Security::jsonResponse(['success' => false, 'message' => $msg], 500);
        }
    }

    public function show($id)
    {
        Session::requireAuth();
        $usuario = $this->model->findById((int)$id);
        if (!$usuario) {
            Security::jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }
        Security::jsonResponse(['success' => true, 'data' => $usuario]);
    }

    public function update($id)
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($user['rol'] !== 'admin') {
            Security::jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $data = [
            'username'        => trim($_POST['username'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'rol'             => $_POST['rol'] ?? 'recepcionista',
            'activo'          => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        ];

        $password = $_POST['password'] ?? '';
        if (!empty($password)) {
            $data['password'] = $password;
        }

        try {
            $this->model->update((int)$id, $data);
            $detalles = "Editó el usuario '{$data['username']}'";
            if (!empty($data['password'])) $detalles .= " (contraseña cambiada)";
            Bitacora::registrar($user['id'], 'EDITAR USUARIO', 'usuarios', $id, $detalles);
            Security::jsonResponse(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al actualizar el usuario'], 500);
        }
    }

    public function delete($id)
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($user['rol'] !== 'admin') {
            Security::jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // No puede eliminarse a sí mismo
        if ((int)$id === (int)$user['id']) {
            Security::jsonResponse(['success' => false, 'message' => 'No puede eliminarse a sí mismo'], 403);
        }

        try {
            $usuario = $this->model->findById((int)$id);
            $this->model->delete((int)$id);
            Bitacora::registrar($user['id'], 'ELIMINAR USUARIO', 'usuarios', $id, "Eliminó el usuario '{$usuario->username}'");
            Security::jsonResponse(['success' => true, 'message' => 'Usuario eliminado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al eliminar el usuario'], 500);
        }
    }

    public function toggleEstado($id)
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($user['rol'] !== 'admin') {
            Security::jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // No puede desactivarse a sí mismo
        if ((int)$id === (int)$user['id']) {
            Security::jsonResponse(['success' => false, 'message' => 'No puede desactivarse a sí mismo'], 403);
        }

        try {
            $usuario = $this->model->findById((int)$id);
            if (!$usuario) {
                Security::jsonResponse(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }
            
            $nuevoEstado = $usuario->activo ? 0 : 1;
            $this->model->update((int)$id, ['activo' => $nuevoEstado]);
            
            $accion = $nuevoEstado ? 'ACTIVAR USUARIO' : 'DESACTIVAR USUARIO';
            $estadoTexto = $nuevoEstado ? 'activó' : 'desactivó';
            Bitacora::registrar($user['id'], $accion, 'usuarios', $id, "{$estadoTexto} el usuario '{$usuario->username}'");
            
            Security::jsonResponse([
                'success' => true, 
                'message' => $nuevoEstado ? 'Usuario activado' : 'Usuario desactivado',
                'activo' => $nuevoEstado
            ]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }
}
