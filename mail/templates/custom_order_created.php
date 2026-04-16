<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Новый индивидуальный заказ</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
<h2 style="margin-top: 0;">Новый индивидуальный заказ</h2>

<p><strong>ID заказа:</strong> <?= htmlspecialchars((string)$orderId) ?></p>
<p><strong>Имя клиента:</strong> <?= htmlspecialchars((string)$customerName) ?></p>
<p><strong>Email клиента:</strong> <?= htmlspecialchars((string)$customerEmail) ?></p>
<p><strong>Телефон:</strong> <?= htmlspecialchars((string)($customerPhone ?: '—')) ?></p>

<hr>

<p><strong>Материал:</strong> <?= htmlspecialchars((string)$material) ?></p>
<p><strong>Цвет:</strong> <?= htmlspecialchars((string)($color ?: '—')) ?></p>
<p><strong>Высота слоя:</strong> <?= htmlspecialchars((string)$layerHeight) ?> мм</p>
<p><strong>Заполнение:</strong> <?= htmlspecialchars((string)$infill) ?>%</p>
<p><strong>Вес:</strong> <?= htmlspecialchars((string)$weight) ?> г</p>
<p><strong>Ориентировочная цена:</strong> €<?= number_format((float)$estimatedPrice, 2) ?></p>
<p><strong>Файл:</strong> <?= htmlspecialchars((string)$modelFile) ?></p>

<?php if (!empty($comment)): ?>
    <hr>
    <p><strong>Комментарий:</strong></p>
    <p><?= nl2br(htmlspecialchars((string)$comment)) ?></p>
<?php endif; ?>

<hr>

<p><strong>Дата:</strong> <?= htmlspecialchars((string)$createdAt) ?></p>
</body>
</html>