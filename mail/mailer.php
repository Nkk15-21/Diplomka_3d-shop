<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/Exception.php';

/* =========================================================
   ОТПРАВКА EMAIL АДМИНУ
   ========================================================= */

function sendMailToAdmin(string $subject, string $htmlBody, array $attachments = []): bool
{
    $config = require __DIR__ . '/config.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($config['admin_email']);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        /* =========================================================
           ВЛОЖЕНИЯ
           ========================================================= */
        foreach ($attachments as $attachment) {
            if (!empty($attachment['path']) && is_file($attachment['path'])) {
                $mail->addAttachment(
                    $attachment['path'],
                    $attachment['name'] ?? basename($attachment['path'])
                );
            }
        }

        return $mail->send();
    } catch (Exception $e) {
        echo 'Ошибка отправки: ' . $mail->ErrorInfo;
        return false;
    }
}

/* =========================================================
   РЕНДЕР ШАБЛОНА ПИСЬМА
   ========================================================= */

function renderMailTemplate(string $templateFile, array $data = []): string
{
    $fullPath = __DIR__ . '/templates/' . $templateFile;

    if (!file_exists($fullPath)) {
        return '<p>Шаблон письма не найден.</p>';
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $fullPath;
    return (string)ob_get_clean();
}