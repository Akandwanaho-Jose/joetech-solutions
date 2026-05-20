<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_staff');

$page_title = 'Add Staff';
$errors = [];
$roles = db_all("SELECT id, name FROM roles ORDER BY id ASC");
$form = [
    'role_id' => '',
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'status' => 'active',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);
    if ($form['role_id'] === '') $errors['role_id'] = 'Choose a role.';
    if ($form['full_name'] === '') $errors['full_name'] = 'Enter full name.';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if ($form['password'] === '' || strlen($form['password']) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if (db_one("SELECT id FROM staff WHERE email = ? AND deleted_at IS NULL LIMIT 1", [$form['email']])) $errors['email'] = 'This staff email already exists.';
    if (!$errors) {
      db_insert(
        "INSERT INTO staff (role_id, full_name, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)",
        [(int) $form['role_id'], $form['full_name'], $form['email'], $form['phone'] !== '' ? $form['phone'] : null, hash_password($form['password']), $form['status']]
      );
      flash('success', 'Staff account created.');
      redirect('public/admin/staff.php');
    }
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Admin</p><h2>Add staff account</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted staff details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/staff-add.php" class="contact-form">
    <?= csrf_field(); ?>
    <label for="role_id">Role</label>
    <select id="role_id" name="role_id"><option value="">Select role</option><?php foreach ($roles as $role): ?><option value="<?= e((string) $role['id']) ?>"<?= $form['role_id'] === (string) $role['id'] ? ' selected' : '' ?>><?= e($role['name']) ?></option><?php endforeach; ?></select>
    <?php if (isset($errors['role_id'])): ?><p class="field-error"><?= e($errors['role_id']) ?></p><?php endif; ?>
    <label for="full_name">Full name</label><input id="full_name" name="full_name" type="text" value="<?= e($form['full_name']) ?>"><?= isset($errors['full_name']) ? '<p class="field-error">' . e($errors['full_name']) . '</p>' : '' ?>
    <label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($form['email']) ?>"><?= isset($errors['email']) ? '<p class="field-error">' . e($errors['email']) . '</p>' : '' ?>
    <label for="phone">Phone</label><input id="phone" name="phone" type="text" value="<?= e($form['phone']) ?>">
    <label for="password">Password</label><input id="password" name="password" type="password"><?= isset($errors['password']) ? '<p class="field-error">' . e($errors['password']) . '</p>' : '' ?>
    <label for="status">Status</label>
    <select id="status" name="status"><?php foreach (['active', 'inactive', 'suspended'] as $status): ?><option value="<?= e($status) ?>"<?= $form['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select>
    <button class="button" type="submit">Create staff account</button>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
