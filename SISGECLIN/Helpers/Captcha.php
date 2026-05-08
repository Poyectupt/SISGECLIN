<?php

namespace App\Helpers;

class Captcha  
{
    public function verifyCaptcha($response)
    {
        // Reemplaza con la clave secreta de tu sitio en Google reCAPTCHA
        $secretKey = '6LdgkfcpAAAAAMCHp3gcCtCHwtsVkOQ5yP8h6JC2';

        // Verifica el captcha con la API de Google
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $secretKey,
            'response' => $response
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);

        return $response->success;
    }
}