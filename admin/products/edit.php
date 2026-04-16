<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../header.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('error', t('admin.products.not_found'));
    redirect('/3d_print_shop/admin/products/index.php');
}

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
    setFlash('error', t('admin.products.not_found'));
    redirect('/3d_print_shop/admin/products/index.php');
}

$categoriesResult = $mysqli->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");
$categories = $categoriesResult ? $categoriesResult->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];

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
        $errors[] = t('admin.products.name_error');
    }

    if ($price <= 0) {
        $errors[] = t('admin.products.price_error');
    }

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
            setFlash('success', t('admin.products.update_success'));
            redirect('/3d_print_shop/admin/products/index.php');
        } else {
            $stmt->close();
            $errors[] = t('admin.products.update_error');
        }
    }

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
        <h2><?= e(t('admin.products.edit')) ?></h2>
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
        <input type="text" id="name" name="name" value="<?= e((string)$product['name']) ?>" required>

        <label for="short_description"><?= e(t('common.short_description')) ?></label>
        <input type="text" id="short_description" name="short_description" value="<?= e((string)$product['short_description']) ?>">

        <label for="description"><?= e(t('common.description')) ?></label>
        <textarea id="description" name="description"><?= e((string)$product['description']) ?></textarea>

        <label for="price"><?= e(t('common.price')) ?> (€)</label>
        <input type="number" step="0.01" min="0.01" id="price" name="price" value="<?= e((string)$product['price']) ?>" required>

        <label for="category_id"><?= e(t('common.category')) ?></label>
        <select id="category_id" name="category_id">
            <option value=""><?= e(t('common.none')) ?></option>
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
                <p><strong><?= e(t('admin.products.current_image')) ?>:</strong></p>
                <img
                        src="/3d_print_shop/<?= e((string)$product['image_path']) ?>"
                        alt="<?= e((string)$product['name']) ?>"
                        class="product-image-preview"
                >
            </div>
        <?php endif; ?>

        <label for="image"><?= e(t('admin.products.new_image')) ?></label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

        <label style="display: flex; align-items: center; gap: 10px; font-weight: 600;">
            <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?= (int)$product['is_active'] === 1 ? 'checked' : '' ?>
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