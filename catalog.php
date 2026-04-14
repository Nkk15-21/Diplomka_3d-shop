<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$result = $mysqli->query("
    SELECT p.id, p.name, p.short_description, p.description, p.price, p.image_path, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
");
?>

    <div class="page-header">
        <h1>Каталог товаров</h1>
        <p>Выберите готовое изделие и оформите заказ в пару кликов.</p>
    </div>

    <div class="product-list">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <span class="badge"><?= e($product['category_name'] ?? 'Без категории') ?></span>
                    <h3><?= e($product['name']) ?></h3>
                    <p><?= e($product['short_description'] ?? '') ?></p>
                    <div class="price">€<?= number_format((float)$product['price'], 2) ?></div>
                    <a class="btn" href="product.php?id=<?= (int)$product['id'] ?>">Подробнее</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="message info">Пока товаров нет.</div>
        <?php endif; ?>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';