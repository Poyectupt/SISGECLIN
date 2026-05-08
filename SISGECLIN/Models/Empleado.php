<?php

namespace App\Models;

use App\Core\Database;

class Empleado
{
    public function getAll()
    {
        return Database::fetchAll(
            "SELECT * FROM nomina ORDER BY created_at DESC"
        );
    }

    public function findById($id)
    {
        return Database::fetch(
            "SELECT * FROM nomina WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function create($data)
    {
        Database::query(
            "INSERT INTO nomina (nombre, apellido, cedula, cargo, departamento, salario, fecha_ingreso, telefono, email, estado) 
             VALUES (:nombre, :apellido, :cedula, :cargo, :departamento, :salario, :fecha_ingreso, :telefono, :email, :estado)",
            [
                'nombre'        => $data['nombre'],
                'apellido'      => $data['apellido'],
                'cedula'        => $data['cedula'],
                'cargo'         => $data['cargo'],
                'departamento'  => $data['departamento'],
                'salario'       => $data['salario'] ?? 0,
                'fecha_ingreso' => $data['fecha_ingreso'],
                'telefono'      => $data['telefono'] ?? null,
                'email'         => $data['email'] ?? null,
                'estado'        => $data['estado'] ?? 'activo',
            ]
        );
        return Database::lastInsertId();
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        return Database::query(
            "UPDATE nomina SET 
                nombre = :nombre, 
                apellido = :apellido, 
                cedula = :cedula, 
                cargo = :cargo, 
                departamento = :departamento, 
                salario = :salario, 
                fecha_ingreso = :fecha_ingreso, 
                telefono = :telefono, 
                email = :email, 
                estado = :estado 
             WHERE id = :id",
            $data
        );
    }

    public function delete($id)
    {
        return Database::query("DELETE FROM nomina WHERE id = :id", ['id' => $id]);
    }

    public function getByDepartamento($departamento)
    {
        return Database::fetchAll(
            "SELECT * FROM nomina WHERE departamento = :departamento ORDER BY nombre ASC",
            ['departamento' => $departamento]
        );
    }

    /**
     * Obtener solo el personal médico activo para asignar consultas
     * Incluye todos los cargos médicos y de apoyo clínico
     */
    public function getMedicos()
    {
        return Database::fetchAll(
            "SELECT id, nombre, apellido, cargo, departamento,
                    CONCAT(nombre, ' ', apellido) as nombre_completo
             FROM nomina
             WHERE estado = 'activo'
               AND (
                   cargo LIKE '%Médico%'
                OR cargo LIKE '%médico%'
                OR cargo LIKE '%Cirujano%'
                OR cargo LIKE '%Pediatra%'
                OR cargo LIKE '%Ginecólogo%'
                OR cargo LIKE '%Obstetra%'
                OR cargo LIKE '%Cardiólogo%'
                OR cargo LIKE '%Neurólogo%'
                OR cargo LIKE '%Traumatólogo%'
                OR cargo LIKE '%Oftalmólogo%'
                OR cargo LIKE '%Otorrinolaringólogo%'
                OR cargo LIKE '%Dermatólogo%'
                OR cargo LIKE '%Urólogo%'
                OR cargo LIKE '%Psiquiatra%'
                OR cargo LIKE '%Anestesiólogo%'
                OR cargo LIKE '%Radiólogo%'
                OR cargo LIKE '%Patólogo%'
                OR cargo LIKE '%Oncólogo%'
                OR cargo LIKE '%Endocrinólogo%'
                OR cargo LIKE '%Gastroenterólogo%'
                OR cargo LIKE '%Nefrólogo%'
                OR cargo LIKE '%Neumólogo%'
                OR cargo LIKE '%Reumatólogo%'
                OR cargo LIKE '%Hematólogo%'
                OR cargo LIKE '%Infectólogo%'
                OR cargo LIKE '%Internista%'
               )
             ORDER BY nombre ASC"
        );
    }

    public function search($term)
    {
        return Database::fetchAll(
            "SELECT * FROM nomina 
             WHERE nombre LIKE :term OR apellido LIKE :term OR cedula LIKE :term 
             ORDER BY created_at DESC",
            ['term' => "%{$term}%"]
        );
    }

    /**
     * Obtener empleados por rango de fechas
     */
    public function getByDateRange($fechaInicio, $fechaFin, $cargo = null)
    {
        if ($cargo) {
            return Database::fetchAll(
                "SELECT * FROM nomina 
                 WHERE DATE(created_at) BETWEEN :fecha_inicio AND :fecha_fin
                 AND cargo = :cargo
                 ORDER BY created_at DESC",
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'cargo' => $cargo
                ]
            );
        } else {
            return Database::fetchAll(
                "SELECT * FROM nomina 
                 WHERE DATE(created_at) BETWEEN :fecha_inicio AND :fecha_fin
                 ORDER BY created_at DESC",
                [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]
            );
        }
    }
}
