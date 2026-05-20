<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_messages');

$page_title = 'Messages';
$messages = [];
$db_error = null;

try {
    $messages = db_all(
        "SELECT
            cm.id,
            cm.name,
            cm.email,
            cm.phone,
            cm.subject,
            cm.status,
            cm.created_at,
            s.full_name AS assigned_staff
         FROM contact_messages cm
         LEFT JOIN staff s ON s.id = cm.assigned_staff_id
         ORDER BY
            CASE cm.status
                WHEN 'unread' THEN 1
                WHEN 'read' THEN 2
                WHEN 'replied' THEN 3
                ELSE 4
            END,
            cm.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Messages are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Messages</p>
  <h2>Customer enquiries</h2>
  <p>Messages coming from the contact page are received here first, then reviewed and moved forward.</p>
</div>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($messages): ?>
  <div class="table-list">
    <?php foreach ($messages as $message): ?>
      <article class="table-item">
        <div>
          <strong><?= e($message['subject']) ?></strong>
          <span><?= e($message['name']) ?> | <?= e($message['email']) ?></span>
        </div>
        <div>
          <strong><?= e(ucwords((string) $message['status'])) ?></strong>
          <span><?= e(date_fmt((string) $message['created_at'])) ?></span>
        </div>
        <div>
          <span class="chip"><?= e($message['assigned_staff'] ?: 'Unassigned') ?></span>
        </div>
        <div class="table-actions">
          <a class="text-link" href="<?= SITE_URL ?>/public/admin/message-view.php?id=<?= e((string) $message['id']) ?>">Open message</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No messages yet.</h3>
    <p>New contact enquiries will appear here.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
