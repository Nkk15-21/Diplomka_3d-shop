<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $passwordRepeat = (string)($_POST['password_repeat'] ?? '');

    if (!isValidName($name)) {
        $errors[] = 'Введите корректное имя.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный e-mail.';
    }

    if (!isValidPhone($phone)) {
        $errors[] = 'Введите корректный номер телефона.';
    }

    if (mb_strlen($password) < 6) {
        $errors[] = 'Пароль должен содержать минимум 6 символов.';
    }

    if ($password !== $passwordRepeat) {
        $errors[] = 'Пароли не совпадают.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $errors[] = 'Пользователь с таким e-mail уже существует.';
        }
    }

    if (!$errors) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("
            INSERT INTO users (name, email, phone, password_hash, role)
            VALUES (?, ?, ?, ?, 'user')
        ");
        $stmt->bind_param('ssss', $name, $email, $phone, $passwordHash);
        $stmt->execute();

        $userId = $stmt->insert_id;
        $stmt->close();

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'user';

        setFlash('success', 'Вы успешно зарегистрировались.');
        redirect('profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Регистрация</h1>
        <p>Создайте аккаунт, чтобы заказывать товары и отправлять свои 3D-модели на печать.</p>
    </div>

<?php if ($errors): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <form method="post">
        <label for="name">Имя</label>
        <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required>

        <label for="phone">Телефон</label>
        <input type="text" id="phone" name="phone" value="<?= e(old('phone')) ?>">

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>

        <label for="password_repeat">Повтор пароля</label>
        <input type="password" id="password_repeat" name="password_repeat" required>

        <button type="submit">Зарегистрироваться</button>
    </form>

<?php
require_once __DIR__ . '/includes/footer.php';