<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Core\Database;
use App\Models\Bitacora;

class DashboardController extends Controller
{
    public function index()
    {
        Session::requireAuth();
        $user      = Session::getUser();
        $csrfToken = Security::generateCSRFToken();

        try {
            $stats = [
                'pacientes'  => (int)(Database::fetch("SELECT COUNT(*) as total FROM pacientes")->total ?? 0),
                'consultas'  => (int)(Database::fetch("SELECT COUNT(*) as total FROM consultas WHERE DATE(fecha_hora) = CURDATE()")->total ?? 0),
                'inventario' => (int)(Database::fetch("SELECT COUNT(*) as total FROM inventario WHERE cantidad <= stock_minimo")->total ?? 0),
                'mensajes'   => (int)(Database::fetch(
                    "SELECT COUNT(*) as total FROM mensajes WHERE destinatario_id = :id AND leido = 0",
                    ['id' => $user['id']]
                )->total ?? 0),
            ];

            $consultasRecientes = Database::fetchAll(
                "SELECT c.id, c.fecha_hora, c.motivo, c.estado,
                        p.nombre AS pac_nombre, p.apellido AS pac_apellido,
                        COALESCE(
                            CONCAT(e.nombre, ' ', e.apellido),
                            u.username
                        ) AS medico,
                        COALESCE(e.cargo, 'Médico') AS medico_cargo
                 FROM consultas c
                 JOIN pacientes p ON c.paciente_id = p.id
                 LEFT JOIN nomina e ON c.empleado_id = e.id
                 LEFT JOIN usuarios u ON c.medico_id = u.id
                 ORDER BY c.created_at DESC
                 LIMIT 4"
            );

            // Bitácora solo para admin
            $bitacora = null;
            if ($user['rol'] === 'admin') {
                $bitacora = Database::fetchAll(
                    "SELECT b.*, u.nombre_completo, u.username 
                     FROM bitacora b 
                     JOIN usuarios u ON b.usuario_id = u.id 
                     ORDER BY b.created_at DESC 
                     LIMIT 15"
                );
            }
        } catch (\Throwable $e) {
            $stats              = ['pacientes' => 0, 'consultas' => 0, 'inventario' => 0, 'mensajes' => 0];
            $consultasRecientes = [];
            $bitacora           = null;
        }

        $this->view('dashboard', [
            'username'           => $user['username']       ?? 'Usuario',
            'nombre_completo'    => Session::get('nombre_completo') ?? $user['username'],
            'rol'                => $user['rol']             ?? 'recepcionista',
            'stats'              => $stats,
            'consultasRecientes' => $consultasRecientes,
            'bitacora'           => $bitacora,
            'csrf_token'         => $csrfToken,
            'module'             => 'dashboard',
        ]);
    }
}
