<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_requests');

$page_title = 'Service Requests';
$requests = [];
$db_error = null;

try {
    $requests = db_all(
        "SELECT
            sr.id,
            sr.request_ref,
            sr.full_name,
            sr.email,
            sr.phone,
            sr.project_title,
            sr.status,
            sr.created_at,
            s.full_name AS assigned_staff
         FROM service_requests sr
         LEFT JOIN staff s ON s.id = sr.assigned_staff_id
         ORDER BY sr.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Service requests are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Requests</p>
  <h2>Service requests</h2>
  <p>Quote and project enquiries should move from new request to approval and delivery through this workflow.</p>
</div>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($requests): ?>
  <div class="table-list">
    <?php foreach ($requests as $request): ?>
      <article class="table-item">
        <div>
          <strong><?= e($request['request_ref']) ?></strong>
          <span><?= e($request['full_name']) ?> | <?= e($request['project_title'] ?: 'General request') ?></span>
        </div>
        <div>
          <strong><?= e(ucwords(str_replace('_', ' ', (string) $request['status']))) ?></strong>
          <span><?= e(date_fmt((string) $request['created_at'])) ?></span>
        </div>
        <div>
          <span class="chip"><?= e($request['assigned_staff'] ?: 'Unassigned') ?></span>
        </div>
        <div class="table-actions">
          <a class="text-link" href="<?= SITE_URL ?>/public/admin/request-view.php?id=<?= e((string) $request['id']) ?>">Open request</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No service requests yet.</h3>
    <p>When request intake is connected, new opportunities will appear here.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
