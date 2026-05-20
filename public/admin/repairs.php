<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_repairs');

$page_title = 'Repair Jobs';
$repairs = [];
$db_error = null;

try {
    $repairs = db_all(
        "SELECT
            rj.id,
            rj.repair_ref,
            rj.customer_name,
            rj.customer_phone,
            rj.device_type,
            rj.brand,
            rj.model,
            rj.repair_status,
            rj.received_at,
            s.full_name AS assigned_staff
         FROM repair_jobs rj
         LEFT JOIN staff s ON s.id = rj.assigned_staff_id
         ORDER BY rj.received_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Repair jobs are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Repairs</p>
  <h2>Repair jobs</h2>
  <p>Track each device from received to diagnosis, repair, readiness, and collection.</p>
</div>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($repairs): ?>
  <div class="table-list">
    <?php foreach ($repairs as $repair): ?>
      <article class="table-item">
        <div>
          <strong><?= e($repair['repair_ref']) ?></strong>
          <span><?= e($repair['customer_name']) ?> | <?= e(trim(($repair['brand'] ?? '') . ' ' . ($repair['model'] ?? ''))) ?></span>
        </div>
        <div>
          <strong><?= e(ucwords(str_replace('_', ' ', (string) $repair['repair_status']))) ?></strong>
          <span><?= e($repair['device_type']) ?> | <?= e(date_fmt((string) $repair['received_at'])) ?></span>
        </div>
        <div>
          <span class="chip"><?= e($repair['assigned_staff'] ?: 'Unassigned') ?></span>
        </div>
        <div class="table-actions">
          <a class="text-link" href="<?= SITE_URL ?>/public/admin/repair-view.php?id=<?= e((string) $repair['id']) ?>">Open repair</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No repair jobs yet.</h3>
    <p>When repair intake is connected, active device jobs will appear here.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
