<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_portfolio');

$page_title = 'Portfolio';
$projects = [];
$db_error = null;

try {
    $projects = db_all(
        "SELECT pp.id, pp.title, pp.slug, pp.client_name, pp.is_featured, pp.completed_date, s.title AS service_title
         FROM portfolio_projects pp
         INNER JOIN services s ON s.id = pp.service_id
         WHERE pp.deleted_at IS NULL
         ORDER BY pp.sort_order ASC, pp.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Portfolio projects are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Content</p><h2>Portfolio projects</h2><p>Showcase work, client delivery, and featured case studies.</p></div>
<p><a class="button" href="<?= SITE_URL ?>/public/admin/portfolio-add.php">Add project</a></p>
<?php if ($db_error): ?><div class="inline-error"><?= e($db_error) ?></div><?php endif; ?>
<?php if ($projects): ?>
  <div class="table-list">
    <?php foreach ($projects as $project): ?>
      <article class="table-item">
        <div><strong><?= e($project['title']) ?></strong><span><?= e($project['service_title']) ?> | <?= e($project['slug']) ?></span></div>
        <div><strong><?= e($project['client_name'] ?: 'Internal / Public') ?></strong><span><?= !empty($project['completed_date']) ? e(date_fmt((string) $project['completed_date'])) : 'No date' ?></span></div>
        <div><span class="chip"><?= !empty($project['is_featured']) ? 'Featured' : 'Standard' ?></span></div>
        <div class="table-actions"><a class="text-link" href="<?= SITE_URL ?>/public/admin/portfolio-edit.php?id=<?= e((string) $project['id']) ?>">Edit</a></div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state"><h3>No portfolio projects yet.</h3><p>Add projects as your showcase library grows.</p></div>
<?php endif; ?>
<?php include INCLUDES . '/admin_footer.php'; ?>
