<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../header.php';

/* =========================================================
   ПОЛУЧЕНИЕ ТОВАРОВ
   ========================================================= */

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
        <h2>Товары</h2>
        <p>Здесь можно добавлять, редактировать и удалять товары магазина.</p>
        <a href="create.php" class="btn">+ Добавить товар</a>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Изображение</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Цена</th>
            <th>Статус</th>
            <th>Дата</th>
            <th>Действия</th>
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
                        <span class="small-text">Нет фото</span>
                    <?php endif; ?>
                </td>

                <td>
                    <strong><?= e($product['name']) ?></strong><br>
                    <span class="small-text"><?= e($product['short_description'] ?: 'Без краткого описания') ?></span>
                </td>

                <td><?= e($product['category_name'] ?: 'Без категории') ?></td>

                <td>€<?= number_format((float)$product['price'], 2) ?></td>

                <td>
                    <?php if ((int)$product['is_active'] === 1): ?>
                        <span class="badge badge-done">Активен</span>
                    <?php else: ?>
                        <span class="badge badge-cancelled">Скрыт</span>
                    <?php endif; ?>
                </td>

                <td><?= e($product['created_at']) ?></td>

                <td>
                    <a
                            href="edit.php?id=<?= (int)$product['id'] ?>"
                            class="action-icon edit"
                            title="Редактировать"
                    >
                        ✏️
                    </a>

                    <a
                            href="delete.php?id=<?= (int)$product['id'] ?>"
                            class="action-icon delete"
                            title="Удалить"
                            data-confirm="true"
                            data-confirm-title="Удаление товара"
                            data-confirm-text="Вы уверены, что хотите удалить товар «<?= e($product['name']) ?>»?"
                            data-confirm-button="Удалить"
                    >
                        🗑️
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">Товаров пока нет.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>