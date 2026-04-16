<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

$usersCount = (int)($mysqli->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'] ?? 0);
$productsCount = (int)($mysqli->query("SELECT COUNT(*) AS cnt FROM products")->fetch_assoc()['cnt'] ?? 0);
$ordersCount = (int)($mysqli->query("SELECT COUNT(*) AS cnt FROM orders")->fetch_assoc()['cnt'] ?? 0);
$customOrdersCount = (int)($mysqli->query("SELECT COUNT(*) AS cnt FROM custom_orders")->fetch_assoc()['cnt'] ?? 0);
$contactsCount = (int)($mysqli->query("SELECT COUNT(*) AS cnt FROM contacts")->fetch_assoc()['cnt'] ?? 0);
?>

    <div class="page-header">
        <h2><?= e(t('admin.dashboard.title')) ?></h2>
        <p><?= e(t('admin.dashboard.subtitle')) ?></p>
    </div>

    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-card__title"><?= e(t('admin.users')) ?></div>
            <div class="admin-stat-card__value"><?= $usersCount ?></div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__title"><?= e(t('admin.products')) ?></div>
            <div class="admin-stat-card__value"><?= $productsCount ?></div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__title"><?= e(t('admin.orders')) ?></div>
            <div class="admin-stat-card__value"><?= $ordersCount ?></div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__title"><?= e(t('admin.custom_orders')) ?></div>
            <div class="admin-stat-card__value"><?= $customOrdersCount ?></div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-card__title"><?= e(t('admin.contacts')) ?></div>
            <div class="admin-stat-card__value"><?= $contactsCount ?></div>
        </div>
    </div>

<?php
require_once __DIR__ . '/footer.php';