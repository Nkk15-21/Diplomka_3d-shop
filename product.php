<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('error', t('product.not_found'));
    redirect('/3d_print_shop/catalog.php');
}

$stmt = $mysqli->prepare("
    SELECT
        p.id,
        p.name,
        p.short_description,
        p.description,
        p.price,
        p.image_path,
        p.is_active,
        c.name AS category_name
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
    setFlash('error', t('product.not_found'));
    redirect('/3d_print_shop/catalog.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();

    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($quantity < 1 || $quantity > 100) {
        $errors[] = t('product.quantity_error');
    }

    if (!$errors) {
        $userId = (int)$_SESSION['user_id'];

        $stmt = $mysqli->prepare("
            SELECT id, name, email, phone
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors[] = t('login.error');
        } else {
            $totalAmount = $quantity * (float)$product['price'];

            $stmt = $mysqli->prepare("
                INSERT INTO orders (
                    user_id,
                    customer_name,
                    customer_email,
                    customer_phone,
                    total_amount,
                    status
                )
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
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    quantity,
                    unit_price
                )
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param(
                    'iiid',
                    $orderId,
                    $productId,
                    $quantity,
                    $unitPrice
            );
            $stmt->execute();
            $stmt->close();

            require_once __DIR__ . '/mail/mailer.php';

            $mailBody = renderMailTemplate('order_created.php', [
                    'orderId' => $orderId,
                    'customerName' => $user['name'],
                    'customerEmail' => $user['email'],
                    'customerPhone' => $user['phone'],
                    'productName' => $product['name'],
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                    'totalAmount' => $totalAmount,
                    'createdAt' => date('Y-m-d H:i:s'),
            ]);

            sendMailToAdmin('New product order / Новый заказ товара #' . $orderId, $mailBody);

            setFlash('success', t('product.order_success'));
            redirect('/3d_print_shop/profile.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1><?= e($product['name']) ?></h1>
        <p class="small-text">
            <?= e(t('common.category')) ?>: <?= e($product['category_name'] ?: t('common.none')) ?>
        </p>
    </div>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <div class="card">
        <?php if (!empty($product['image_path'])): ?>
            <div style="margin-bottom: 20px;">
                <img
                        src="/3d_print_shop/<?= e($product['image_path']) ?>"
                        alt="<?= e($product['name']) ?>"
                        style="max-width: 100%; border-radius: 16px;"
                >
            </div>
        <?php endif; ?>

        <p>
            <strong><?= e(t('product.short_description')) ?>:</strong>
            <?= e($product['short_description'] ?: t('common.none')) ?>
        </p>

        <p>
            <strong><?= e(t('product.description')) ?>:</strong><br>
            <?= nl2br(e($product['description'] ?: t('common.none'))) ?>
        </p>

        <p class="price">
            <strong><?= e(t('product.price')) ?>:</strong>
            €<?= number_format((float)$product['price'], 2) ?>
        </p>

        <?php if (isLoggedIn()): ?>
            <form method="post">
                <label for="quantity"><?= e(t('common.quantity')) ?></label>
                <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="1"
                        max="100"
                        value="1"
                        required
                >

                <button type="submit"><?= e(t('product.order')) ?></button>
            </form>
        <?php else: ?>
            <div class="message info">
                <?= e(t('product.login_required')) ?>
                <a href="/3d_print_shop/login.php"><?= e(t('nav.login')) ?></a>.
            </div>
        <?php endif; ?>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php';