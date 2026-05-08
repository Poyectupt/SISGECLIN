<?php

namespace App\Models;

use App\Core\Database;

class Consulta
{
    public function getAll()
    {
        return Database::fetchAll(
            "SELECT c.*,
                    p.nombre, p.apellido, p.cedula,
                    COALESCE(
                        CONCAT(e.nombre, ' ', e.apellido),
                        u.username
                    ) as medico,
                    COALESCE(e.cargo, 'Médico') as medico_cargo
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             LEFT JOIN nomina e ON c.empleado_id = e.id
             LEFT JOIN usuarios u ON c.medico_id = u.id
             ORDER BY c.fecha_hora DESC"
        );
    }

    public function findById($id)
    {
        return Database::fetch(
            "SELECT c.*,
                    p.nombre, p.apellido, p.cedula,
                    p.telefono, p.email, p.tipo_sangre, p.alergias,
                    COALESCE(
                        CONCAT(e.nombre, ' ', e.apellido),
                        u.username
                    ) as medico,
                    COALESCE(e.cargo, 'Médico') as medico_cargo,
                    e.departamento as medico_departamento
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             LEFT JOIN nomina e ON c.empleado_id = e.id
             LEFT JOIN usuarios u ON c.medico_id = u.id
             WHERE c.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function create($data)
    {
        // Si viene empleado_id, usarlo; si no, usar medico_id
        if (!empty($data['empleado_id'])) {
            Database::query(
                "INSERT INTO consultas (paciente_id, empleado_id, fecha_hora, motivo, estado)
                 VALUES (:paciente_id, :empleado_id, :fecha_hora, :motivo, :estado)",
                [
                    'paciente_id' => $data['paciente_id'],
                    'empleado_id' => $data['empleado_id'],
                    'fecha_hora'  => $data['fecha_hora'],
                    'motivo'      => $data['motivo'],
                    'estado'      => $data['estado'] ?? 'pendiente',
                ]
            );
        } else {
            Database::query(
                "INSERT INTO consultas (paciente_id, medico_id, fecha_hora, motivo, estado)
                 VALUES (:paciente_id, :medico_id, :fecha_hora, :motivo, :estado)",
                [
                    'paciente_id' => $data['paciente_id'],
                    'medico_id'   => $data['medico_id'] ?? null,
                    'fecha_hora'  => $data['fecha_hora'],
                    'motivo'      => $data['motivo'],
                    'estado'      => $data['estado'] ?? 'pendiente',
                ]
            );
        }
        return Database::lastInsertId();
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        
        // Si viene empleado_id, actualizar con empleado_id
        if (isset($data['empleado_id'])) {
            return Database::query(
                "UPDATE consultas SET
                    paciente_id  = :paciente_id,
                    empleado_id  = :empleado_id,
                    medico_id    = NULL,
                    fecha_hora   = :fecha_hora,
                    motivo       = :motivo,
                    diagnostico  = :diagnostico,
                    tratamiento  = :tratamiento,
                    estado       = :estado
                 WHERE id = :id",
                $data
            );
        } else {
            // Si no viene empleado_id, mantener medico_id
            return Database::query(
                "UPDATE consultas SET
                    paciente_id  = :paciente_id,
                    fecha_hora   = :fecha_hora,
                    motivo       = :motivo,
                    diagnostico  = :diagnostico,
                    tratamiento  = :tratamiento,
                    estado       = :estado
                 WHERE id = :id",
                $data
            );
        }
    }

    public function delete($id)
    {
        return Database::query("DELETE FROM consultas WHERE id = :id", ['id' => $id]);
    }

    public function getByFecha($fecha)
    {
        return Database::fetchAll(
            "SELECT c.*, p.nombre, p.apellido
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             WHERE DATE(c.fecha_hora) = :fecha
             ORDER BY c.fecha_hora ASC",
            ['fecha' => $fecha]
        );
    }

    public function getByPaciente($paciente_id)
    {
        return Database::fetchAll(
            "SELECT * FROM consultas WHERE paciente_id = :paciente_id ORDER BY fecha_hora DESC",
            ['paciente_id' => $paciente_id]
        );
    }

    public function count()
    {
        $result = Database::fetch("SELECT COUNT(*) as total FROM consultas");
        return $result ? $result->total : 0;
    }

    public function getConsultasHoy()
    {
        return Database::fetchAll(
            "SELECT c.*, p.nombre, p.apellido
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             WHERE DATE(c.fecha_hora) = CURDATE()
             ORDER BY c.fecha_hora ASC"
        );
    }

    public function countConsultasHoy()
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM consultas WHERE DATE(fecha_hora) = CURDATE()"
        );
        return $result ? $result->total : 0;
    }

    public function getRecientes($limit = 4)
    {
        return Database::fetchAll(
            "SELECT c.*,
                    p.nombre as pac_nombre,
                    p.apellido as pac_apellido,
                    COALESCE(
                        CONCAT(e.nombre, ' ', e.apellido),
                        u.username
                    ) as medico
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             LEFT JOIN nomina e ON c.empleado_id = e.id
             LEFT JOIN usuarios u ON c.medico_id = u.id
             ORDER BY c.created_at DESC
             LIMIT :limit",
            ['limit' => $limit]
        );
    }

    public function getEstadisticas($periodo = 'semanal')
    {
        $intervalo = $periodo === 'semanal' ? '7 DAY' : '30 DAY';
        return Database::fetchAll(
            "SELECT
                DATE(fecha_hora) as fecha,
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                SUM(CASE WHEN estado = 'pendiente'  THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'cancelada'  THEN 1 ELSE 0 END) as canceladas
             FROM consultas
             WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL {$intervalo})
             GROUP BY DATE(fecha_hora)
             ORDER BY fecha DESC"
        );
    }

    public function getAllPaginated($limit = 15, $offset = 0)
    {
        return Database::fetchAll(
            "SELECT c.*,
                    p.nombre, p.apellido, p.cedula,
                    COALESCE(
                        CONCAT(e.nombre, ' ', e.apellido),
                        u.username
                    ) as medico,
                    COALESCE(e.cargo, 'Médico') as medico_cargo
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             LEFT JOIN nomina e ON c.empleado_id = e.id
             LEFT JOIN usuarios u ON c.medico_id = u.id
             ORDER BY c.fecha_hora DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    public function getByDateRange($fechaInicio, $fechaFin)
    {
        return Database::fetchAll(
            "SELECT c.*,
                    p.nombre, p.apellido, p.cedula,
                    COALESCE(
                        CONCAT(e.nombre, ' ', e.apellido),
                        u.username
                    ) as medico,
                    COALESCE(e.cargo, 'Médico') as medico_cargo
             FROM consultas c
             JOIN pacientes p ON c.paciente_id = p.id
             LEFT JOIN nomina e ON c.empleado_id = e.id
             LEFT JOIN usuarios u ON c.medico_id = u.id
             WHERE DATE(c.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
             ORDER BY c.fecha_hora DESC",
            [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin'    => $fechaFin,
            ]
        );
    }
}
