<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Repair Request';
$meta_desc = 'Book a repair intake with Joetech and send your device issue directly into the repair workflow.';
$errors = [];
$submitted_ref = get('ref');
$form = [
    'customer_name' => '',
    'customer_email' => '',
    'customer_phone' => '',
    'device_type' => '',
    'brand' => '',
    'model' => '',
    'serial_number' => '',
    'issue_description' => '',
];

$hero = page_content('repair_request', 'hero', [
    'subtitle' => 'Repair Intake',
    'title' => 'Book your device into the repair workflow.',
    'body' => 'This sends the issue into the admin repair board so the device can be received, diagnosed, repaired, and tracked.',
]);

$sidebar = page_content('repair_request', 'sidebar', [
    'subtitle' => 'Good for',
    'items' => [
        'Laptop and desktop faults',
        'Upgrade-related issues',
        'Devices that need diagnostics before repair',
    ],
]);

$flow = page_content('repair_request', 'flow', [
    'subtitle' => 'Repair Flow',
    'title' => 'Clear intake before technical work begins',
    'body' => 'Your repair request goes into the admin repair queue where it can be assigned, diagnosed, and updated through completion.',
    'success_title' => 'Repair booked',
    'success_body' => 'Keep this reference so Joetech can quickly trace your repair job during follow-up.',
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);

    if ($form['customer_name'] === '') $errors['customer_name'] = 'Please enter your name.';
    if ($form['customer_phone'] === '') $errors['customer_phone'] = 'Please enter your phone number.';
    if ($form['device_type'] === '') $errors['device_type'] = 'Please choose a device type.';
    if ($form['issue_description'] === '') $errors['issue_description'] = 'Please describe the issue.';
    if ($form['customer_email'] !== '' && !filter_var($form['customer_email'], FILTER_VALIDATE_EMAIL)) $errors['customer_email'] = 'Please enter a valid email address.';

    if (!$errors) {
        try {
            db_insert(
                "INSERT INTO repair_jobs
                (user_id, assigned_staff_id, repair_ref, customer_name, customer_email, customer_phone, device_type, brand, model, serial_number, issue_description)
                 VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $_SESSION['user_id'] ?? null,
                    $repair_ref = repair_ref(),
                    $form['customer_name'],
                    $form['customer_email'] !== '' ? $form['customer_email'] : null,
                    $form['customer_phone'],
                    $form['device_type'],
                    $form['brand'] !== '' ? $form['brand'] : null,
                    $form['model'] !== '' ? $form['model'] : null,
                    $form['serial_number'] !== '' ? $form['serial_number'] : null,
                    $form['issue_description'],
                ]
            );

            notify_repair_request($form, $repair_ref);

            flash('success', 'Your repair request has been received.');
            redirect('public/repair-request.php?ref=' . urlencode($repair_ref));
        } catch (Throwable $e) {
            $errors['form'] = APP_ENV === 'development' ? 'Repair save failed: ' . $e->getMessage() : 'We could not save your repair request right now.';
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="hero service-hero" data-reveal="up">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block">
      <p class="eyebrow"><?= e($hero['subtitle']) ?></p>
      <h1><?= e($hero['title']) ?></h1>
      <p class="hero-copy"><?= e($hero['body']) ?></p>
    </div>
    <aside class="hero-panel landing-panel" data-reveal="left">
      <p class="panel-kicker"><?= e($sidebar['subtitle']) ?></p>
      <ul class="step-list">
        <?php foreach (($sidebar['items'] ?? []) as $item): ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </aside>
  </div>
</section>

<section class="section" data-reveal="up">
  <div class="wrapper contact-grid">
    <div class="contact-copy" data-reveal="up">
      <div class="section-heading">
        <p class="eyebrow"><?= e($flow['subtitle']) ?></p>
        <h2><?= e($flow['title']) ?></h2>
        <p><?= e($flow['body']) ?></p>
      </div>

      <?php if ($submitted_ref !== ''): ?>
        <div class="story-card submission-card" data-reveal="up">
          <p class="eyebrow"><?= e($flow['success_title']) ?></p>
          <h3>Reference: <?= e($submitted_ref) ?></h3>
          <p><?= e($flow['success_body']) ?></p>
          <div class="hero-actions">
            <a class="button" href="<?= SITE_URL ?>/public/repair-status.php?ref=<?= e($submitted_ref) ?>">Track this repair</a>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
      <div class="form-intro">
        <p class="eyebrow">Device intake</p>
        <h3>Capture the fault clearly so the repair workflow starts with context.</h3>
      </div>

      <form method="post" action="<?= SITE_URL ?>/public/repair-request.php" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>
        <div class="form-section">
          <div class="form-section-head">
            <strong>Device details</strong>
            <span>Tell us what device is affected and what problem it is showing.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="device_type">Device type</label>
              <select id="device_type" name="device_type">
                <option value="">Select device</option>
                <?php foreach (['Laptop', 'Desktop', 'Printer', 'Phone', 'Other'] as $device_type): ?>
                  <option value="<?= e($device_type) ?>"<?= $form['device_type'] === $device_type ? ' selected' : '' ?>><?= e($device_type) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="field-hint">Choose the closest type so the repair queue is clearer.</p>
              <?php if (isset($errors['device_type'])): ?><p class="field-error"><?= e($errors['device_type']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="serial_number">Serial number</label>
              <input id="serial_number" name="serial_number" type="text" value="<?= e($form['serial_number']) ?>">
              <p class="field-hint">Optional, but useful for faster identification.</p>
            </div>

            <div class="field">
              <label for="brand">Brand</label>
              <input id="brand" name="brand" type="text" value="<?= e($form['brand']) ?>">
            </div>

            <div class="field">
              <label for="model">Model</label>
              <input id="model" name="model" type="text" value="<?= e($form['model']) ?>">
            </div>

            <div class="field field-wide">
              <label for="issue_description">Describe the issue</label>
              <textarea id="issue_description" name="issue_description" rows="6"><?= e($form['issue_description']) ?></textarea>
              <p class="field-hint">Mention symptoms, what happened before the fault, and any urgent business impact.</p>
              <?php if (isset($errors['issue_description'])): ?><p class="field-error"><?= e($errors['issue_description']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Contact details</strong>
            <span>We will use this to confirm intake, quotes, and status updates.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="customer_name">Full name</label>
              <input id="customer_name" name="customer_name" type="text" value="<?= e($form['customer_name']) ?>">
              <?php if (isset($errors['customer_name'])): ?><p class="field-error"><?= e($errors['customer_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="customer_email">Email</label>
              <input id="customer_email" name="customer_email" type="email" value="<?= e($form['customer_email']) ?>">
              <?php if (isset($errors['customer_email'])): ?><p class="field-error"><?= e($errors['customer_email']) ?></p><?php endif; ?>
            </div>

            <div class="field field-wide">
              <label for="customer_phone">Phone</label>
              <input id="customer_phone" name="customer_phone" type="text" value="<?= e($form['customer_phone']) ?>">
              <p class="field-hint">Best number for intake confirmation and repair updates.</p>
              <?php if (isset($errors['customer_phone'])): ?><p class="field-error"><?= e($errors['customer_phone']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-submit-row">
          <div class="form-submit-copy">
            <strong>Book repair</strong>
            <span>We will log the repair and give you a reference for tracking.</span>
          </div>
          <button class="button" type="submit">Book repair</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
