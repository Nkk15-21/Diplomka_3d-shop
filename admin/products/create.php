<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../header.php';

$errors = [];

$categoriesResult = $mysqli->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");
$categories = $categoriesResult ? $categoriesResult->fetch_all(MYSQLI_ASSOC) : [];

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
        $errors[] = t('admin.products.name_error');
    }

    if ($price <= 0) {
        $errors[] = t('admin.products.price_error');
    }

    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = t('admin.products.image_upload_error');
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $originalName = (string)$_FILES['image']['name'];
            $tmpPath = (string)$_FILES['image']['tmp_name'];
            $fileSize = (int)$_FILES['image']['size'];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = t('admin.products.image_type_error');
            }

            if ($fileSize > 5 * 1024 * 1024) {
                $errors[] = t('admin.products.image_size_error');
            }

            if (!$errors) {
                $uploadDir = __DIR__ . '/../../uploads/images/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = uniqid('product_', true) . '.' . $extension;
                $destination = $uploadDir . $newFileName;

                if (!move_uploaded_file($tmpPath, $destination)) {
                    $errors[] = t('admin.products.image_save_error');
                } else {
                    $imagePath = 'uploads/images/' . $newFileName;
                }
            }
        }
    }

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
            setFlash('success', t('admin.products.create_success'));
            redirect('/3d_print_shop/admin/products/index.php');
        } else {
            $stmt->close();
            $errors[] = t('admin.products.save_error');
        }
    }
}
?>

    <div class="page-header">
        <h2><?= e(t('admin.products.create')) ?></h2>
        <p><?= e(t('admin.products.subtitle')) ?></p>
    </div>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="name"><?= e(t('common.name')) ?></label>
        <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required>

        <label for="short_description"><?= e(t('common.short_description')) ?></label>
        <input type="text" id="short_description" name="short_description" value="<?= e(old('short_description')) ?>">

        <label for="description"><?= e(t('common.description')) ?></label>
        <textarea id="description" name="description"><?= e(old('description')) ?></textarea>

        <label for="price"><?= e(t('common.price')) ?> (€)</label>
        <input type="number" step="0.01" min="0.01" id="price" name="price" value="<?= e(old('price')) ?>" required>

        <label for="category_id"><?= e(t('common.category')) ?></label>
        <select id="category_id" name="category_id">
            <option value=""><?= e(t('common.none')) ?></option>
            <?php foreach ($categories as $category): ?>
                <option
                        value="<?= (int)$category['id'] ?>"
                        <?= old('category_id') === (string)$category['id'] ? 'selected' : '' ?>
                >
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="image"><?= e(t('common.file')) ?></label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

        <label style="display: flex; align-items: center; gap: 10px; font-weight: 600;">
            <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?= old('is_active', '1') === '1' ? 'checked' : '' ?>
                    style="width: auto; margin: 0;"
            >
            <?= e(t('admin.products.active_label')) ?>
        </label>

        <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
            <button type="submit"><?= e(t('common.save')) ?></button>
            <a href="/3d_print_shop/admin/products/index.php" class="btn btn-secondary"><?= e(t('common.cancel')) ?></a>
        </div>
    </form>

<?php require_once __DIR__ . '/../footer.php'; ?>