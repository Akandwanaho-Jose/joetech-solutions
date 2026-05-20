<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_staff');

$page_title = 'Staff Accounts';
$staff_accounts = [];
$db_error = null;

try {
    $staff_accounts = db_all(
        "SELECT s.id, s.full_name, s.email, s.phone, s.status, s.last_login_at, r.name AS role_name
         FROM staff s
         INNER JOIN roles r ON r.id = s.role_id
         WHERE s.deleted_at IS NULL
         ORDER BY s.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Staff accounts are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Admin</p><h2>Staff accounts</h2><p>Create and manage the internal team that operates the site.</p></div>
<p><a class="button" href="<?= SITE_URL ?>/public/admin/staff-add.php">Add staff</a></p>
<?php if ($db_error): ?><div class="inline-error"><?= e($db_error) ?></div><?php endif; ?>
<?php if ($staff_accounts): ?>
  <div class="table-list">
    <?php foreach ($staff_accounts as $member): ?>
      <article class="table-item">
        <div><strong><?= e($member['full_name']) ?></strong><span><?= e($member['email']) ?><?php if (!empty($member['phone'])): ?> | <?= e($member['phone']) ?><?php endif; ?></span></div>
        <div><strong><?= e($member['role_name']) ?></strong><span><?= !empty($member['last_login_at']) ? e(date_fmt((string) $member['last_login_at'])) : 'No login yet' ?></span></div>
        <div><span class="chip"><?= e(ucfirst((string) $member['status'])) ?></span></div>
        <div class="table-actions"><a class="text-link" href="<?= SITE_URL ?>/public/admin/staff-edit.php?id=<?= e((string) $member['id']) ?>">Edit</a></div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state"><h3>No staff accounts yet.</h3><p>Add staff accounts to expand admin access safely.</p></div>
<?php endif; ?>
<?php include INCLUDES . '/admin_footer.php'; ?>
