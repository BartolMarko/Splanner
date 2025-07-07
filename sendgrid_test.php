<?php
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.sendgrid.net';
    $mail->SMTPAuth = true;
    $mail->Username = 'apikey';
    $mail->Password = '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';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('jelenazxy1@gmail.com', 'Splanner');
    $mail->addAddress('jzaja.math@pmf.hr'); // stavi neku drugu adresu da vidiš dolazi li

    $mail->isHTML(false);
    $mail->Subject = 'Test SendGrid';
    $mail->Body = 'Ovo je test poruka.';

    $mail->SMTPDebug = 2; // da vidiš cijeli log

    $mail->send();
    echo "Mail poslan.";
} catch (Exception $e) {
    echo "Greška kod slanja: " . $e->getMessage();
}
