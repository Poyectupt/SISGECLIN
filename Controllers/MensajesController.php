<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Models\Mensaje;
use App\Models\User;

class MensajesController extends Controller
{
    private Mensaje $model;

    public function __construct()
    {
        $this->model = new Mensaje();
    }

    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        $csrfToken = Security::generateCSRFToken();

        $mensajesRecibidos = $this->model->getInbox($user['id']);
        $mensajesEnviados  = $this->model->getSent($user['id']);
        $usuarios          = (new User())->getAll();

        $this->view('dashboard', [
            'username'          => $user['username'],
            'nombre_completo'   => Session::get('nombre_completo') ?? $user['username'],
            'rol'               => $user['rol'],
            'csrf_token'        => $csrfToken,
            'module'            => 'mensajes',
            'mensajesRecibidos' => $mensajesRecibidos,
            'mensajesEnviados'  => $mensajesEnviados,
            'usuarios'          => $usuarios,
        ]);
    }

    public function store()
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
            'remitente_id'    => $user['id'],
            'destinatario_id' => (int)($_POST['destinatario_id'] ?? 0),
            'asunto'          => trim($_POST['asunto'] ?? ''),
            'contenido'       => trim($_POST['contenido'] ?? ''),
        ];

        if ($data['destinatario_id'] <= 0) {
            Security::jsonResponse(['success' => false, 'message' => 'Seleccione un destinatario'], 422);
        }
        if (empty($data['asunto'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El asunto es requerido'], 422);
        }
        if (empty($data['contenido'])) {
            Security::jsonResponse(['success' => false, 'message' => 'El mensaje es requerido'], 422);
        }

        try {
            $id = $this->model->create($data);
            Security::jsonResponse(['success' => true, 'message' => 'Mensaje enviado correctamente', 'id' => $id]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al enviar el mensaje'], 500);
        }
    }

    /**
     * Enviar mensaje a todos los usuarios del sistema
     */
    public function storeMasivo()
    {
        Session::requireAuth();
        $user = Session::getUser();

        // Solo administradores pueden enviar mensajes masivos
        if ($user['rol'] !== 'admin') {
            Security::jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Security::jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            Security::jsonResponse(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }

        $asunto    = trim($_POST['asunto'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $destinos  = $_POST['destinatarios'] ?? 'todos'; // 'todos' | 'medicos' | 'recepcionistas'

        if (empty($asunto)) {
            Security::jsonResponse(['success' => false, 'message' => 'El asunto es requerido'], 422);
        }
        if (empty($contenido)) {
            Security::jsonResponse(['success' => false, 'message' => 'El mensaje es requerido'], 422);
        }

        try {
            $userModel = new User();
            $todos = $userModel->getAll();

            // Filtrar según la selección
            $destinatarioIds = [];
            foreach ($todos as $u) {
                if ((int)$u->id === (int)$user['id']) continue; // no enviarse a sí mismo
                if (!$u->activo) continue;                       // solo usuarios activos

                if ($destinos === 'todos') {
                    $destinatarioIds[] = $u->id;
                } elseif ($destinos === 'medicos' && $u->rol === 'medico') {
                    $destinatarioIds[] = $u->id;
                } elseif ($destinos === 'recepcionistas' && $u->rol === 'recepcionista') {
                    $destinatarioIds[] = $u->id;
                } elseif ($destinos === 'admins' && $u->rol === 'admin') {
                    $destinatarioIds[] = $u->id;
                }
            }

            if (empty($destinatarioIds)) {
                Security::jsonResponse(['success' => false, 'message' => 'No hay destinatarios disponibles para el grupo seleccionado'], 422);
            }

            $enviados = $this->model->createMasivo($user['id'], $destinatarioIds, $asunto, $contenido);
            Security::jsonResponse([
                'success' => true,
                'message' => "Mensaje enviado a {$enviados} usuario(s) correctamente",
                'enviados' => $enviados
            ]);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al enviar el mensaje masivo'], 500);
        }
    }

    public function show($id)
    {
        Session::requireAuth();
        $mensaje = $this->model->findById((int)$id);
        if (!$mensaje) {
            Security::jsonResponse(['success' => false, 'message' => 'Mensaje no encontrado'], 404);
        }
        // Marcar como leído
        $this->model->markAsRead((int)$id);
        Security::jsonResponse(['success' => true, 'data' => $mensaje]);
    }

    public function markAsRead($id)
    {
        Session::requireAuth();
        try {
            $this->model->markAsRead((int)$id);
            Security::jsonResponse(['success' => true, 'message' => 'Mensaje marcado como leído']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error'], 500);
        }
    }

    public function delete($id)
    {
        Session::requireAuth();

        try {
            $this->model->delete((int)$id);
            Security::jsonResponse(['success' => true, 'message' => 'Mensaje eliminado']);
        } catch (\Throwable $e) {
            Security::jsonResponse(['success' => false, 'message' => 'Error al eliminar el mensaje'], 500);
        }
    }

    public function getUnreadCount()
    {
        Session::requireAuth();
        $user = Session::getUser();
        $count = $this->model->getUnreadCount($user['id']);
        Security::jsonResponse(['success' => true, 'count' => $count]);
    }
}
