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
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">3D Print Shop</a>

        <nav class="nav">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="custom_order.php">Индивидуальный заказ</a>
            <a href="services.php">Услуги</a>
            <a href="portfolio.php">Портфолио</a>
            <a href="blog.php">Блог</a>
            <a href="contacts.php">Контакты</a>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="profile.php">Профиль</a>

                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a href="admin/index.php">Админка</a>
                <?php endif; ?>

                <a href="logout.php"
                   class="danger-link"
                   data-confirm="true"
                   data-confirm-title="Выход из аккаунта"
                   data-confirm-text="Вы уверены, что хотите выйти из аккаунта?"
                   data-confirm-button="Выйти">
                    Выход
                </a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
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