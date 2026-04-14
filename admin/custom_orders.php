<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

/* =========================================================
   ФУНКЦИЯ ДЛЯ СТИЛЯ СТАТУСА
   ========================================================= */

function adminCustomOrderBadgeClass(string $status): string
{
    return match ($status) {
        'new' => 'badge badge-new',
        'processing' => 'badge badge-processing',
        'done' => 'badge badge-done',
        'cancelled' => 'badge badge-cancelled',
        default => 'badge'
    };
}

/* =========================================================
   ПОЛУЧЕНИЕ ИНДИВИДУАЛЬНЫХ ЗАКАЗОВ
   ========================================================= */

$result = $mysqli->query("
    SELECT
        id,
        customer_name,
        customer_email,
        customer_phone,
        material,
        color,
        layer_height,
        infill,
        estimated_price,
        status,
        model_file,
        comment,
        created_at
    FROM custom_orders
    ORDER BY created_at DESC, id DESC
");
?>

    <div class="page-header">
        <h2>Индивидуальные заказы</h2>
        <p>Здесь можно просматривать заявки на печать по файлу и менять их статус.</p>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Клиент</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Материал</th>
            <th>Цвет</th>
            <th>Слой</th>
            <th>Заполнение</th>
            <th>Цена</th>
            <th>Файл</th>
            <th>Комментарий</th>
            <th>Статус</th>
            <th>Дата</th>
            <th>Действие</th>
        </tr>

        <?php while ($order = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$order['id'] ?></td>
                <td><?= e($order['customer_name']) ?></td>
                <td><?= e($order['customer_email']) ?></td>
                <td><?= e($order['customer_phone']) ?></td>
                <td><?= e($order['material']) ?></td>
                <td><?= e($order['color'] ?: '—') ?></td>
                <td><?= number_format((float)$order['layer_height'], 2) ?> мм</td>
                <td><?= (int)$order['infill'] ?>%</td>
                <td>€<?= number_format((float)$order['estimated_price'], 2) ?></td>
                <td>
                    <?php if (!empty($order['model_file'])): ?>
                        <a href="../<?= e($order['model_file']) ?>" target="_blank">Открыть</a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($order['comment'])): ?>
                        <div class="admin-comment-preview"><?= e($order['comment']) ?></div>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td>
                    <span class="<?= adminCustomOrderBadgeClass($order['status']) ?>">
                        <?= e($order['status']) ?>
                    </span>
                </td>
                <td><?= e($order['created_at']) ?></td>
                <td>
                    <form method="post" action="update_custom_order_status.php" class="admin-inline-form">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">

                        <select name="status" required>
                            <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>new</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>processing</option>
                            <option value="done" <?= $order['status'] === 'done' ? 'selected' : '' ?>>done</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                        </select>

                        <button type="submit">Сохранить</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">Индивидуальных заказов пока нет.</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';