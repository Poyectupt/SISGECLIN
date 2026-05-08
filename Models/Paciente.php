<?php

namespace App\Models;

use App\Core\Database;

class Paciente
{
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM pacientes ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        return Database::fetchAll($sql);
    }

    public function findById($id)
    {
        return Database::fetch(
            "SELECT * FROM pacientes WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function create($data)
    {
        $sql = "INSERT INTO pacientes (nombre, apellido, cedula, telefono, email, direccion, fecha_nacimiento, genero, tipo_sangre, alergias) 
                VALUES (:nombre, :apellido, :cedula, :telefono, :email, :direccion, :fecha_nacimiento, :genero, :tipo_sangre, :alergias)";
        Database::query($sql, [
            'nombre'           => $data['nombre'],
            'apellido'         => $data['apellido'],
            'cedula'           => $data['cedula'],
            'telefono'         => $data['telefono'] ?? null,
            'email'            => $data['email'] ?? null,
            'direccion'        => $data['direccion'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'genero'           => $data['genero'] ?? 'M',
            'tipo_sangre'      => $data['tipo_sangre'] ?? null,
            'alergias'         => $data['alergias'] ?? null,
        ]);
        return Database::lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE pacientes SET 
                nombre = :nombre, 
                apellido = :apellido, 
                cedula = :cedula, 
                telefono = :telefono, 
                email = :email, 
                direccion = :direccion, 
                fecha_nacimiento = :fecha_nacimiento, 
                genero = :genero,
                tipo_sangre = :tipo_sangre,
                alergias = :alergias
                WHERE id = :id";
        $data['id'] = $id;
        return Database::query($sql, $data);
    }

    public function delete($id)
    {
        // Primero eliminar consultas relacionadas
        Database::query("DELETE FROM consultas WHERE paciente_id = :id", ['id' => $id]);
        
        // Luego eliminar el paciente
        return Database::query("DELETE FROM pacientes WHERE id = :id", ['id' => $id]);
    }

    public function search($term)
    {
        return Database::fetchAll(
            "SELECT * FROM pacientes 
             WHERE nombre LIKE :term OR apellido LIKE :term OR cedula LIKE :term 
             ORDER BY created_at DESC",
            ['term' => "%{$term}%"]
        );
    }

    public function count()
    {
        $result = Database::fetch("SELECT COUNT(*) as total FROM pacientes");
        return $result ? $result->total : 0;
    }

    /**
     * Obtener pacientes con paginación
     */
    public function getAllPaginated($limit = 15, $offset = 0)
    {
        return Database::fetchAll(
            "SELECT * FROM pacientes 
             ORDER BY created_at DESC 
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    /**
     * Buscar con paginación
     */
    public function searchPaginated($term, $limit = 15, $offset = 0)
    {
        return Database::fetchAll(
            "SELECT * FROM pacientes 
             WHERE nombre LIKE :term OR apellido LIKE :term OR cedula LIKE :term 
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['term' => "%{$term}%"]
        );
    }

    /**
     * Contar resultados de búsqueda
     */
    public function countSearch($term)
    {
        $result = Database::fetch(
            "SELECT COUNT(*) as total FROM pacientes 
             WHERE nombre LIKE :term OR apellido LIKE :term OR cedula LIKE :term",
            ['term' => "%{$term}%"]
        );
        return $result ? $result->total : 0;
    }

    /**
     * Obtener pacientes por rango de fechas
     */
    public function getByDateRange($fechaInicio, $fechaFin)
    {
        return Database::fetchAll(
            "SELECT * FROM pacientes 
             WHERE DATE(created_at) BETWEEN :fecha_inicio AND :fecha_fin
             ORDER BY created_at DESC",
            [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]
        );
    }
}
