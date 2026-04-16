<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

$result = $mysqli->query("
    SELECT
        o.id,
        o.customer_name,
        o.customer_email,
        o.customer_phone,
        o.total_amount,
        o.status,
        o.created_at,
        GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') AS items
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN products p ON p.id = oi.product_id
    GROUP BY o.id
    ORDER BY o.created_at DESC, o.id DESC
");

function adminOrderBadgeClassI18n(string $status): string
{
    return match ($status) {
        'new' => 'badge badge-new',
        'processing' => 'badge badge-processing',
        'done' => 'badge badge-done',
        'cancelled' => 'badge badge-cancelled',
        default => 'badge'
    };
}
?>

    <div class="page-header">
        <h2><?= e(t('admin.orders.title')) ?></h2>
        <p><?= e(t('admin.orders.subtitle')) ?></p>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th><?= e(t('common.name')) ?></th>
            <th><?= e(t('common.email')) ?></th>
            <th><?= e(t('common.phone')) ?></th>
            <th><?= e(t('admin.orders.items')) ?></th>
            <th><?= e(t('common.total')) ?></th>
            <th><?= e(t('common.status')) ?></th>
            <th><?= e(t('common.date')) ?></th>
            <th><?= e(t('common.actions')) ?></th>
        </tr>

        <?php while ($order = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$order['id'] ?></td>
                <td><?= e($order['customer_name']) ?></td>
                <td><?= e($order['customer_email']) ?></td>
                <td><?= e($order['customer_phone']) ?></td>
                <td><?= e($order['items'] ?? '') ?></td>
                <td>€<?= number_format((float)$order['total_amount'], 2) ?></td>
                <td>
                    <span class="<?= adminOrderBadgeClassI18n($order['status']) ?>">
                        <?= e(t('status.' . $order['status'])) ?>
                    </span>
                </td>
                <td><?= e($order['created_at']) ?></td>
                <td>
                    <form method="post" action="/3d_print_shop/admin/update_order_status.php" class="admin-inline-form">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">

                        <select name="status" required>
                            <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>><?= e(t('status.new')) ?></option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>><?= e(t('status.processing')) ?></option>
                            <option value="done" <?= $order['status'] === 'done' ? 'selected' : '' ?>><?= e(t('status.done')) ?></option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>><?= e(t('status.cancelled')) ?></option>
                        </select>

                        <button type="submit"><?= e(t('common.save')) ?></button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info"><?= e(t('admin.orders.empty')) ?></div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';