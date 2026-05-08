<?php

namespace App\Models;

use App\Core\Database;

class Mensaje
{
    public function getInbox($userId)
    {
        return Database::fetchAll(
            "SELECT m.*, u.nombre_completo as remitente_nombre 
             FROM mensajes m 
             JOIN usuarios u ON m.remitente_id = u.id 
             WHERE m.destinatario_id = :user_id 
             ORDER BY m.created_at DESC",
            ['user_id' => $userId]
        );
    }

    public function getSent($userId)
    {
        return Database::fetchAll(
            "SELECT m.*, u.nombre_completo as destinatario_nombre 
             FROM mensajes m 
             JOIN usuarios u ON m.destinatario_id = u.id 
             WHERE m.remitente_id = :user_id 
             ORDER BY m.created_at DESC",
            ['user_id' => $userId]
        );
    }

    public function findById($id)
    {
        return Database::fetch(
            "SELECT m.*, u.nombre_completo as remitente_nombre 
             FROM mensajes m 
             JOIN usuarios u ON m.remitente_id = u.id 
             WHERE m.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function create($data)
    {
        Database::query(
            "INSERT INTO mensajes (remitente_id, destinatario_id, asunto, contenido) 
             VALUES (:remitente_id, :destinatario_id, :asunto, :contenido)",
            $data
        );
        return Database::lastInsertId();
    }

    /**
     * Enviar el mismo mensaje a múltiples destinatarios
     * Retorna la cantidad de mensajes enviados
     */
    public function createMasivo($remitenteId, array $destinatarioIds, $asunto, $contenido)
    {
        $enviados = 0;
        foreach ($destinatarioIds as $destId) {
            $destId = (int)$destId;
            if ($destId <= 0 || $destId === (int)$remitenteId) continue;
            Database::query(
                "INSERT INTO mensajes (remitente_id, destinatario_id, asunto, contenido) 
                 VALUES (:remitente_id, :destinatario_id, :asunto, :contenido)",
                [
                    'remitente_id'    => $remitenteId,
                    'destinatario_id' => $destId,
                    'asunto'          => $asunto,
                    'contenido'       => $contenido,
                ]
            );
            $enviados++;
        }
        return $enviados;
    }

    public function markAsRead($id)
    {
        return Database::query("UPDATE mensajes SET leido = 1 WHERE id = :id", ['id' => $id]);
    }

    public function delete($id)
    {
        return Database::query("DELETE FROM mensajes WHERE id = :id", ['id' => $id]);
    }

    public function getUnreadCount($userId)
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM mensajes WHERE destinatario_id = :user_id AND leido = 0",
            ['user_id' => $userId]
        );
        return $result ? $result->total : 0;
    }
}
