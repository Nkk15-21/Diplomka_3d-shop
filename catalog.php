<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$result = $mysqli->query("
    SELECT
        id,
        name,
        name_ru,
        name_en,
        name_et,
        short_description,
        short_description_ru,
        short_description_en,
        short_description_et,
        description,
        description_ru,
        description_en,
        description_et,
        price,
        image_path
    FROM products
    WHERE is_active = 1
    ORDER BY created_at DESC, id DESC
");

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1><?= e(t('catalog.title')) ?></h1>
        <p><?= e(t('catalog.subtitle')) ?></p>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="product-list">
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="product-card">
                <?php if (!empty($product['image_path'])): ?>
                    <img
                            src="/3d_print_shop/<?= e($product['image_path']) ?>"
                            alt="<?= e(tdb($product, 'name')) ?>"
                    >
                <?php endif; ?>

                <h3><?= e(tdb($product, 'name')) ?></h3>

                <p>
                    <?= e(tdb($product, 'short_description') ?: tdb($product, 'description')) ?>
                </p>

                <div class="price">€<?= number_format((float)$product['price'], 2) ?></div>

                <a class="btn" href="/3d_print_shop/product.php?id=<?= (int)$product['id'] ?>">
                    <?= e(t('catalog.more')) ?>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="message info"><?= e(t('catalog.empty')) ?></div>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';