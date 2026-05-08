<?php

namespace App\Models;

use App\Core\Database;

class Bitacora
{
    public static function registrar($usuarioId, $accion, $tabla = null, $registroId = null, $detalles = null)
    {
        $ip = self::obtenerIP();
        
        Database::query(
            "INSERT INTO bitacora (usuario_id, accion, tabla, registro_id, detalles, ip) 
             VALUES (:usuario_id, :accion, :tabla, :registro_id, :detalles, :ip)",
            [
                'usuario_id' => $usuarioId,
                'accion'     => $accion,
                'tabla'      => $tabla,
                'registro_id' => $registroId,
                'detalles'   => $detalles,
                'ip'         => $ip
            ]
        );
    }
    
    private static function obtenerIP()
    {
        $ip = '0.0.0.0';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Validar que sea una IP válida
        $ip = filter_var(trim($ip), FILTER_VALIDATE_IP);
        
        return $ip ?: '0.0.0.0';
    }

    public function getAll($limit = 50)
    {
        return Database::fetchAll(
            "SELECT b.*, u.nombre_completo, u.username 
             FROM bitacora b 
             JOIN usuarios u ON b.usuario_id = u.id 
             ORDER BY b.created_at DESC 
             LIMIT :limit",
            ['limit' => $limit]
        );
    }

    public function getByUsuario($usuarioId, $limit = 20)
    {
        return Database::fetchAll(
            "SELECT * FROM bitacora 
             WHERE usuario_id = :usuario_id 
             ORDER BY created_at DESC 
             LIMIT :limit",
            ['usuario_id' => $usuarioId, 'limit' => $limit]
        );
    }

    public function count()
    {
        $result = Database::fetch("SELECT COUNT(*) as total FROM bitacora");
        return $result ? $result->total : 0;
    }
}
