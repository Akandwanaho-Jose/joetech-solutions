<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_requests');

$request_id = get_int('id');
$request = null;
$db_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request_id > 0) {
    verify_csrf();

    $status = post('status');
    $assign_to_me = post('assign_to_me');
    $allowed_statuses = ['new', 'reviewing', 'quoted', 'approved', 'in_progress', 'completed', 'cancelled'];

    if (in_array($status, $allowed_statuses, true)) {
        try {
            db_run(
                "UPDATE service_requests
                 SET status = ?, assigned_staff_id = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    $status,
                    $assign_to_me === '1' ? (int) $_SESSION['staff_id'] : null,
                    $request_id,
                ]
            );

            $updated_request = db_one("SELECT * FROM service_requests WHERE id = ? LIMIT 1", [$request_id]);
            if ($updated_request) {
                notify_service_status($updated_request);
            }

            flash('success', 'Request updated.');
            redirect('public/admin/request-view.php?id=' . $request_id);
        } catch (Throwable $e) {
            $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Could not update this request.';
        }
    } else {
        $db_error = 'Choose a valid request status.';
    }
}

try {
    if ($request_id > 0) {
        $request = db_one(
            "SELECT
                sr.*,
                s.full_name AS assigned_staff,
                sv.title AS service_title
             FROM service_requests sr
             LEFT JOIN staff s ON s.id = sr.assigned_staff_id
             LEFT JOIN services sv ON sv.id = sr.service_id
             WHERE sr.id = ?
             LIMIT 1",
            [$request_id]
        );
    }
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Request details are temporarily unavailable.';
}

$page_title = $request ? 'Request ' . $request['request_ref'] : 'Request Detail';
include INCLUDES . '/admin_header.php';
?>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($request): ?>
  <div class="section-heading">
    <p class="eyebrow">Request Detail</p>
    <h2><?= e($request['request_ref']) ?></h2>
    <p><?= e($request['full_name']) ?> | <?= e($request['email']) ?><?php if (!empty($request['phone'])): ?> | <?= e($request['phone']) ?><?php endif; ?></p>
  </div>

  <div class="stat-strip">
    <article class="stat-card">
      <strong><?= e(ucwords(str_replace('_', ' ', (string) $request['status']))) ?></strong>
      <span>Status</span>
    </article>
    <article class="stat-card">
      <strong><?= e($request['assigned_staff'] ?: 'Unassigned') ?></strong>
      <span>Assigned staff</span>
    </article>
    <article class="stat-card">
      <strong><?= e($request['service_title'] ?: 'General enquiry') ?></strong>
      <span>Requested service</span>
    </article>
  </div>

  <div class="form-card">
    <h3>Update request</h3>
    <form method="post" action="<?= SITE_URL ?>/public/admin/request-view.php?id=<?= e((string) $request_id) ?>" class="contact-form">
      <?= csrf_field(); ?>

      <label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach (['new', 'reviewing', 'quoted', 'approved', 'in_progress', 'completed', 'cancelled'] as $status): ?>
          <option value="<?= e($status) ?>"<?= $request['status'] === $status ? ' selected' : '' ?>>
            <?= e(ucwords(str_replace('_', ' ', $status))) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label class="checkbox-row">
        <input type="checkbox" name="assign_to_me" value="1"<?= (int) ($request['assigned_staff_id'] ?? 0) === (int) $_SESSION['staff_id'] ? ' checked' : '' ?>>
        <span>Assign this request to me</span>
      </label>

      <button class="button" type="submit">Save changes</button>
    </form>
  </div>

  <div class="story-card">
    <h3>Request summary</h3>
    <?php if (!empty($request['company_name'])): ?><p><strong>Company:</strong> <?= e($request['company_name']) ?></p><?php endif; ?>
    <?php if (!empty($request['project_title'])): ?><p><strong>Project:</strong> <?= e($request['project_title']) ?></p><?php endif; ?>
    <?php if (!empty($request['budget_min']) || !empty($request['budget_max'])): ?>
      <p><strong>Budget:</strong> <?= $request['budget_min'] !== null ? e(money((float) $request['budget_min'])) : 'Open' ?> - <?= $request['budget_max'] !== null ? e(money((float) $request['budget_max'])) : 'Open' ?></p>
    <?php endif; ?>
    <?php if (!empty($request['deadline_date'])): ?><p><strong>Deadline:</strong> <?= e(date_fmt((string) $request['deadline_date'], 'd M Y')) ?></p><?php endif; ?>
    <p><?= nl2br(e((string) $request['description'])) ?></p>
  </div>

  <p><a class="text-link" href="<?= SITE_URL ?>/public/admin/requests.php">Back to requests</a></p>
<?php else: ?>
  <div class="empty-state">
    <h3>Request not found.</h3>
    <p>Return to the requests list and try again.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
