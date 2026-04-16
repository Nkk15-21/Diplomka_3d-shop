<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../header.php';

$result = $mysqli->query("
    SELECT
        p.id,
        p.name,
        p.short_description,
        p.price,
        p.image_path,
        p.is_active,
        p.created_at,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC, p.id DESC
");
?>

    <div class="page-header">
        <h2><?= e(t('admin.products.title')) ?></h2>
        <p><?= e(t('admin.products.subtitle')) ?></p>
        <a href="/3d_print_shop/admin/products/create.php" class="btn"><?= e(t('admin.products.add')) ?></a>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th><?= e(t('common.file')) ?></th>
            <th><?= e(t('common.name')) ?></th>
            <th><?= e(t('common.category')) ?></th>
            <th><?= e(t('common.price')) ?></th>
            <th><?= e(t('common.status')) ?></th>
            <th><?= e(t('common.created_at')) ?></th>
            <th><?= e(t('common.actions')) ?></th>
        </tr>

        <?php while ($product = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$product['id'] ?></td>

                <td>
                    <?php if (!empty($product['image_path'])): ?>
                        <img
                                src="/3d_print_shop/<?= e($product['image_path']) ?>"
                                alt="<?= e($product['name']) ?>"
                                style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;"
                        >
                    <?php else: ?>
                        <span class="small-text"><?= e(t('admin.products.no_image')) ?></span>
                    <?php endif; ?>
                </td>

                <td>
                    <strong><?= e($product['name']) ?></strong><br>
                    <span class="small-text"><?= e($product['short_description'] ?: t('common.none')) ?></span>
                </td>

                <td><?= e($product['category_name'] ?: t('common.none')) ?></td>

                <td>€<?= number_format((float)$product['price'], 2) ?></td>

                <td>
                    <?php if ((int)$product['is_active'] === 1): ?>
                        <span class="badge badge-done"><?= e(t('admin.products.active_label_short')) ?></span>
                    <?php else: ?>
                        <span class="badge badge-cancelled"><?= e(t('admin.products.hidden_label')) ?></span>
                    <?php endif; ?>
                </td>

                <td><?= e($product['created_at']) ?></td>

                <td>
                    <a
                            href="/3d_print_shop/admin/products/edit.php?id=<?= (int)$product['id'] ?>"
                            class="action-icon edit"
                            title="<?= e(t('common.edit')) ?>"
                    >
                        ✏️
                    </a>

                    <a
                            href="/3d_print_shop/admin/products/delete.php?id=<?= (int)$product['id'] ?>"
                            class="action-icon delete"
                            title="<?= e(t('common.delete')) ?>"
                            data-confirm="true"
                            data-confirm-title="<?= e(t('common.delete')) ?>"
                            data-confirm-text="<?= e(t('admin.products.delete_confirm')) ?>"
                            data-confirm-button="<?= e(t('common.delete')) ?>"
                    >
                        🗑️
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info"><?= e(t('admin.products.empty')) ?></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>