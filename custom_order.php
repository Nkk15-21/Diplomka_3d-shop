<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

/* =========================================================
   ПОДГОТОВКА ДАННЫХ
   ========================================================= */

$userId = (int)$_SESSION['user_id'];
$materialsList = getMaterialsList();
$errors = [];
$estimatedPricePreview = null;

/* Получаем данные текущего пользователя */
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
    setFlash('error', 'Пользователь не найден.');
    redirect('logout.php');
}

/* =========================================================
   ОБРАБОТКА ФОРМЫ
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material = trim((string)($_POST['material'] ?? ''));
    $color = trim((string)($_POST['color'] ?? ''));
    $layerHeight = isset($_POST['layer_height']) ? (float)$_POST['layer_height'] : 0.0;
    $infill = isset($_POST['infill']) ? (int)$_POST['infill'] : 0;
    $weight = isset($_POST['weight']) ? (float)$_POST['weight'] : 0.0;
    $comment = trim((string)($_POST['comment'] ?? ''));

    /* =========================================================
       ВАЛИДАЦИЯ
       ========================================================= */

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

    /* Предварительный расчёт цены */
    if (!$errors) {
        $estimatedPricePreview = calculateCustomOrderPrice($material, $weight, $layerHeight, $infill);
    }

    /* =========================================================
       ПРОВЕРКА И СОХРАНЕНИЕ ФАЙЛА
       ========================================================= */

    if (!$errors) {
        $allowedExtensions = ['stl', 'obj', 'step', 'stp'];

        $originalFileName = (string)$_FILES['model_file']['name'];
        $tmpFilePath = (string)$_FILES['model_file']['tmp_name'];
        $fileSize = (int)$_FILES['model_file']['size'];

        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            $errors[] = 'Разрешены только файлы .stl, .obj, .step и .stp.';
        }

        if ($fileSize > 20 * 1024 * 1024) {
            $errors[] = 'Файл слишком большой. Максимальный размер: 20 МБ.';
        }

        $uploadDir = __DIR__ . '/uploads/models/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    }

    /* =========================================================
       СОХРАНЕНИЕ В БД
       ========================================================= */

    if (!$errors) {
        $newFileName = uniqid('model_', true) . '.' . $fileExtension;
        $destinationPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($tmpFilePath, $destinationPath)) {
            $errors[] = 'Не удалось сохранить загруженный файл.';
        } else {
            $modelFileForDb = 'uploads/models/' . $newFileName;
            $estimatedPrice = calculateCustomOrderPrice($material, $weight, $layerHeight, $infill);

            $stmt = $mysqli->prepare("
                INSERT INTO custom_orders (
                    user_id,
                    customer_name,
                    customer_email,
                    customer_phone,
                    material,
                    color,
                    layer_height,
                    infill,
                    estimated_price,
                    status,
                    model_file,
                    comment
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?)
            ");

            $stmt->bind_param(
                    'isssssiddss',
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

            if ($stmt->execute()) {
                $stmt->close();

                setFlash('success', 'Индивидуальный заказ успешно отправлен.');
                redirect('profile.php');
            } else {
                $stmt->close();
                $errors[] = 'Не удалось сохранить заказ в базу данных.';
            }
        }
    }

    /* Если есть ошибки, но данные валидные для расчёта, оставляем превью цены */
    if ($estimatedPricePreview === null && $material !== '' && $layerHeight > 0 && $weight > 0 && $infill >= 0 && $infill <= 100) {
        $estimatedPricePreview = calculateCustomOrderPrice($material, $weight, $layerHeight, $infill);
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <div class="page-header">
        <h1>Индивидуальный заказ</h1>
        <p>Загрузите 3D-файл, выберите параметры печати и получите примерную стоимость.</p>
    </div>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <div class="calculator-box">
        <h2>Калькулятор стоимости</h2>
        <p class="small-text">
            Это ориентировочный расчёт. Финальная стоимость может отличаться в зависимости
            от сложности модели, времени печати и дополнительной обработки.
        </p>

        <div class="calculator-price" id="pricePreview">
            €<?= number_format((float)($estimatedPricePreview ?? 0), 2) ?>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" id="customOrderForm">
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
        <input
                type="text"
                id="color"
                name="color"
                value="<?= e(old('color')) ?>"
                placeholder="Например: чёрный, белый, красный"
        >

        <label for="layer_height">Высота слоя (мм)</label>
        <input
                type="number"
                step="0.01"
                min="0.01"
                id="layer_height"
                name="layer_height"
                value="<?= e(old('layer_height', '0.20')) ?>"
                required
        >

        <label for="infill">Заполнение (%)</label>
        <input
                type="number"
                min="0"
                max="100"
                id="infill"
                name="infill"
                value="<?= e(old('infill', '20')) ?>"
                required
        >

        <label for="weight">Вес модели (г)</label>
        <input
                type="number"
                step="0.01"
                min="0.01"
                id="weight"
                name="weight"
                value="<?= e(old('weight', '50')) ?>"
                required
        >

        <label for="model_file">Файл модели</label>
        <input
                type="file"
                id="model_file"
                name="model_file"
                accept=".stl,.obj,.step,.stp"
                required
        >

        <label for="comment">Комментарий</label>
        <textarea id="comment" name="comment"><?= e(old('comment')) ?></textarea>

        <button type="submit">Отправить заказ</button>
    </form>

    <script>
        (function () {
            const materialSelect = document.getElementById('material');
            const layerHeightInput = document.getElementById('layer_height');
            const infillInput = document.getElementById('infill');
            const weightInput = document.getElementById('weight');
            const pricePreview = document.getElementById('pricePreview');

            const materialRates = {
                'Bambu PLA Basic': 0.35,
                'Bambu PLA Matte': 0.37,
                'Bambu PLA Tough': 0.42,
                'Bambu PLA Silk': 0.40,
                'Bambu PLA Silk+': 0.43,
                'Bambu PLA Galaxy': 0.41,
                'Bambu PLA Marble': 0.43,
                'Bambu PLA Translucent': 0.39,
                'Bambu PLA Dynamic': 0.40,
                'Bambu PLA Glow': 0.50,
                'Bambu PLA Metal': 0.48,
                'Bambu PLA Wood': 0.46,
                'Bambu PLA-CF': 0.55,
                'Bambu PETG Basic': 0.40,
                'Bambu PETG Translucent': 0.42,
                'Bambu PETG HF': 0.45,
                'Bambu PETG-CF': 0.58,
                'Bambu ABS': 0.44,
                'Bambu ASA': 0.46,
                'Bambu PC': 0.60,
                'Bambu PA (Nylon)': 0.62,
                'Bambu PA-CF': 0.70,
                'Bambu PAHT-CF': 0.78,
                'Bambu TPU 95A': 0.52,
                'Bambu Support G': 0.65,
                'Bambu Support W': 0.68,
                'Другое (указать в комментарии)': 0.50
            };

            function calculatePrice() {
                const material = materialSelect.value;
                const layerHeight = parseFloat(layerHeightInput.value) || 0;
                const infill = parseInt(infillInput.value) || 0;
                const weight = parseFloat(weightInput.value) || 0;

                if (!material || layerHeight <= 0 || infill < 0 || infill > 100 || weight <= 0) {
                    pricePreview.textContent = '€0.00';
                    return;
                }

                const basePrice = 3.00;
                const ratePerGram = materialRates[material] || 0.50;

                let layerCoefficient = 1.0;

                if (layerHeight <= 0.12) {
                    layerCoefficient = 1.35;
                } else if (layerHeight <= 0.16) {
                    layerCoefficient = 1.20;
                } else if (layerHeight <= 0.20) {
                    layerCoefficient = 1.10;
                } else if (layerHeight <= 0.28) {
                    layerCoefficient = 1.00;
                } else {
                    layerCoefficient = 0.95;
                }

                const infillCoefficient = 1.0 + (infill / 200);
                const price = (basePrice + (weight * ratePerGram)) * layerCoefficient * infillCoefficient;

                pricePreview.textContent = '€' + price.toFixed(2);
            }

            materialSelect.addEventListener('change', calculatePrice);
            layerHeightInput.addEventListener('input', calculatePrice);
            infillInput.addEventListener('input', calculatePrice);
            weightInput.addEventListener('input', calculatePrice);

            calculatePrice();
        })();
    </script>

<?php
require_once __DIR__ . '/includes/footer.php';