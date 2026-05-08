<?php

namespace App\Models;

use App\Core\Database;

class Medicamento
{
    public function getAll()
    {
        return Database::fetchAll(
            "SELECT * FROM inventario ORDER BY nombre_medicamento ASC"
        );
    }

    public function findById($id)
    {
        return Database::fetch(
            "SELECT * FROM inventario WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function create($data)
    {
        Database::query(
            "INSERT INTO inventario (nombre_medicamento, descripcion, cantidad, unidad, precio, fecha_vencimiento, proveedor, stock_minimo) 
             VALUES (:nombre_medicamento, :descripcion, :cantidad, :unidad, :precio, :fecha_vencimiento, :proveedor, :stock_minimo)",
            [
                'nombre_medicamento' => $data['nombre_medicamento'],
                'descripcion'        => $data['descripcion'] ?? null,
                'cantidad'           => $data['cantidad'] ?? 0,
                'unidad'             => $data['unidad'] ?? 'unidades',
                'precio'             => $data['precio'] ?? 0,
                'fecha_vencimiento'  => $data['fecha_vencimiento'] ?? null,
                'proveedor'          => $data['proveedor'] ?? null,
                'stock_minimo'       => $data['stock_minimo'] ?? 10,
            ]
        );
        return Database::lastInsertId();
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        return Database::query(
            "UPDATE inventario SET 
                nombre_medicamento = :nombre_medicamento, 
                descripcion = :descripcion, 
                cantidad = :cantidad, 
                unidad = :unidad, 
                precio = :precio, 
                fecha_vencimiento = :fecha_vencimiento, 
                proveedor = :proveedor, 
                stock_minimo = :stock_minimo 
             WHERE id = :id",
            $data
        );
    }

    public function delete($id)
    {
        // Primero eliminar movimientos relacionados
        Database::query("DELETE FROM inventario_movimientos WHERE medicamento_id = :id", ['id' => $id]);
        
        // Eliminar relaciones con consultas
        Database::query("DELETE FROM consultas_medicamentos WHERE medicamento_id = :id", ['id' => $id]);
        
        // Luego eliminar el medicamento
        return Database::query("DELETE FROM inventario WHERE id = :id", ['id' => $id]);
    }

    public function getLowStock()
    {
        return Database::fetchAll(
            "SELECT * FROM inventario WHERE cantidad <= stock_minimo ORDER BY cantidad ASC"
        );
    }

    public function getExpiringSoon($days = 30)
    {
        return Database::fetchAll(
            "SELECT * FROM inventario 
             WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY) 
             ORDER BY fecha_vencimiento ASC",
            ['days' => $days]
        );
    }

    public function search($term)
    {
        return Database::fetchAll(
            "SELECT * FROM inventario 
             WHERE nombre_medicamento LIKE :term OR proveedor LIKE :term 
             ORDER BY nombre_medicamento ASC",
            ['term' => "%{$term}%"]
        );
    }

    /**
     * Obtener todos los medicamentos con paginación
     */
    public function getAllPaginated($limit = 15, $offset = 0)
    {
        return Database::fetchAll(
            "SELECT * FROM inventario 
             ORDER BY nombre_medicamento ASC 
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    /**
     * Contar total de medicamentos
     */
    public function count()
    {
        $result = Database::fetch("SELECT COUNT(*) as total FROM inventario");
        return $result ? $result->total : 0;
    }

    /**
     * Buscar con paginación
     */
    public function searchPaginated($term, $limit = 15, $offset = 0)
    {
        return Database::fetchAll(
            "SELECT * FROM inventario 
             WHERE nombre_medicamento LIKE :term OR proveedor LIKE :term 
             ORDER BY nombre_medicamento ASC
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
            "SELECT COUNT(*) as total FROM inventario 
             WHERE nombre_medicamento LIKE :term OR proveedor LIKE :term",
            ['term' => "%{$term}%"]
        );
        return $result ? $result->total : 0;
    }
}
