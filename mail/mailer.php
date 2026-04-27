<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer
require_once __DIR__ . '/../includes/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../includes/phpmailer/SMTP.php';
require_once __DIR__ . '/../includes/phpmailer/Exception.php';

// Конфиг
$config = require __DIR__ . '/config.php';
$mailConfig = $config['mail'];

/**
 * Рендер HTML шаблона письма
 */
function renderMailTemplate(string $templateName, array $data = []): string
{
    $templatePath = __DIR__ . '/templates/' . $templateName;

    if (!file_exists($templatePath)) {
        return '<p>Template not found: ' . htmlspecialchars($templateName, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $templatePath;
    return ob_get_clean();
}

/**
 * Отправка письма админу
 */
function sendMailToAdmin(string $subject, string $body): bool
{
    global $mailConfig;

    $mail = new PHPMailer(true);

    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host = $mailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['username'];
        $mail->Password = $mailConfig['password'];
        $mail->Port = (int)$mailConfig['port'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Кодировка
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // От кого
        $mail->setFrom(
            $mailConfig['from_email'],
            $mailConfig['from_name']
        );

        // Кому
        $mail->addAddress($mailConfig['admin_email']);

        // Контент
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->AltBody = strip_tags(
            str_replace(['<br>', '<br/>', '<br />'], "\n", $body)
        );

        return $mail->send();

    } catch (Exception $e) {
        // Лог ошибок
        $logMessage =
            date('Y-m-d H:i:s') .
            ' | SUBJECT: ' . $subject .
            ' | ERROR_INFO: ' . $mail->ErrorInfo .
            ' | EXCEPTION: ' . $e->getMessage() .
            PHP_EOL;

        file_put_contents(
            __DIR__ . '/mail_errors.log',
            $logMessage,
            FILE_APPEND
        );

        return false;
    }
}