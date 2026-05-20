<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_services');

$page_title = 'Services';
$services = [];
$db_error = null;

try {
    $services = db_all(
        "SELECT id, title, slug, price_from, currency, status, sort_order
         FROM services
         WHERE deleted_at IS NULL
         ORDER BY sort_order ASC, title ASC"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Services are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Content</p><h2>Services</h2><p>Manage the services that appear on the public site.</p></div>
<p><a class="button" href="<?= SITE_URL ?>/public/admin/service-add.php">Add service</a></p>
<?php if ($db_error): ?><div class="inline-error"><?= e($db_error) ?></div><?php endif; ?>
<?php if ($services): ?>
  <div class="table-list">
    <?php foreach ($services as $service): ?>
      <article class="table-item">
        <div><strong><?= e($service['title']) ?></strong><span><?= e($service['slug']) ?></span></div>
        <div><strong><?= $service['price_from'] !== null ? e(money((float) $service['price_from'], (string) $service['currency'])) : 'Price on request' ?></strong><span>Sort: <?= e((string) $service['sort_order']) ?></span></div>
        <div><span class="chip"><?= e(ucfirst((string) $service['status'])) ?></span></div>
        <div class="table-actions"><a class="text-link" href="<?= SITE_URL ?>/public/admin/service-edit.php?id=<?= e((string) $service['id']) ?>">Edit</a></div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state"><h3>No services found.</h3><p>Seeded or created services will appear here.</p></div>
<?php endif; ?>
<?php include INCLUDES . '/admin_footer.php'; ?>
