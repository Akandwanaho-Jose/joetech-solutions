<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_repairs');

$repair_id = get_int('id');
$repair = null;
$db_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $repair_id > 0) {
    verify_csrf();

    $status = post('repair_status');
    $diagnosis = post('diagnosis');
    $estimated_cost = post('estimated_cost');
    $final_cost = post('final_cost');
    $notes = post('notes');
    $assign_to_me = post('assign_to_me');

    $allowed_statuses = ['received', 'diagnosing', 'awaiting_approval', 'repairing', 'ready', 'collected', 'cancelled'];

    if (!in_array($status, $allowed_statuses, true)) {
        $db_error = 'Choose a valid repair status.';
    } elseif (($estimated_cost !== '' && !is_numeric($estimated_cost)) || ($final_cost !== '' && !is_numeric($final_cost))) {
        $db_error = 'Repair costs must be valid numbers.';
    } else {
        try {
            db_run(
                "UPDATE repair_jobs
                 SET assigned_staff_id = ?,
                     diagnosis = ?,
                     repair_status = ?,
                     estimated_cost = ?,
                     final_cost = ?,
                     notes = ?,
                     completed_at = CASE WHEN ? = 'ready' THEN COALESCE(completed_at, NOW()) ELSE completed_at END,
                     collected_at = CASE WHEN ? = 'collected' THEN COALESCE(collected_at, NOW()) ELSE collected_at END,
                     updated_at = NOW()
                 WHERE id = ?",
                [
                    $assign_to_me === '1' ? (int) $_SESSION['staff_id'] : null,
                    $diagnosis !== '' ? $diagnosis : null,
                    $status,
                    $estimated_cost !== '' ? (float) $estimated_cost : null,
                    $final_cost !== '' ? (float) $final_cost : null,
                    $notes !== '' ? $notes : null,
                    $status,
                    $status,
                    $repair_id,
                ]
            );

            $updated_repair = db_one("SELECT * FROM repair_jobs WHERE id = ? LIMIT 1", [$repair_id]);
            if ($updated_repair) {
                notify_repair_status($updated_repair);
            }

            flash('success', 'Repair job updated.');
            redirect('public/admin/repair-view.php?id=' . $repair_id);
        } catch (Throwable $e) {
            $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Could not update this repair job.';
        }
    }
}

try {
    if ($repair_id > 0) {
        $repair = db_one(
            "SELECT
                rj.*,
                s.full_name AS assigned_staff
             FROM repair_jobs rj
             LEFT JOIN staff s ON s.id = rj.assigned_staff_id
             WHERE rj.id = ?
             LIMIT 1",
            [$repair_id]
        );
    }
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Repair details are temporarily unavailable.';
}

$page_title = $repair ? 'Repair ' . $repair['repair_ref'] : 'Repair Detail';
include INCLUDES . '/admin_header.php';
?>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($repair): ?>
  <div class="section-heading">
    <p class="eyebrow">Repair Detail</p>
    <h2><?= e($repair['repair_ref']) ?></h2>
    <p><?= e($repair['customer_name']) ?> | <?= e($repair['customer_phone']) ?><?php if (!empty($repair['customer_email'])): ?> | <?= e($repair['customer_email']) ?><?php endif; ?></p>
  </div>

  <div class="stat-strip">
    <article class="stat-card">
      <strong><?= e(ucwords(str_replace('_', ' ', (string) $repair['repair_status']))) ?></strong>
      <span>Status</span>
    </article>
    <article class="stat-card">
      <strong><?= e($repair['assigned_staff'] ?: 'Unassigned') ?></strong>
      <span>Assigned staff</span>
    </article>
    <article class="stat-card">
      <strong><?= e($repair['device_type']) ?></strong>
      <span><?= e(trim(($repair['brand'] ?? '') . ' ' . ($repair['model'] ?? ''))) ?></span>
    </article>
  </div>

  <div class="form-card">
    <h3>Update repair job</h3>
    <form method="post" action="<?= SITE_URL ?>/public/admin/repair-view.php?id=<?= e((string) $repair_id) ?>" class="contact-form">
      <?= csrf_field(); ?>

      <label for="repair_status">Repair status</label>
      <select id="repair_status" name="repair_status">
        <?php foreach (['received', 'diagnosing', 'awaiting_approval', 'repairing', 'ready', 'collected', 'cancelled'] as $status): ?>
          <option value="<?= e($status) ?>"<?= $repair['repair_status'] === $status ? ' selected' : '' ?>>
            <?= e(ucwords(str_replace('_', ' ', $status))) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label class="checkbox-row">
        <input type="checkbox" name="assign_to_me" value="1"<?= (int) ($repair['assigned_staff_id'] ?? 0) === (int) $_SESSION['staff_id'] ? ' checked' : '' ?>>
        <span>Assign this repair to me</span>
      </label>

      <label for="diagnosis">Diagnosis</label>
      <textarea id="diagnosis" name="diagnosis" rows="3"><?= e((string) ($repair['diagnosis'] ?? '')) ?></textarea>

      <label for="estimated_cost">Estimated cost</label>
      <input id="estimated_cost" name="estimated_cost" type="text" value="<?= e((string) ($repair['estimated_cost'] ?? '')) ?>">

      <label for="final_cost">Final cost</label>
      <input id="final_cost" name="final_cost" type="text" value="<?= e((string) ($repair['final_cost'] ?? '')) ?>">

      <label for="notes">Internal notes</label>
      <textarea id="notes" name="notes" rows="4"><?= e((string) ($repair['notes'] ?? '')) ?></textarea>

      <button class="button" type="submit">Save changes</button>
    </form>
  </div>

  <div class="story-card">
    <h3>Device issue</h3>
    <?php if (!empty($repair['serial_number'])): ?><p><strong>Serial:</strong> <?= e($repair['serial_number']) ?></p><?php endif; ?>
    <p><?= nl2br(e((string) $repair['issue_description'])) ?></p>
  </div>

  <p><a class="text-link" href="<?= SITE_URL ?>/public/admin/repairs.php">Back to repairs</a></p>
<?php else: ?>
  <div class="empty-state">
    <h3>Repair job not found.</h3>
    <p>Return to the repairs list and try again.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
