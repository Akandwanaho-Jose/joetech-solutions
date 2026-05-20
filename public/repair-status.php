<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Track Repair';
$meta_desc = 'Track a Joetech repair job using your reference and phone number.';
$errors = [];
$repair = null;
$form = [
    'repair_ref' => get('ref'),
    'customer_phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['repair_ref'] = strtoupper(post('repair_ref'));
    $form['customer_phone'] = post('customer_phone');

    if ($form['repair_ref'] === '') $errors['repair_ref'] = 'Enter your repair reference.';
    if ($form['customer_phone'] === '') $errors['customer_phone'] = 'Enter the same phone number used during intake.';

    if (!$errors) {
        $repair = db_one(
            "SELECT repair_ref, customer_name, device_type, brand, model, repair_status, diagnosis, estimated_cost, final_cost, received_at, updated_at
             FROM repair_jobs
             WHERE repair_ref = ? AND customer_phone = ?
             LIMIT 1",
            [$form['repair_ref'], $form['customer_phone']]
        );

        if (!$repair) {
            $errors['form'] = 'We could not find a repair job with that reference and phone number.';
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper contact-grid">
    <div class="contact-copy">
      <div class="section-heading">
        <p class="eyebrow">Track Repair</p>
        <h1>Check the current stage of your repair job.</h1>
        <p>Use the repair reference and phone number you used during intake.</p>
      </div>

      <?php if ($repair): ?>
        <div class="story-card submission-card">
          <p class="eyebrow">Current status</p>
          <h3><?= e($repair['repair_ref']) ?></h3>
          <p><strong>Status:</strong> <?= e(ucwords(str_replace('_', ' ', (string) $repair['repair_status']))) ?></p>
          <p><strong>Device:</strong> <?= e(trim(($repair['device_type'] ?? '') . ' ' . ($repair['brand'] ?? '') . ' ' . ($repair['model'] ?? ''))) ?></p>
          <p><strong>Received:</strong> <?= e(date_fmt((string) $repair['received_at'])) ?></p>
          <p><strong>Last update:</strong> <?= e(date_fmt((string) $repair['updated_at'])) ?></p>
          <?php if (!empty($repair['diagnosis'])): ?><p><strong>Diagnosis:</strong> <?= nl2br(e((string) $repair['diagnosis'])) ?></p><?php endif; ?>
          <?php if ($repair['estimated_cost'] !== null): ?><p><strong>Estimated cost:</strong> <?= e(money((float) $repair['estimated_cost'])) ?></p><?php endif; ?>
          <?php if ($repair['final_cost'] !== null): ?><p><strong>Final cost:</strong> <?= e(money((float) $repair['final_cost'])) ?></p><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-card">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
      <form method="post" action="<?= SITE_URL ?>/public/repair-status.php" class="contact-form" novalidate>
        <?= csrf_field(); ?>
        <label for="repair_ref">Repair reference</label>
        <input id="repair_ref" name="repair_ref" type="text" value="<?= e($form['repair_ref']) ?>">
        <?php if (isset($errors['repair_ref'])): ?><p class="field-error"><?= e($errors['repair_ref']) ?></p><?php endif; ?>

        <label for="customer_phone">Phone number</label>
        <input id="customer_phone" name="customer_phone" type="text" value="<?= e($form['customer_phone']) ?>">
        <?php if (isset($errors['customer_phone'])): ?><p class="field-error"><?= e($errors['customer_phone']) ?></p><?php endif; ?>

        <button class="button" type="submit">Track repair</button>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
