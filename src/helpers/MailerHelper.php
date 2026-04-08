<?php
// src/helpers/MailerHelper.php

class MailerHelper
{
    /**
     * Envía un correo electrónico utilizando la API de Brevo (HTTPS) o PHPMailer (SMTP)
     * prioritizando la API si la llave está configurada.
     */
    public static function send($to_email, $to_name, $subject, $html_body)
    {
        // 1. Intentar con la API de Brevo si la llave existe
        if (!empty(BREVO_API_KEY)) {
            return self::sendViaBrevoApi($to_email, $to_name, $subject, $html_body);
        }

        // 2. Fallback a PHPMailer si no hay API Key (útil para desarrollo local)
        return self::sendViaPHPMailer($to_email, $to_name, $subject, $html_body);
    }

    private static function sendViaBrevoApi($to_email, $to_name, $subject, $html_body)
    {
        $url = 'https://api.brevo.com/v3/smtp/email';
        
        $data = [
            'sender' => [
                'name' => SMTP_NAME,
                'email' => SMTP_FROM
            ],
            'to' => [
                [
                    'email' => $to_email,
                    'name' => $to_name
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $html_body
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . BREVO_API_KEY,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("❌ Error en Brevo API: " . $error);
            return ['status' => false, 'message' => "Error de conexión con la API: " . $error];
        }

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['status' => true, 'message' => 'Enviado via Brevo API'];
        } else {
            // Logueamos los primeros caracteres para verificar en Render logs
            $partialKey = substr(BREVO_API_KEY, 0, 8) . "...";
            error_log("❌ Error respuesta Brevo API (Code $httpCode) con llave $partialKey: " . $response);
            
            // Decodificar el error de Brevo si es posible
            $errorInfo = json_decode($response, true);
            $msg = $errorInfo['message'] ?? $response;
            return ['status' => false, 'message' => "Error de Brevo ($httpCode): " . $msg];
        }
    }

    private static function sendViaPHPMailer($to_email, $to_name, $subject, $html_body)
    {
        // Asegurarse de tener PHPMailer disponible
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return ['status' => false, 'message' => 'Librería PHPMailer no encontrada.'];
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer();
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com;smtp.googlemail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->Timeout = 15;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ),
                'socket' => array(
                    'bindto' => '0.0.0.0:0'
                )
            );

            $mail->setFrom(SMTP_FROM, SMTP_NAME);
            $mail->addAddress($to_email, $to_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html_body;

            if ($mail->send()) {
                return ['status' => true, 'message' => 'Enviado via PHPMailer'];
            } else {
                return ['status' => false, 'message' => 'PHPMailer failed: ' . $mail->ErrorInfo];
            }
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'PHPMailer Exception: ' . $e->getMessage()];
        }
    }
}
