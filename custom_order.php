<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$materialsList = getMaterialsList();
$errors = [];
$estimatedPricePreview = null;

$stmt = $mysqli->prepare("SELECT name, email, phone FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    setFlash('error', 'Пользователь не найден.');
    redirect('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material = trim((string)($_POST['material'] ?? ''));
    $color = trim((string)($_POST['color'] ?? ''));
    $layerHeight = (float)($_POST['layer_height'] ?? 0);
    $infill = (int)($_POST['infill'] ?? 0);
    $weight = (float)($_POST['weight'] ?? 0);
    $comment = trim((string)($_POST['comment'] ?? ''));

    if (!in_array($material, $materialsList, true)) {
        $errors[] = 'Выберите материал из списка.';
    }

    if ($layerHeight <= 0) {
        $errors[] = 'Введите корректную высоту слоя.';
    }

    if ($infill < 0 || $infill > 100) {
        $errors[] = 'Заполнение должно быть от 0 до 100.';
    }

    if ($weight <= 0) {
        $errors[] = 'Введите корректный вес модели.';
    }

    if (!isset($_FILES['model_file']) || $_FILES['model_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Загрузите файл модели.';
    }

    $estimatedPricePreview = calculateCustomOrderPrice($material, $weight, $layerHeight, $infill);

    if (!$errors) {
        $allowedExtensions = ['stl', 'obj', 'step', 'stp'];
        $fileName = $_FILES['model_file']['name'];
        $fileTmpPath = $_FILES['model_file']['tmp_name'];
        $fileSize = (int)$_FILES['model_file']['size'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Разрешены только файлы .stl, .obj, .step, .stp.';
        }

        if ($fileSize > 20 * 1024 * 1024) {
            $errors[] = 'Файл слишком большой. Максимум 20 МБ.';
        }

        $uploadDir = __DIR__ . '/uploads/models/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (!$errors) {
            $newFileName = uniqid('model_', true) . '.' . $extension;
            $destination = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $destination)) {
                $errors[] = 'Не удалось сохранить файл.';
            } else {
                $modelFileForDb = 'uploads/models/' . $newFileName;
                $estimatedPrice = $estimatedPricePreview;

                $stmt = $mysqli->prepare("
                    INSERT INTO custom_orders (
                        user_id, customer_name, customer_email, customer_phone,
                        material, color, layer_height, infill, estimated_price,
                        status, model_file, comment
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?)
                ");
                $stmt->bind_param(
                    'isssssidsss',
                    $userId,
                    $user['name'],
                    $user['email'],
                    $user['phone'],
                    $material,
                    $color,
                    $layerHeight,
                    $infill,
                    $estimatedPrice,
                    $modelFileForDb,
                    $comment
                );
                $stmt->execute();
                $stmt->close();

                setFlash('success', 'Индивидуальный заказ успешно отправлен.');
                redirect('profile.php');
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Индивидуальный заказ</h1>
        <p>Загрузите 3D-файл, выберите параметры печати и отправьте заявку.</p>
    </div>

<?php if ($errors): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($estimatedPricePreview !== null): ?>
    <div class="message info">
        Ориентировочная стоимость: <strong>€<?= number_format((float)$estimatedPricePreview, 2) ?></strong>
    </div>
<?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="material">Материал</label>
        <select id="material" name="material" required>
            <option value="">Выберите материал</option>
            <?php foreach ($materialsList as $materialOption): ?>
                <option value="<?= e($materialOption) ?>" <?= old('material') === $materialOption ? 'selected' : '' ?>>
                    <?= e($materialOption) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="color">Цвет</label>
        <input type="text" id="color" name="color" value="<?= e(old('color')) ?>" placeholder="Например: чёрный, белый, красный">

        <label for="layer_height">Высота слоя (мм)</label>
        <input type="number" step="0.01" id="layer_height" name="layer_height" value="<?= e(old('layer_height')) ?>" required>

        <label for="infill">Заполнение (%)</label>
        <input type="number" min="0" max="100" id="infill" name="infill" value="<?= e(old('infill', '20')) ?>" required>

        <label for="weight">Вес модели (г)</label>
        <input type="number" step="0.01" id="weight" name="weight" value="<?= e(old('weight')) ?>" required>

        <label for="model_file">Файл модели</label>
        <input type="file" id="model_file" name="model_file" accept=".stl,.obj,.step,.stp" required>

        <label for="comment">Комментарий</label>
        <textarea id="comment" name="comment"><?= e(old('comment')) ?></textarea>

        <button type="submit">Отправить заказ</button>
    </form>

<?php
require_once __DIR__ . '/includes/footer.php';