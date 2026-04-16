<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новое сообщение с сайта</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
<h2 style="margin-top: 0;">Новое сообщение с формы контактов</h2>

<p><strong>Имя:</strong> <?= htmlspecialchars((string)$name) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars((string)$email) ?></p>
<p><strong>Тема:</strong> <?= htmlspecialchars((string)($subject ?: 'Без темы')) ?></p>

<hr>

<p><strong>Сообщение:</strong></p>
<p><?= nl2br(htmlspecialchars((string)$message)) ?></p>

<hr>

<p><strong>Дата:</strong> <?= htmlspecialchars((string)$createdAt) ?></p>
</body>
</html>