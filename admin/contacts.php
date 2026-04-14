<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

/* =========================================================
   ПОЛУЧЕНИЕ СООБЩЕНИЙ
   ========================================================= */

$result = $mysqli->query("
    SELECT
        id,
        name,
        email,
        subject,
        message,
        created_at
    FROM contacts
    ORDER BY created_at DESC, id DESC
");
?>

    <div class="page-header">
        <h2>Сообщения из формы контактов</h2>
        <p>Здесь отображаются все обращения, отправленные через страницу контактов.</p>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Тема</th>
            <th>Сообщение</th>
            <th>Дата</th>
        </tr>

        <?php while ($contact = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$contact['id'] ?></td>
                <td><?= e($contact['name']) ?></td>
                <td><?= e($contact['email']) ?></td>
                <td><?= e($contact['subject'] ?: 'Без темы') ?></td>
                <td>
                    <div class="admin-message-box">
                        <?= nl2br(e($contact['message'])) ?>
                    </div>
                </td>
                <td><?= e($contact['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <div class="message info">Сообщений пока нет.</div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';