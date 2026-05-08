<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\User;
use App\Models\Bitacora;

class PerfilController extends Controller
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        $csrfToken = Security::generateCSRFToken();
        
        $usuario = $this->model->findById($user['id']);

        $this->view('dashboard', [
            'username'        => $user['username'],
            'nombre_completo' => Session::get('nombre_completo') ?? $user['username'],
            'rol'             => $user['rol'],
            'csrf_token'      => $csrfToken,
            'module'          => 'perfil',
            'usuario'         => $usuario,
        ]);
    }

    public function update()
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $data = [
            'nombre_completo' => trim($_POST['nombre_completo'] ?? ''),
            'email'           => trim($_POST['email'] ?? ''),
        ];

        if (empty($data['nombre_completo'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El nombre es requerido'], 422);
        }

        // Solo admin puede cambiar rol y estado
        if ($user['rol'] === 'admin') {
            if (isset($_POST['rol'])) {
                $data['rol'] = $_POST['rol'];
            }
            if (isset($_POST['activo'])) {
                $data['activo'] = (int)$_POST['activo'];
            }
        }

        try {
            $this->model->update($user['id'], $data);
            
            // Actualizar sesión
            Session::set('nombre_completo', $data['nombre_completo']);
            
            Bitacora::registrar($user['id'], 'ACTUALIZAR PERFIL', 'usuarios', $user['id']);
            Security::jsonResponse(['success' => true, 'message' => 'Perfil actualizado correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al actualizar el perfil'], 500);
        }
    }

    public function password()
    {
        $this->cambiarPassword();
    }
    
    private function cambiarPassword()
    {
        Session::requireAuth();
        $user = Session::getUser();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNuevo  = $_POST['password_nuevo'] ?? '';
        $passwordConf   = $_POST['password_confirmar'] ?? '';

        if (empty($passwordActual) || empty($passwordNuevo) || empty($passwordConf)) {
            Security::jsonResponse(['success' => false, 'message' => 'Complete todos los campos'], 422);
        }

        if (strlen($passwordNuevo) < 6) {
            Security::jsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 422);
        }

        if ($passwordNuevo !== $passwordConf) {
            Security::jsonResponse(['success' => false, 'message' => 'Las contraseñas no coinciden'], 422);
        }

        // Verificar contraseña actual
        $usuario = $this->model->findById($user['id']);
        if (!$usuario || !password_verify($passwordActual, $usuario->password)) {
            Security::jsonResponse(['success' => false, 'message' => 'La contraseña actual es incorrecta'], 422);
        }

        try {
            $this->model->update($user['id'], ['password' => $passwordNuevo]);
            Bitacora::registrar($user['id'], 'CAMBIAR CONTRASEÑA', 'usuarios', $user['id']);
            Security::jsonResponse(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al cambiar la contraseña'], 500);
        }
    }
}
