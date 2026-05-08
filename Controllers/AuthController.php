<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\User;
use App\Models\Bitacora;

class AuthController extends Controller
{
    public function login()
    {
        Session::start();

        // Si ya está autenticado, redirigir al dashboard
        if (Session::isAuthenticated()) {
            Security::redirect('index.php');
        }

        $error = null;
        $csrfToken = Security::generateCSRFToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $csrfPost = $_POST['csrf_token'] ?? '';

            // Verificar CSRF
            if (!Security::verifyCSRFToken($csrfPost)) {
                $error = 'Token de seguridad inválido. Recargue la página.';
            } elseif (empty($username) || empty($password)) {
                $error = 'Ingrese usuario y contraseña.';
            } else {
                try {
                    $userModel = new User();
                    $user = $userModel->findByUsername($username);

                    if ($user) {
                        // Verificar si está activo
                        if (!$user->activo) {
                            $error = 'Su cuenta está desactivada. Contacte al administrador.';
                        } elseif (password_verify($password, $user->password)) {
                            // Login exitoso
                            Session::setUserData($user);
                            $userModel->updateLastAccess($user->id);
                            Bitacora::registrar($user->id, 'INICIO SESIÓN', 'usuarios', $user->id);
                            Security::redirect('index.php');
                        } else {
                            $error = 'Contraseña incorrecta.';
                        }
                    } else {
                        $error = 'Usuario no encontrado.';
                    }
                } catch (\Throwable $e) {
                    $error = 'Error del sistema: ' . $e->getMessage();
                }
            }
        }

        $this->view('login', [
            'error' => $error,
            'csrf_token' => $csrfToken
        ]);
    }

    public function logout()
    {
        $userId = Session::get('id');
        if ($userId) {
            Bitacora::registrar($userId, 'CIERRE SESIÓN', 'usuarios', $userId);
        }
        Session::destroy();
        Security::redirect('index.php');
    }
}
