<?php

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function posaljiMail($to, $subject, $plainBody, $htmlBody)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.sendgrid.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'apikey'; // doslovno ovako
            $mail->Password = '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'; //api key
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('jelenazxy1@gmail.com', 'Splanner');
            $mail->addAddress($to);

             $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            throw new Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }
}
