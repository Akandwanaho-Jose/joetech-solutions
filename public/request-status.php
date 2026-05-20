<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Track Request';
$meta_desc = 'Track a Joetech service request using your reference and email address.';
$errors = [];
$request = null;
$form = [
    'request_ref' => get('ref'),
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['request_ref'] = strtoupper(post('request_ref'));
    $form['email'] = post('email');

    if ($form['request_ref'] === '') $errors['request_ref'] = 'Enter your request reference.';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter the same email used on the request.';

    if (!$errors) {
        $request = db_one(
            "SELECT sr.request_ref, sr.full_name, sr.project_title, sr.status, sr.description, sr.created_at, sr.updated_at, s.title AS service_title
             FROM service_requests sr
             LEFT JOIN services s ON s.id = sr.service_id
             WHERE sr.request_ref = ? AND sr.email = ?
             LIMIT 1",
            [$form['request_ref'], $form['email']]
        );

        if (!$request) {
            $errors['form'] = 'We could not find a request with that reference and email.';
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper contact-grid">
    <div class="contact-copy">
      <div class="section-heading">
        <p class="eyebrow">Track Request</p>
        <h1>Check the current status of your service request.</h1>
        <p>Use the request reference and email address you used during submission.</p>
      </div>

      <?php if ($request): ?>
        <div class="story-card submission-card">
          <p class="eyebrow">Current status</p>
          <h3><?= e($request['request_ref']) ?></h3>
          <p><strong>Status:</strong> <?= e(ucwords(str_replace('_', ' ', (string) $request['status']))) ?></p>
          <p><strong>Service:</strong> <?= e($request['service_title'] ?: 'General request') ?></p>
          <?php if (!empty($request['project_title'])): ?><p><strong>Project:</strong> <?= e($request['project_title']) ?></p><?php endif; ?>
          <p><strong>Submitted:</strong> <?= e(date_fmt((string) $request['created_at'])) ?></p>
          <p><strong>Last update:</strong> <?= e(date_fmt((string) $request['updated_at'])) ?></p>
          <p><?= nl2br(e((string) $request['description'])) ?></p>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-card">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
      <form method="post" action="<?= SITE_URL ?>/public/request-status.php" class="contact-form" novalidate>
        <?= csrf_field(); ?>
        <label for="request_ref">Request reference</label>
        <input id="request_ref" name="request_ref" type="text" value="<?= e($form['request_ref']) ?>">
        <?php if (isset($errors['request_ref'])): ?><p class="field-error"><?= e($errors['request_ref']) ?></p><?php endif; ?>

        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="<?= e($form['email']) ?>">
        <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

        <button class="button" type="submit">Track request</button>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
