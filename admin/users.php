<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

/* =========================================================
   ПОЛУЧЕНИЕ ПОЛЬЗОВАТЕЛЕЙ
   ========================================================= */

$result = $mysqli->query("
    SELECT id, name, email, phone, role, created_at
    FROM users
    ORDER BY created_at DESC, id DESC
");
?>

    <div class="page-header">
        <h2>Пользователи</h2>
        <p>Здесь отображаются все зарегистрированные пользователи сайта.</p>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Телефон</th>
            <th>Роль</th>
            <th>Дата регистрации</th>
        </tr>

        <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$user['id'] ?></td>
                <td><?= e($user['name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><?= e($user['phone'] ?: '—') ?></td>
                <td>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="badge">admin</span>
                    <?php else: ?>
                        <span class="badge badge-new">user</span>
                    <?php endif; ?>
                </td>
                <td><?= e($user['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">Пользователей пока нет.</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';