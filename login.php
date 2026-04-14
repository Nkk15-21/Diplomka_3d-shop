<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный e-mail.';
    }

    if ($password === '') {
        $errors[] = 'Введите пароль.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Неверный e-mail или пароль.';
        } else {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            setFlash('success', 'Вы успешно вошли.');
            redirect('profile.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Вход в аккаунт</h1>
        <p>Войдите, чтобы увидеть свои заказы и оформить новые.</p>
    </div>

<?php if ($errors): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <form method="post">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required>

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Войти</button>
    </form>

<?php
require_once __DIR__ . '/includes/footer.php';