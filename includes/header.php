<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$flashSuccess = getFlash('success');
$flashError = getFlash('error');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Print Shop</title>
    <link rel="stylesheet" href="/3d_print_shop/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="/3d_print_shop/index.php" class="logo">3D Print Shop</a>

        <nav class="nav nav-main">
            <a href="/3d_print_shop/index.php">Главная</a>
            <a href="/3d_print_shop/catalog.php">Каталог</a>
            <a href="/3d_print_shop/custom_order.php">Индивидуальный заказ</a>
            <a href="/3d_print_shop/services.php">Услуги</a>
            <a href="/3d_print_shop/blog.php">Блог</a>

            <?php if (!empty($_SESSION['user_id']) && (($_SESSION['user_role'] ?? '') === 'admin')): ?>
                <a href="/3d_print_shop/admin/index.php">Админка</a>
            <?php endif; ?>
        </nav>

        <nav class="nav nav-actions">
            <a href="/3d_print_shop/contacts.php" class="nav-circle-btn" title="Контакты" aria-label="Контакты">
                <svg viewBox="0 0 24 24" class="nav-svg" aria-hidden="true">
                    <path d="M3 6h18v12H3z" fill="none" stroke="currentColor" stroke-width="2"/>
                    <path d="M3 7l9 7 9-7" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
            </a>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/3d_print_shop/profile.php" class="nav-profile-btn" title="Профиль">
                    <span class="nav-profile-icon-wrap">
                        <svg viewBox="0 0 24 24" class="nav-svg" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </span>
                    <span class="nav-profile-name"><?= e($_SESSION['user_name'] ?? 'Профиль') ?></span>
                </a>

                <a href="/3d_print_shop/logout.php"
                   class="nav-circle-btn nav-logout-btn"
                   title="Выход"
                   aria-label="Выход"
                   data-confirm="true"
                   data-confirm-title="Выход из аккаунта"
                   data-confirm-text="Вы уверены, что хотите выйти из аккаунта?"
                   data-confirm-button="Выйти">
                    <svg viewBox="0 0 24 24" class="nav-svg" aria-hidden="true">
                        <path d="M16 17l5-5-5-5" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 12H9" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 4H5v16h8" fill="none" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </a>
            <?php else: ?>
                <a href="/3d_print_shop/login.php">Вход</a>
                <a href="/3d_print_shop/register.php">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <?php if ($flashSuccess): ?>
        <div class="message success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="message error"><?= e($flashError) ?></div>
    <?php endif; ?>