<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$flashSuccess = getFlash('success');
$flashError = getFlash('error');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка | 3D Print Shop</title>
    <link rel="stylesheet" href="/3d_print_shop/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar__logo">
            <a href="index.php">Админка</a>
        </div>

        <nav class="admin-sidebar__nav">
            <a href="index.php">Главная</a>
            <a href="orders.php">Заказы товаров</a>
            <a href="custom_orders.php">Индивидуальные заказы</a>
            <a href="contacts.php">Сообщения</a>
            <a href="products/index.php">Товары</a>
            <a href="categories.php">Категории</a>
            <a href="users.php">Пользователи</a>
            <a href="../index.php">На сайт</a>
            <a href="../logout.php"
               class="danger-link"
               data-confirm="true"
               data-confirm-title="Выход из аккаунта"
               data-confirm-text="Вы уверены, что хотите выйти из аккаунта?"
               data-confirm-button="Выйти">Выход</a>
        </nav>
    </aside>

    <div class="admin-content">
        <header class="admin-topbar">
            <h1>Панель администратора</h1>
            <div class="admin-topbar__user">
                <?= e($_SESSION['user_name'] ?? 'admin') ?>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($flashSuccess): ?>
                <div class="message success"><?= e($flashSuccess) ?></div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="message error"><?= e($flashError) ?></div>
            <?php endif; ?>