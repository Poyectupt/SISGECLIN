<?php

namespace App\Models;

use App\Core\Database;

class InventarioMovimiento
{
    /**
     * Obtener todos los movimientos con paginación
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM v_movimientos_inventario";
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        return Database::fetchAll($sql);
    }

    /**
     * Obtener movimientos por medicamento
     */
    public function getByMedicamento($medicamentoId, $limit = null)
    {
        $sql = "SELECT * FROM v_movimientos_inventario WHERE medicamento_id = :medicamento_id ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return Database::fetchAll($sql, ['medicamento_id' => $medicamentoId]);
    }

    /**
     * Obtener movimientos por rango de fechas
     */
    public function getByDateRange($fechaInicio, $fechaFin, $tipo = null)
    {
        $sql = "SELECT * FROM v_movimientos_inventario 
                WHERE DATE(created_at) BETWEEN :fecha_inicio AND :fecha_fin";
        
        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        if ($tipo) {
            $sql .= " AND tipo_movimiento = :tipo";
            $params['tipo'] = $tipo;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }

    /**
     * Obtener estadísticas de movimientos por período
     */
    public function getEstadisticas($periodo = 'semanal')
    {
        $intervalo = $periodo === 'semanal' ? '7 DAY' : '30 DAY';
        
        $sql = "SELECT 
                    tipo_movimiento,
                    COUNT(*) as total_movimientos,
                    SUM(cantidad) as total_cantidad,
                    DATE(created_at) as fecha
                FROM inventario_movimientos
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$intervalo})
                GROUP BY tipo_movimiento, DATE(created_at)
                ORDER BY fecha DESC";
        
        return Database::fetchAll($sql);
    }

    /**
     * Registrar entrada de medicamento
     */
    public function registrarEntrada($medicamentoId, $cantidad, $motivo, $usuarioId)
    {
        // Obtener cantidad actual
        $medicamento = Database::fetch(
            "SELECT cantidad FROM inventario WHERE id = :id",
            ['id' => $medicamentoId]
        );
        
        if (!$medicamento) {
            throw new \Exception('Medicamento no encontrado');
        }
        
        $cantidadAnterior = $medicamento->cantidad;
        $cantidadNueva = $cantidadAnterior + $cantidad;
        
        // Actualizar inventario
        Database::query(
            "UPDATE inventario SET cantidad = :cantidad WHERE id = :id",
            ['cantidad' => $cantidadNueva, 'id' => $medicamentoId]
        );
        
        // Registrar movimiento
        Database::query(
            "INSERT INTO inventario_movimientos 
             (medicamento_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, usuario_id) 
             VALUES (:medicamento_id, 'entrada', :cantidad, :cantidad_anterior, :cantidad_nueva, :motivo, :usuario_id)",
            [
                'medicamento_id' => $medicamentoId,
                'cantidad' => $cantidad,
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => $cantidadNueva,
                'motivo' => $motivo,
                'usuario_id' => $usuarioId
            ]
        );
        
        return Database::lastInsertId();
    }

    /**
     * Registrar salida de medicamento
     */
    public function registrarSalida($medicamentoId, $cantidad, $motivo, $usuarioId, $consultaId = null)
    {
        // Obtener cantidad actual
        $medicamento = Database::fetch(
            "SELECT cantidad, nombre_medicamento FROM inventario WHERE id = :id",
            ['id' => $medicamentoId]
        );
        
        if (!$medicamento) {
            throw new \Exception('Medicamento no encontrado');
        }
        
        $cantidadAnterior = $medicamento->cantidad;
        
        if ($cantidadAnterior < $cantidad) {
            throw new \Exception("Stock insuficiente de {$medicamento->nombre_medicamento}. Disponible: {$cantidadAnterior}");
        }
        
        $cantidadNueva = $cantidadAnterior - $cantidad;
        
        // Actualizar inventario
        Database::query(
            "UPDATE inventario SET cantidad = :cantidad WHERE id = :id",
            ['cantidad' => $cantidadNueva, 'id' => $medicamentoId]
        );
        
        // Registrar movimiento
        Database::query(
            "INSERT INTO inventario_movimientos 
             (medicamento_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, consulta_id, usuario_id) 
             VALUES (:medicamento_id, 'salida', :cantidad, :cantidad_anterior, :cantidad_nueva, :motivo, :consulta_id, :usuario_id)",
            [
                'medicamento_id' => $medicamentoId,
                'cantidad' => $cantidad,
                'cantidad_anterior' => $cantidadAnterior,
                'cantidad_nueva' => $cantidadNueva,
                'motivo' => $motivo,
                'consulta_id' => $consultaId,
                'usuario_id' => $usuarioId
            ]
        );
        
        return Database::lastInsertId();
    }

    /**
     * Contar total de movimientos
     */
    public function count($tipo = null)
    {
        $sql = "SELECT COUNT(*) as total FROM inventario_movimientos";
        $params = [];
        
        if ($tipo) {
            $sql .= " WHERE tipo_movimiento = :tipo";
            $params['tipo'] = $tipo;
        }
        
        $result = Database::fetch($sql, $params);
        return $result ? $result->total : 0;
    }

    /**
     * Obtener movimientos recientes
     */
    public function getRecientes($limit = 10)
    {
        return Database::fetchAll(
            "SELECT * FROM v_movimientos_inventario ORDER BY created_at DESC LIMIT :limit",
            ['limit' => $limit]
        );
    }
}
