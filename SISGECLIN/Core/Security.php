<?php

namespace App\Core;

class Security
{
    private static $csrfToken = null;
    
    public static function generateCSRFToken()
    {
        Session::start();
        
        // Si ya tenemos el token en memoria, devolverlo
        if (self::$csrfToken !== null) {
            return self::$csrfToken;
        }
        
        // Si existe en la sesión, usarlo
        if (isset($_SESSION['csrf_token']) && !empty($_SESSION['csrf_token'])) {
            self::$csrfToken = $_SESSION['csrf_token'];
            return self::$csrfToken;
        }
        
        // Generar nuevo token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        self::$csrfToken = $_SESSION['csrf_token'];
        
        return self::$csrfToken;
    }

    public static function verifyCSRFToken($token)
    {
        Session::start();
        
        if (empty($token)) {
            return false;
        }
        
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateRequired($data, $fields)
    {
        $errors = [];
        foreach ($fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "El campo {$label} es requerido";
            }
        }
        return $errors;
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateLength($string, $min, $max)
    {
        $length = strlen($string);
        return $length >= $min && $length <= $max;
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }

    public static function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}

?>
