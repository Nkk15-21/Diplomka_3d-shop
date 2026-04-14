<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../header.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('error', 'Товар не найден.');
    redirect('index.php');
}

/* =========================================================
   ПОЛУЧЕНИЕ ТОВАРА
   ========================================================= */

$stmt = $mysqli->prepare("
    SELECT *
    FROM products
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    setFlash('error', 'Товар не найден.');
    redirect('index.php');
}

/* =========================================================
   ПОЛУЧЕНИЕ КАТЕГОРИЙ
   ========================================================= */

$categoriesResult = $mysqli->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");
$categories = $categoriesResult ? $categoriesResult->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];

/* =========================================================
   РЕДАКТИРОВАНИЕ ТОВАРА
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
    $currentImagePath = $product['image_path'];

    if ($name === '') {
        $errors[] = 'Введите название товара.';
    }

    if ($price <= 0) {
        $errors[] = 'Цена должна быть больше 0.';
    }

    /* =========================================================
       ЗАГРУЗКА НОВОГО ИЗОБРАЖЕНИЯ
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
                    if (!empty($currentImagePath)) {
                        $oldFile = __DIR__ . '/../../' . $currentImagePath;
                        if (is_file($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $currentImagePath = 'uploads/images/' . $newFileName;
                }
            }
        }
    }

    /* =========================================================
       СОХРАНЕНИЕ ИЗМЕНЕНИЙ
       ========================================================= */

    if (!$errors) {
        $stmt = $mysqli->prepare("
            UPDATE products
            SET
                category_id = ?,
                name = ?,
                short_description = ?,
                description = ?,
                price = ?,
                image_path = ?,
                is_active = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
                'isssdsii',
                $categoryId,
                $name,
                $shortDescription,
                $description,
                $price,
                $currentImagePath,
                $isActive,
                $productId
        );

        if ($stmt->execute()) {
            $stmt->close();
            setFlash('success', 'Товар успешно обновлён.');
            redirect('index.php');
        } else {
            $stmt->close();
            $errors[] = 'Не удалось обновить товар.';
        }
    }

    /* Обновляем локальные данные для повторного вывода формы */
    $product['category_id'] = $categoryId;
    $product['name'] = $name;
    $product['short_description'] = $shortDescription;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['image_path'] = $currentImagePath;
    $product['is_active'] = $isActive;
}
?>

    <div class="page-header">
        <h2>Редактировать товар</h2>
        <p>Измените информацию о товаре.</p>
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
                value="<?= e((string)$product['name']) ?>"
                required
        >

        <label for="short_description">Краткое описание</label>
        <input
                type="text"
                id="short_description"
                name="short_description"
                value="<?= e((string)$product['short_description']) ?>"
        >

        <label for="description">Полное описание</label>
        <textarea id="description" name="description"><?= e((string)$product['description']) ?></textarea>

        <label for="price">Цена (€)</label>
        <input
                type="number"
                step="0.01"
                min="0.01"
                id="price"
                name="price"
                value="<?= e((string)$product['price']) ?>"
                required
        >

        <label for="category_id">Категория</label>
        <select id="category_id" name="category_id">
            <option value="">Без категории</option>
            <?php foreach ($categories as $category): ?>
                <option
                        value="<?= (int)$category['id'] ?>"
                        <?= (string)$product['category_id'] === (string)$category['id'] ? 'selected' : '' ?>
                >
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (!empty($product['image_path'])): ?>
            <div style="margin-bottom: 16px;">
                <p><strong>Текущее изображение:</strong></p>
                <img
                        src="/3d_print_shop/<?= e((string)$product['image_path']) ?>"
                        alt="<?= e((string)$product['name']) ?>"
                        style="max-width: 220px; border-radius: 12px;"
                >
            </div>
        <?php endif; ?>

        <label for="image">Новое изображение</label>
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
                    <?= (int)$product['is_active'] === 1 ? 'checked' : '' ?>
                    style="width: auto; margin: 0;"
            >
            Товар активен
        </label>

        <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="submit">Сохранить изменения</button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>

<?php require_once __DIR__ . '/../footer.php'; ?>