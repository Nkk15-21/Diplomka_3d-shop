<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if (!isValidName($name)) {
        $errors[] = 'Введите корректное имя.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный e-mail.';
    }

    if (mb_strlen($message) < 10) {
        $errors[] = 'Сообщение слишком короткое.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("
            INSERT INTO contacts (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        $stmt->execute();
        $stmt->close();

        setFlash('success', 'Сообщение успешно отправлено.');
        redirect('contacts.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Контакты</h1>
        <p>Напишите нам, если хотите уточнить материал, сроки, стоимость или детали заказа.</p>
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

        <label for="subject">Тема</label>
        <input type="text" id="subject" name="subject" value="<?= e(old('subject')) ?>">

        <label for="message">Сообщение</label>
        <textarea id="message" name="message" required><?= e(old('message')) ?></textarea>

        <button type="submit">Отправить</button>
    </form>

<?php
require_once __DIR__ . '/includes/footer.php';