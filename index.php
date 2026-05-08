<?php

require_once __DIR__ . '/Core/Session.php';
require_once __DIR__ . '/Core/Security.php';
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/Controller.php';

require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Models/Bitacora.php';
require_once __DIR__ . '/Models/Paciente.php';
require_once __DIR__ . '/Models/Consulta.php';
require_once __DIR__ . '/Models/Medicamento.php';
require_once __DIR__ . '/Models/InventarioMovimiento.php';
require_once __DIR__ . '/Models/Empleado.php';
require_once __DIR__ . '/Models/Mensaje.php';

require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/DashboardController.php';
require_once __DIR__ . '/Controllers/PacientesController.php';
require_once __DIR__ . '/Controllers/ConsultasController.php';
require_once __DIR__ . '/Controllers/MedicinaController.php';
require_once __DIR__ . '/Controllers/InventarioMovimientosController.php';
require_once __DIR__ . '/Controllers/NominaController.php';
require_once __DIR__ . '/Controllers/MensajesController.php';
require_once __DIR__ . '/Controllers/UsuariosController.php';
require_once __DIR__ . '/Controllers/PerfilController.php';
require_once __DIR__ . '/Controllers/ExportController.php';

use App\Core\Session;
use App\Core\Security;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PacientesController;
use App\Controllers\ConsultasController;
use App\Controllers\MedicinaController;
use App\Controllers\InventarioMovimientosController;
use App\Controllers\NominaController;
use App\Controllers\MensajesController;
use App\Controllers\UsuariosController;
use App\Controllers\PerfilController;
use App\Controllers\ExportController;

Session::start();

// Logout
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    Session::destroy();
    Security::redirect('index.php');
}

// Routing
$module = $_GET['module'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$id     = $_GET['id'] ?? null;

// Si no está autenticado, solo permitir login
if (!Session::isAuthenticated() && $module !== 'auth') {
    $module = 'auth';
    $action = 'login';
}

try {
    switch ($module) {
        case 'auth':
            $controller = new AuthController();
            if ($action === 'login') {
                $controller->login();
            } elseif ($action === 'logout') {
                $controller->logout();
            }
            break;

        case 'dashboard':
            $controller = new DashboardController();
            $controller->index();
            break;

        case 'pacientes':
            $controller = new PacientesController();
            switch ($action) {
                case 'store':    $controller->store(); break;
                case 'update':   $controller->update($id); break;
                case 'delete':   $controller->delete($id); break;
                case 'show':     $controller->show($id); break;
                case 'exportar': $controller->exportar(); break;
                default:         $controller->index();
            }
            break;

        case 'consultas':
            $controller = new ConsultasController();
            switch ($action) {
                case 'store':    $controller->store(); break;
                case 'update':   $controller->update($id); break;
                case 'delete':   $controller->delete($id); break;
                case 'show':     $controller->show($id); break;
                case 'exportar': $controller->exportar(); break;
                default:         $controller->index();
            }
            break;

        case 'medicina':
            $controller = new MedicinaController();
            switch ($action) {
                case 'store':  $controller->store(); break;
                case 'update': $controller->update($id); break;
                case 'delete': $controller->delete($id); break;
                case 'show':   $controller->show($id); break;
                default:       $controller->index();
            }
            break;

        case 'inventario_movimientos':
            $controller = new InventarioMovimientosController();
            switch ($action) {
                case 'registrarEntrada': $controller->registrarEntrada(); break;
                case 'registrarSalida':  $controller->registrarSalida(); break;
                case 'getByMedicamento': $controller->getByMedicamento($id); break;
                case 'getEstadisticas':  $controller->getEstadisticas(); break;
                case 'exportar':         $controller->exportar(); break;
                default:                 $controller->index();
            }
            break;

        case 'mensajes':
            $controller = new MensajesController();
            switch ($action) {
                case 'store':       $controller->store(); break;
                case 'storeMasivo': $controller->storeMasivo(); break;
                case 'read':        $controller->markAsRead($id); break;
                case 'delete':      $controller->delete($id); break;
                case 'show':        $controller->show($id); break;
                default:            $controller->index();
            }
            break;

        case 'nomina':
            $controller = new NominaController();
            switch ($action) {
                case 'store':    $controller->store(); break;
                case 'update':   $controller->update($id); break;
                case 'delete':   $controller->delete($id); break;
                case 'show':     $controller->show($id); break;
                case 'exportar': $controller->exportar(); break;
                default:         $controller->index();
            }
            break;

        case 'usuarios':
            $controller = new UsuariosController();
            switch ($action) {
                case 'store':  $controller->store(); break;
                case 'update': $controller->update($id); break;
                case 'delete': $controller->delete($id); break;
                case 'show':   $controller->show($id); break;
                case 'toggle': $controller->toggleEstado($id); break;
                default:       $controller->index();
            }
            break;

        case 'perfil':
            $controller = new PerfilController();
            switch ($action) {
                case 'update':   $controller->update(); break;
                case 'password': $controller->password(); break;
                default:         $controller->index();
            }
            break;

        case 'export':
            $controller = new ExportController();
            switch ($action) {
                case 'consultas': $controller->exportarConsultas(); break;
                case 'pacientes': $controller->exportarPacientes(); break;
                default:          $controller->exportarConsultas();
            }
            break;

        default:
            $controller = new DashboardController();
            $controller->index();
    }
} catch (\Throwable $e) {
    error_log($e->getMessage());
    die('Error del sistema. Por favor intente más tarde.');
}
