<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$result = $mysqli->query("
    SELECT
        p.id,
        p.name,
        p.name_ru,
        p.name_en,
        p.name_et,
        p.short_description,
        p.short_description_ru,
        p.short_description_en,
        p.short_description_et,
        p.description,
        p.description_ru,
        p.description_en,
        p.description_et,
        p.price,
        p.image_path,

        (
            SELECT pi.image_path
            FROM product_images pi
            WHERE pi.product_id = p.id
            ORDER BY pi.is_main DESC, pi.id ASC
            LIMIT 1
        ) AS gallery_main_image
    FROM products p
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC, p.id DESC
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
            <?php $imageToShow = $product['gallery_main_image'] ?: $product['image_path']; ?>

            <div class="product-card">
                <?php if (!empty($imageToShow)): ?>
                    <img
                            src="/3d_print_shop/<?= e($imageToShow) ?>"
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