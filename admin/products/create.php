<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../header.php';

$errors = [];

/* =========================================================
   ПОЛУЧЕНИЕ КАТЕГОРИЙ
   ========================================================= */

$categoriesResult = $mysqli->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");
$categories = $categoriesResult ? $categoriesResult->fetch_all(MYSQLI_ASSOC) : [];

/* =========================================================
   СОЗДАНИЕ ТОВАРА
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $shortDescription = trim((string)($_POST['short_description'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== ''
            ? (int)$_POST['category_id']
            : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $errors[] = 'Введите название товара.';
    }

    if ($price <= 0) {
        $errors[] = 'Цена должна быть больше 0.';
    }

    $imagePath = null;

    /* =========================================================
       ЗАГРУЗКА ИЗОБРАЖЕНИЯ
       ========================================================= */

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Ошибка при загрузке изображения.';
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $originalName = (string)$_FILES['image']['name'];
            $tmpPath = (string)$_FILES['image']['tmp_name'];
            $fileSize = (int)$_FILES['image']['size'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = 'Разрешены только изображения JPG, JPEG, PNG, WEBP.';
            }

            if ($fileSize > 5 * 1024 * 1024) {
                $errors[] = 'Изображение слишком большое. Максимум 5 МБ.';
            }

            if (!$errors) {
                $uploadDir = __DIR__ . '/../../uploads/images/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = uniqid('product_', true) . '.' . $extension;
                $destination = $uploadDir . $newFileName;

                if (!move_uploaded_file($tmpPath, $destination)) {
                    $errors[] = 'Не удалось сохранить изображение.';
                } else {
                    $imagePath = 'uploads/images/' . $newFileName;
                }
            }
        }
    }

    /* =========================================================
       СОХРАНЕНИЕ В БАЗУ
       ========================================================= */

    if (!$errors) {
        $stmt = $mysqli->prepare("
            INSERT INTO products (
                category_id,
                name,
                short_description,
                description,
                price,
                image_path,
                is_active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
                'isssdsi',
                $categoryId,
                $name,
                $shortDescription,
                $description,
                $price,
                $imagePath,
                $isActive
        );

        if ($stmt->execute()) {
            $stmt->close();
            setFlash('success', 'Товар успешно создан.');
            redirect('index.php');
        } else {
            $stmt->close();
            $errors[] = 'Не удалось сохранить товар.';
        }
    }
}
?>

    <div class="page-header">
        <h2>Добавить товар</h2>
        <p>Создайте новый товар для каталога.</p>
    </div>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="name">Название</label>
        <input
                type="text"
                id="name"
                name="name"
                value="<?= e(old('name')) ?>"
                required
        >

        <label for="short_description">Краткое описание</label>
        <input
                type="text"
                id="short_description"
                name="short_description"
                value="<?= e(old('short_description')) ?>"
        >

        <label for="description">Полное описание</label>
        <textarea id="description" name="description"><?= e(old('description')) ?></textarea>

        <label for="price">Цена (€)</label>
        <input
                type="number"
                step="0.01"
                min="0.01"
                id="price"
                name="price"
                value="<?= e(old('price')) ?>"
                required
        >

        <label for="category_id">Категория</label>
        <select id="category_id" name="category_id">
            <option value="">Без категории</option>
            <?php foreach ($categories as $category): ?>
                <option
                        value="<?= (int)$category['id'] ?>"
                        <?= old('category_id') === (string)$category['id'] ? 'selected' : '' ?>
                >
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="image">Изображение</label>
        <input
                type="file"
                id="image"
                name="image"
                accept=".jpg,.jpeg,.png,.webp"
        >

        <label style="display: flex; align-items: center; gap: 10px; font-weight: 600;">
            <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?= old('is_active', '1') === '1' ? 'checked' : '' ?>
                    style="width: auto; margin: 0;"
            >
            Товар активен
        </label>

        <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="submit">Сохранить товар</button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>

<?php require_once __DIR__ . '/../footer.php'; ?>