<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_repairs');

$page_title = 'New Repair Job';
$errors = [];
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach ($form as $key => $value) {
        $form[$key] = post($key, (string) $value);
    }

    if ($form['customer_name'] === '') $errors['customer_name'] = 'Enter the customer name.';
    if ($form['customer_phone'] === '') $errors['customer_phone'] = 'Enter the customer phone.';
    if ($form['device_type'] === '') $errors['device_type'] = 'Choose a device type.';
    if ($form['issue_description'] === '') $errors['issue_description'] = 'Describe the issue.';
    if ($form['customer_email'] !== '' && !filter_var($form['customer_email'], FILTER_VALIDATE_EMAIL)) $errors['customer_email'] = 'Enter a valid email address.';

    if (!$errors) {
        try {
            $repair_id = db_insert(
                "INSERT INTO repair_jobs
                (user_id, assigned_staff_id, repair_ref, customer_name, customer_email, customer_phone, device_type, brand, model, serial_number, issue_description)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    null,
                    (int) $_SESSION['staff_id'],
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

            flash('success', 'Repair job created.');
            redirect('public/admin/repair-view.php?id=' . (string) $repair_id);
        } catch (Throwable $e) {
            $errors['form'] = APP_ENV === 'development' ? 'Repair save failed: ' . $e->getMessage() : 'Could not create the repair job.';
        }
    }
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Repairs</p>
  <h2>Create repair job</h2>
  <p>Add a repair manually for walk-in devices, phone bookings, or internal intake.</p>
</div>

<div class="form-card">
  <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/repair-add.php" class="contact-form" novalidate>
    <?= csrf_field(); ?>

    <label for="customer_name">Customer name</label>
    <input id="customer_name" name="customer_name" type="text" value="<?= e($form['customer_name']) ?>">
    <?php if (isset($errors['customer_name'])): ?><p class="field-error"><?= e($errors['customer_name']) ?></p><?php endif; ?>

    <label for="customer_email">Customer email</label>
    <input id="customer_email" name="customer_email" type="email" value="<?= e($form['customer_email']) ?>">
    <?php if (isset($errors['customer_email'])): ?><p class="field-error"><?= e($errors['customer_email']) ?></p><?php endif; ?>

    <label for="customer_phone">Customer phone</label>
    <input id="customer_phone" name="customer_phone" type="text" value="<?= e($form['customer_phone']) ?>">
    <?php if (isset($errors['customer_phone'])): ?><p class="field-error"><?= e($errors['customer_phone']) ?></p><?php endif; ?>

    <label for="device_type">Device type</label>
    <select id="device_type" name="device_type">
      <option value="">Select device</option>
      <?php foreach (['Laptop', 'Desktop', 'Printer', 'Phone', 'Other'] as $device_type): ?>
        <option value="<?= e($device_type) ?>"<?= $form['device_type'] === $device_type ? ' selected' : '' ?>><?= e($device_type) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($errors['device_type'])): ?><p class="field-error"><?= e($errors['device_type']) ?></p><?php endif; ?>

    <label for="brand">Brand</label>
    <input id="brand" name="brand" type="text" value="<?= e($form['brand']) ?>">

    <label for="model">Model</label>
    <input id="model" name="model" type="text" value="<?= e($form['model']) ?>">

    <label for="serial_number">Serial number</label>
    <input id="serial_number" name="serial_number" type="text" value="<?= e($form['serial_number']) ?>">

    <label for="issue_description">Issue description</label>
    <textarea id="issue_description" name="issue_description" rows="6"><?= e($form['issue_description']) ?></textarea>
    <?php if (isset($errors['issue_description'])): ?><p class="field-error"><?= e($errors['issue_description']) ?></p><?php endif; ?>

    <button class="button" type="submit">Create repair job</button>
  </form>
</div>

<?php include INCLUDES . '/admin_footer.php'; ?>
