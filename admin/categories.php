<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

$errors = [];

/* =========================================================
   ДОБАВЛЕНИЕ КАТЕГОРИИ
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'create') {
        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($name === '') {
            $errors[] = 'Введите название категории.';
        }

        if (!$errors) {
            $stmt = $mysqli->prepare("
                INSERT INTO categories (name, description)
                VALUES (?, ?)
            ");
            $stmt->bind_param('ss', $name, $description);
            $stmt->execute();
            $stmt->close();

            setFlash('success', 'Категория успешно добавлена.');
            redirect('categories.php');
        }
    }

    /* =========================================================
       УДАЛЕНИЕ КАТЕГОРИИ
       ========================================================= */

    if ($action === 'delete') {
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        if ($categoryId <= 0) {
            setFlash('error', 'Некорректная категория для удаления.');
            redirect('categories.php');
        }

        /* Проверяем, существует ли категория */
        $stmt = $mysqli->prepare("
            SELECT id, name
            FROM categories
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $category = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$category) {
            setFlash('error', 'Категория не найдена.');
            redirect('categories.php');
        }

        /* Проверяем, есть ли товары в этой категории */
        $stmt = $mysqli->prepare("
            SELECT COUNT(*) AS cnt
            FROM products
            WHERE category_id = ?
        ");
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $productsCount = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();

        if ($productsCount > 0) {
            setFlash(
                'error',
                'Эту категорию нельзя удалить, потому что к ней привязаны товары (' . $productsCount . ').'
            );
            redirect('categories.php');
        }

        /* Удаляем категорию */
        $stmt = $mysqli->prepare("
            DELETE FROM categories
            WHERE id = ?
        ");
        $stmt->bind_param('i', $categoryId);
        $stmt->execute();
        $stmt->close();

        setFlash('success', 'Категория успешно удалена.');
        redirect('categories.php');
    }
}

/* =========================================================
   ПОЛУЧЕНИЕ КАТЕГОРИЙ
   ========================================================= */

$result = $mysqli->query("
    SELECT
        c.id,
        c.name,
        c.description,
        c.created_at,
        COUNT(p.id) AS products_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.created_at DESC, c.id DESC
");
?>

    <div class="page-header">
        <h2>Категории</h2>
        <p>Здесь можно добавлять и удалять категории товаров.</p>
    </div>

<?php if (!empty($errors)): ?>
    <div class="message error">
        <?php foreach ($errors as $error): ?>
            <div><?= e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

    <div class="card" style="margin-bottom: 24px;">
        <h3>Добавить категорию</h3>

        <form method="post">
            <input type="hidden" name="action" value="create">

            <label for="name">Название</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Описание</label>
            <textarea id="description" name="description"></textarea>

            <button type="submit">Добавить категорию</button>
        </form>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Товаров</th>
            <th>Дата создания</th>
            <th>Действие</th>
        </tr>

        <?php while ($category = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$category['id'] ?></td>
                <td><?= e($category['name']) ?></td>
                <td><?= e($category['description'] ?: '—') ?></td>
                <td><?= (int)$category['products_count'] ?></td>
                <td><?= e($category['created_at']) ?></td>
                <td>
                    <?php if ((int)$category['products_count'] > 0): ?>
                        <span class="small-text">Нельзя удалить</span>
                    <?php else: ?>
                        <form method="post" class="admin-inline-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">

                            <button
                                type="button"
                                class="btn btn-danger"
                                data-confirm="true"
                                data-confirm-title="Удаление категории"
                                data-confirm-text="Вы уверены, что хотите удалить категорию «<?= e($category['name']) ?>»?"
                                data-confirm-button="Удалить"
                            >
                                Удалить
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">Категорий пока нет.</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';