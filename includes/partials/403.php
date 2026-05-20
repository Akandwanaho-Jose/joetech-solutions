<?php
$page_title = $page_title ?? 'Access Denied';
include INCLUDES . '/admin_header.php';
?>

<div class="empty-state">
  <p class="eyebrow">403</p>
  <h2>Access denied</h2>
  <p>You are signed in, but your account does not have permission to open this area.</p>
  <p><a class="button" href="<?= SITE_URL ?>/public/admin/index.php">Return to dashboard</a></p>
</div>

<?php include INCLUDES . '/admin_footer.php'; ?>
