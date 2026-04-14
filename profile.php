<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT 
        o.id AS order_id,
        o.total_amount,
        o.status,
        o.created_at,
        p.name AS product_name,
        oi.quantity,
        oi.unit_price
    FROM orders o
    INNER JOIN order_items oi ON oi.order_id = o.id
    INNER JOIN products p ON p.id = oi.product_id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC, o.id DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$ordersResult = $stmt->get_result();
$stmt->close();

$stmt = $mysqli->prepare("
    SELECT id, material, color, layer_height, infill, estimated_price, status, model_file, comment, created_at
    FROM custom_orders
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$customOrdersResult = $stmt->get_result();
$stmt->close();

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Личный кабинет</h1>
        <p>Добро пожаловать, <?= e($user['name'] ?? $_SESSION['user_name']) ?>.</p>
    </div>

    <div class="card" style="margin-bottom: 25px;">
        <h2>Данные аккаунта</h2>
        <p><strong>Имя:</strong> <?= e($user['name'] ?? '') ?></p>
        <p><strong>E-mail:</strong> <?= e($user['email'] ?? '') ?></p>
        <p><strong>Телефон:</strong> <?= e($user['phone'] ?? '') ?></p>
        <p><strong>Роль:</strong> <?= e($user['role'] ?? '') ?></p>
        <p><strong>Дата регистрации:</strong> <?= e($user['created_at'] ?? '') ?></p>
    </div>

    <h2>Заказы товаров</h2>
<?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID заказа</th>
            <th>Товар</th>
            <th>Количество</th>
            <th>Цена за единицу</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Дата</th>
        </tr>
        <?php while ($order = $ordersResult->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$order['order_id'] ?></td>
                <td><?= e($order['product_name']) ?></td>
                <td><?= (int)$order['quantity'] ?></td>
                <td>€<?= number_format((float)$order['unit_price'], 2) ?></td>
                <td>€<?= number_format((float)$order['total_amount'], 2) ?></td>
                <td><?= e($order['status']) ?></td>
                <td><?= e($order['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">У вас пока нет заказов готовых товаров.</div>
<?php endif; ?>

    <h2>Индивидуальные заказы</h2>
<?php if ($customOrdersResult && $customOrdersResult->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Материал</th>
            <th>Цвет</th>
            <th>Высота слоя</th>
            <th>Заполнение</th>
            <th>Оценка цены</th>
            <th>Статус</th>
            <th>Файл</th>
            <th>Дата</th>
        </tr>
        <?php while ($customOrder = $customOrdersResult->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$customOrder['id'] ?></td>
                <td><?= e($customOrder['material']) ?></td>
                <td><?= e($customOrder['color']) ?></td>
                <td><?= e((string)$customOrder['layer_height']) ?></td>
                <td><?= e((string)$customOrder['infill']) ?>%</td>
                <td>€<?= number_format((float)$customOrder['estimated_price'], 2) ?></td>
                <td><?= e($customOrder['status']) ?></td>
                <td><?= e(basename($customOrder['model_file'])) ?></td>
                <td><?= e($customOrder['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">У вас пока нет индивидуальных заказов.</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';