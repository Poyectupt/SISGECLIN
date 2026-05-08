<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    public function findByUsername($username)
    {
        return Database::fetch(
            "SELECT * FROM usuarios WHERE username = :username LIMIT 1",
            ['username' => $username]
        );
    }

    public function findById($id)
    {
        return Database::fetch(
            "SELECT id, username, email, password, rol, nombre_completo, activo, ultimo_acceso, created_at FROM usuarios WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function getAll()
    {
        return Database::fetchAll(
            "SELECT id, username, email, rol, nombre_completo, activo, ultimo_acceso, created_at FROM usuarios ORDER BY created_at DESC"
        );
    }

    public function getAllMedicos()
    {
        return Database::fetchAll(
            "SELECT id, nombre_completo FROM usuarios WHERE rol IN ('medico','admin') AND activo = 1 ORDER BY nombre_completo ASC"
        );
    }

    public function create($data)
    {
        $sql = "INSERT INTO usuarios (username, email, password, rol, nombre_completo, activo) 
                VALUES (:username, :email, :password, :rol, :nombre_completo, :activo)";
        Database::query($sql, [
            'username'       => $data['username'],
            'email'          => $data['email'],
            'password'       => password_hash($data['password'], PASSWORD_BCRYPT),
            'rol'            => $data['rol'] ?? 'recepcionista',
            'nombre_completo'=> $data['nombre_completo'] ?? '',
            'activo'         => $data['activo'] ?? 1,
        ]);
        return Database::lastInsertId();
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            if ($key === 'password' && !empty($value)) {
                $fields[] = "password = :password";
                $params['password'] = password_hash($value, PASSWORD_BCRYPT);
            } elseif ($key !== 'password') {
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }
        
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        return Database::query($sql, $params);
    }

    public function delete($id)
    {
        return Database::query("DELETE FROM usuarios WHERE id = :id", ['id' => $id]);
    }

    public function updateLastAccess($id)
    {
        Database::query("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id", ['id' => $id]);
    }

    /**
     * Obtener usuarios con paginación
     */
    public function getAllPaginated($limit = 15, $offset = 0)
    {
        return Database::fetchAll(
            "SELECT id, username, email, rol, nombre_completo, activo, ultimo_acceso, created_at 
             FROM usuarios 
             ORDER BY created_at DESC 
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    /**
     * Contar total de usuarios
     */
    public function count()
    {
        $result = Database::fetch("SELECT COUNT(*) as total FROM usuarios");
        return $result ? $result->total : 0;
    }
}
