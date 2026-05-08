<?php

namespace App\Core;

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function isAuthenticated()
    {
        return self::get('autenticado') === true;
    }

    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php');
            exit();
        }
    }

    public static function getUser()
    {
        return [
            'id' => self::get('id'),
            'username' => self::get('username'),
            'rol' => self::get('rol')
        ];
    }

    public static function setUserData($user)
    {
        self::set('autenticado', true);
        self::set('id', $user->id ?? null);
        self::set('username', $user->username ?? null);
        self::set('rol', $user->rol ?? 'recepcionista');
        self::set('nombre_completo', $user->nombre_completo ?? null);
    }
}

?>
