<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новый заказ товара</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
<h2 style="margin-top: 0;">Новый заказ товара</h2>

<p><strong>ID заказа:</strong> <?= htmlspecialchars((string)$orderId) ?></p>
<p><strong>Имя клиента:</strong> <?= htmlspecialchars((string)$customerName) ?></p>
<p><strong>Email клиента:</strong> <?= htmlspecialchars((string)$customerEmail) ?></p>
<p><strong>Телефон:</strong> <?= htmlspecialchars((string)($customerPhone ?: '—')) ?></p>

<hr>

<p><strong>Товар:</strong> <?= htmlspecialchars((string)$productName) ?></p>
<p><strong>Количество:</strong> <?= htmlspecialchars((string)$quantity) ?></p>
<p><strong>Цена за единицу:</strong> €<?= number_format((float)$unitPrice, 2) ?></p>
<p><strong>Общая сумма:</strong> €<?= number_format((float)$totalAmount, 2) ?></p>

<hr>

<p><strong>Дата:</strong> <?= htmlspecialchars((string)$createdAt) ?></p>
</body>
</html>