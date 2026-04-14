<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('error', 'Товар не найден.');
    redirect('catalog.php');
}

$stmt = $mysqli->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = ? AND p.is_active = 1
    LIMIT 1
");
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    setFlash('error', 'Товар не найден.');
    redirect('catalog.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();

    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($quantity < 1 || $quantity > 100) {
        $errors[] = 'Количество должно быть от 1 до 100.';
    }

    if (!$errors) {
        $userId = (int)$_SESSION['user_id'];

        $stmt = $mysqli->prepare("SELECT name, email, phone FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors[] = 'Пользователь не найден.';
        } else {
            $totalAmount = $quantity * (float)$product['price'];

            $stmt = $mysqli->prepare("
                INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, total_amount, status)
                VALUES (?, ?, ?, ?, ?, 'new')
            ");
            $stmt->bind_param(
                'isssd',
                $userId,
                $user['name'],
                $user['email'],
                $user['phone'],
                $totalAmount
            );
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            $unitPrice = (float)$product['price'];

            $stmt = $mysqli->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('iiid', $orderId, $productId, $quantity, $unitPrice);
            $stmt->execute();
            $stmt->close();

            setFlash('success', 'Заказ успешно оформлен.');
            redirect('profile.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1><?= e($product['name']) ?></h1>
        <p class="small-text">Категория: <?= e($product['category_name'] ?? 'Без категории') ?></p>
    </div>

<?php if ($errors): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <div class="card">
        <p><strong>Краткое описание:</strong> <?= e($product['short_description'] ?? '') ?></p>
        <p><strong>Описание:</strong> <?= nl2br(e($product['description'] ?? '')) ?></p>
        <p class="price"><strong>Цена:</strong> €<?= number_format((float)$product['price'], 2) ?></p>

        <?php if (isLoggedIn()): ?>
            <form method="post">
                <label for="quantity">Количество</label>
                <input type="number" id="quantity" name="quantity" min="1" max="100" value="1" required>

                <button type="submit">Оформить заказ</button>
            </form>
        <?php else: ?>
            <div class="message info">
                Чтобы заказать товар, сначала <a href="login.php">войдите</a> в аккаунт.
            </div>
        <?php endif; ?>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';