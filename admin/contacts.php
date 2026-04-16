<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

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
        <h2><?= e(t('admin.contacts.title')) ?></h2>
        <p><?= e(t('admin.contacts.subtitle')) ?></p>
    </div>

<?php if ($result && $result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th><?= e(t('common.name')) ?></th>
            <th><?= e(t('common.email')) ?></th>
            <th><?= e(t('common.subject')) ?></th>
            <th><?= e(t('common.message')) ?></th>
            <th><?= e(t('common.date')) ?></th>
        </tr>

        <?php while ($contact = $result->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$contact['id'] ?></td>
                <td><?= e($contact['name']) ?></td>
                <td><?= e($contact['email']) ?></td>
                <td><?= e($contact['subject'] ?: t('common.none')) ?></td>
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
    <div class="message info"><?= e(t('admin.contacts.empty')) ?></div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';