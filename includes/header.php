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
<html lang="<?= e(currentLang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('site.title')) ?></title>
    <link rel="stylesheet" href="/3d_print_shop/css/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="/3d_print_shop/index.php" class="logo"><?= e(t('site.title')) ?></a>

        <nav class="nav nav-main">
            <a href="/3d_print_shop/index.php"><?= e(t('nav.home')) ?></a>
            <a href="/3d_print_shop/catalog.php"><?= e(t('nav.catalog')) ?></a>
            <a href="/3d_print_shop/custom_order.php"><?= e(t('nav.custom_order')) ?></a>
            <a href="/3d_print_shop/services.php"><?= e(t('nav.services')) ?></a>
            <a href="/3d_print_shop/blog.php"><?= e(t('nav.blog')) ?></a>

            <?php if (!empty($_SESSION['user_id']) && (($_SESSION['user_role'] ?? '') === 'admin')): ?>
                <a href="/3d_print_shop/admin/index.php"><?= e(t('nav.admin')) ?></a>
            <?php endif; ?>
        </nav>

        <div class="header-right">
            <div class="lang-switcher">
                <a href="<?= e(langUrl('ru')) ?>" class="lang-btn <?= currentLang() === 'ru' ? 'active' : '' ?>">Rus</a>
                <a href="<?= e(langUrl('en')) ?>" class="lang-btn <?= currentLang() === 'en' ? 'active' : '' ?>">Eng</a>
                <a href="<?= e(langUrl('et')) ?>" class="lang-btn <?= currentLang() === 'et' ? 'active' : '' ?>">Est</a>
            </div>

            <nav class="nav nav-actions">
                <a href="/3d_print_shop/contacts.php" class="nav-circle-btn" title="<?= e(t('nav.contacts')) ?>" aria-label="<?= e(t('nav.contacts')) ?>">
                    <svg viewBox="0 0 24 24" class="nav-svg" aria-hidden="true">
                        <path d="M3 6h18v12H3z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M3 7l9 7 9-7" fill="none" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </a>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="/3d_print_shop/profile.php" class="nav-profile-btn" title="<?= e(t('nav.profile')) ?>">
                        <span class="nav-profile-icon-wrap">
                            <svg viewBox="0 0 24 24" class="nav-svg" aria-hidden="true">
                                <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" fill="none" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </span>
                        <span class="nav-profile-name"><?= e($_SESSION['user_name'] ?? t('nav.profile')) ?></span>
                    </a>

                    <a href="/3d_print_shop/logout.php"
                       class="nav-circle-btn nav-logout-btn"
                       title="<?= e(t('nav.logout')) ?>"
                       aria-label="<?= e(t('nav.logout')) ?>"
                       data-confirm="true"
                       data-confirm-title="<?= e(t('logout.title')) ?>"
                       data-confirm-text="<?= e(t('logout.text')) ?>"
                       data-confirm-button="<?= e(t('logout.button')) ?>">
                        <svg viewBox="0 0 24 24" class="nav-svg" aria-hidden="true">
                            <path d="M16 17l5-5-5-5" fill="none" stroke="currentColor" stroke-width="2"/>
                            <path d="M21 12H9" fill="none" stroke="currentColor" stroke-width="2"/>
                            <path d="M13 4H5v16h8" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="/3d_print_shop/login.php"><?= e(t('nav.login')) ?></a>
                    <a href="/3d_print_shop/register.php"><?= e(t('nav.register')) ?></a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<main class="container">
    <?php if ($flashSuccess): ?>
        <div class="message success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="message error"><?= e($flashError) ?></div>
    <?php endif; ?>