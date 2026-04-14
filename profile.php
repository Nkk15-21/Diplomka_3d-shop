<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Получаем пользователя
|--------------------------------------------------------------------------
*/
$stmt = $mysqli->prepare("
    SELECT name, email, phone, created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Получаем заказы (обычные)
|--------------------------------------------------------------------------
*/
$stmt = $mysqli->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/*
|--------------------------------------------------------------------------
| Получаем индивидуальные заказы
|--------------------------------------------------------------------------
*/
$stmt = $mysqli->prepare("
    SELECT *
    FROM custom_orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$customOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/*
|--------------------------------------------------------------------------
| Функция для статусов
|--------------------------------------------------------------------------
*/
function getStatusBadge(string $status): string {
    return match ($status) {
        'new' => 'badge badge-new',
        'processing' => 'badge badge-processing',
        'done' => 'badge badge-done',
        'cancelled' => 'badge badge-cancelled',
        default => 'badge'
    };
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Личный кабинет</h1>
    </div>

    <div class="card">
        <h3>Ваши данные</h3>
        <p><strong>Имя:</strong> <?= e($user['name']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Телефон:</strong> <?= e($user['phone'] ?? '—') ?></p>
        <p><strong>Дата регистрации:</strong> <?= e($user['created_at']) ?></p>
    </div>

    <br>

    <!-- ===================== ЗАКАЗЫ ===================== -->

    <h2 class="section-title">Ваши заказы</h2>

<?php if (!$orders): ?>
    <div class="message info">У вас пока нет заказов.</div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>

        <div class="card" style="margin-bottom: 20px;">
            <p>
                <strong>Заказ #<?= $order['id'] ?></strong>
                — <span class="<?= getStatusBadge($order['status']) ?>">
                    <?= e($order['status']) ?>
                </span>
            </p>

            <p><strong>Дата:</strong> <?= e($order['created_at']) ?></p>
            <p><strong>Сумма:</strong> €<?= number_format((float)$order['total_amount'], 2) ?></p>

            <h4>Товары:</h4>

            <ul class="clean-list">
                <?php
                $stmt = $mysqli->prepare("
                    SELECT oi.quantity, oi.unit_price, p.name
                    FROM order_items oi
                    JOIN products p ON p.id = oi.product_id
                    WHERE oi.order_id = ?
                ");
                $stmt->bind_param('i', $order['id']);
                $stmt->execute();
                $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                ?>

                <?php foreach ($items as $item): ?>
                    <li>
                        <?= e($item['name']) ?> —
                        <?= $item['quantity'] ?> шт.
                        × €<?= number_format((float)$item['unit_price'], 2) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    <?php endforeach; ?>
<?php endif; ?>

    <!-- ===================== ИНДИВИДУАЛЬНЫЕ ЗАКАЗЫ ===================== -->

    <h2 class="section-title">Индивидуальные заказы</h2>

<?php if (!$customOrders): ?>
    <div class="message info">Вы ещё не отправляли индивидуальные заказы.</div>
<?php else: ?>
    <?php foreach ($customOrders as $order): ?>

        <div class="card" style="margin-bottom: 20px;">
            <p>
                <strong>Заказ #<?= $order['id'] ?></strong>
                — <span class="<?= getStatusBadge($order['status']) ?>">
                    <?= e($order['status']) ?>
                </span>
            </p>

            <p><strong>Материал:</strong> <?= e($order['material']) ?></p>
            <p><strong>Цвет:</strong> <?= e($order['color'] ?? '—') ?></p>
            <p><strong>Слой:</strong> <?= e($order['layer_height']) ?> мм</p>
            <p><strong>Заполнение:</strong> <?= e($order['infill']) ?>%</p>

            <?php if ($order['estimated_price'] !== null): ?>
                <p><strong>Оценка цены:</strong> €<?= number_format((float)$order['estimated_price'], 2) ?></p>
            <?php endif; ?>

            <p><strong>Файл:</strong>
                <a href="<?= e($order['model_file']) ?>" target="_blank">Скачать</a>
            </p>

            <p><strong>Дата:</strong> <?= e($order['created_at']) ?></p>

            <?php if (!empty($order['comment'])): ?>
                <p><strong>Комментарий:</strong><br><?= nl2br(e($order['comment'])) ?></p>
            <?php endif; ?>
        </div>

    <?php endforeach; ?>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';