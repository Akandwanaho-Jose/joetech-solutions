<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_messages');

$message_id = get_int('id');
$message = null;
$db_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $message_id > 0) {
    verify_csrf();

    $status = post('status');
    $assign_to_me = post('assign_to_me');
    $allowed_statuses = ['unread', 'read', 'replied', 'archived'];

    if (in_array($status, $allowed_statuses, true)) {
        try {
            db_run(
                "UPDATE contact_messages
                 SET status = ?, assigned_staff_id = ?
                 WHERE id = ?",
                [
                    $status,
                    $assign_to_me === '1' ? (int) $_SESSION['staff_id'] : null,
                    $message_id,
                ]
            );

            flash('success', 'Message updated.');
            redirect('public/admin/message-view.php?id=' . $message_id);
        } catch (Throwable $e) {
            $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Could not update this message.';
        }
    } else {
        $db_error = 'Choose a valid message status.';
    }
}

try {
    if ($message_id > 0) {
        $message = db_one(
            "SELECT
                cm.*,
                s.full_name AS assigned_staff
             FROM contact_messages cm
             LEFT JOIN staff s ON s.id = cm.assigned_staff_id
             WHERE cm.id = ?
             LIMIT 1",
            [$message_id]
        );
    }
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Message details are temporarily unavailable.';
}

$page_title = $message ? 'Message' : 'Message Detail';
include INCLUDES . '/admin_header.php';
?>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($message): ?>
  <div class="section-heading">
    <p class="eyebrow">Message Detail</p>
    <h2><?= e($message['subject']) ?></h2>
    <p><?= e($message['name']) ?> | <?= e($message['email']) ?><?php if (!empty($message['phone'])): ?> | <?= e($message['phone']) ?><?php endif; ?></p>
  </div>

  <div class="stat-strip">
    <article class="stat-card">
      <strong><?= e(ucwords((string) $message['status'])) ?></strong>
      <span>Status</span>
    </article>
    <article class="stat-card">
      <strong><?= e($message['assigned_staff'] ?: 'Unassigned') ?></strong>
      <span>Assigned staff</span>
    </article>
    <article class="stat-card">
      <strong><?= e(date_fmt((string) $message['created_at'])) ?></strong>
      <span>Received</span>
    </article>
  </div>

  <div class="form-card">
    <h3>Update message</h3>
    <form method="post" action="<?= SITE_URL ?>/public/admin/message-view.php?id=<?= e((string) $message_id) ?>" class="contact-form">
      <?= csrf_field(); ?>

      <label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach (['unread', 'read', 'replied', 'archived'] as $status): ?>
          <option value="<?= e($status) ?>"<?= $message['status'] === $status ? ' selected' : '' ?>><?= e(ucwords($status)) ?></option>
        <?php endforeach; ?>
      </select>

      <label class="checkbox-row">
        <input type="checkbox" name="assign_to_me" value="1"<?= (int) ($message['assigned_staff_id'] ?? 0) === (int) $_SESSION['staff_id'] ? ' checked' : '' ?>>
        <span>Assign this message to me</span>
      </label>

      <button class="button" type="submit">Save changes</button>
    </form>
  </div>

  <div class="story-card">
    <h3>Message</h3>
    <p><?= nl2br(e((string) $message['message'])) ?></p>
  </div>

  <p><a class="text-link" href="<?= SITE_URL ?>/public/admin/messages.php">Back to messages</a></p>
<?php else: ?>
  <div class="empty-state">
    <h3>Message not found.</h3>
    <p>Return to the messages list and try again.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
