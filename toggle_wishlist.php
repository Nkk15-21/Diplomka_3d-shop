<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('error', 'Некорректный товар.');
    redirect('/3d_print_shop/catalog.php');
}

$stmt = $mysqli->prepare("
    SELECT id
    FROM wishlist
    WHERE user_id = ? AND product_id = ?
    LIMIT 1
");
$stmt->bind_param('ii', $userId, $productId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing) {
    $stmt = $mysqli->prepare("
        DELETE FROM wishlist
        WHERE id = ?
    ");
    $stmt->bind_param('i', $existing['id']);
    $stmt->execute();
    $stmt->close();

    setFlash('success', 'Товар удалён из избранного.');
} else {
    $stmt = $mysqli->prepare("
        INSERT INTO wishlist (user_id, product_id)
        VALUES (?, ?)
    ");
    $stmt->bind_param('ii', $userId, $productId);
    $stmt->execute();
    $stmt->close();

    setFlash('success', 'Товар добавлен в избранное.');
}

redirect('/3d_print_shop/wishlist.php');