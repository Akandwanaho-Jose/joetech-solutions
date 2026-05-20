<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_staff');

$staff_id = get_int('id');
$page_title = 'Edit Staff';
$errors = [];
$roles = db_all("SELECT id, name FROM roles ORDER BY id ASC");
$member = db_one("SELECT * FROM staff WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$staff_id]);

if (!$member) {
    flash('error', 'Staff account not found.');
    redirect('public/admin/staff.php');
}

$form = [
    'role_id' => (string) $member['role_id'],
    'full_name' => (string) $member['full_name'],
    'email' => (string) $member['email'],
    'phone' => (string) ($member['phone'] ?? ''),
    'password' => '',
    'status' => (string) $member['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);
    if ($form['role_id'] === '') $errors['role_id'] = 'Choose a role.';
    if ($form['full_name'] === '') $errors['full_name'] = 'Enter full name.';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if ($form['password'] !== '' && strlen($form['password']) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if (db_one("SELECT id FROM staff WHERE email = ? AND id <> ? AND deleted_at IS NULL LIMIT 1", [$form['email'], $staff_id])) $errors['email'] = 'This staff email already exists.';
    if (!$errors) {
        $sql = "UPDATE staff SET role_id = ?, full_name = ?, email = ?, phone = ?, status = ?";
        $params = [(int) $form['role_id'], $form['full_name'], $form['email'], $form['phone'] !== '' ? $form['phone'] : null, $form['status']];
        if ($form['password'] !== '') {
            $sql .= ", password_hash = ?";
            $params[] = hash_password($form['password']);
        }
        $sql .= " WHERE id = ?";
        $params[] = $staff_id;
        db_run($sql, $params);
        flash('success', 'Staff account updated.');
        redirect('public/admin/staff.php');
    }
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Admin</p><h2>Edit staff account</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted staff details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/staff-edit.php?id=<?= e((string) $staff_id) ?>" class="contact-form">
    <?= csrf_field(); ?>
    <label for="role_id">Role</label>
    <select id="role_id" name="role_id"><?php foreach ($roles as $role): ?><option value="<?= e((string) $role['id']) ?>"<?= $form['role_id'] === (string) $role['id'] ? ' selected' : '' ?>><?= e($role['name']) ?></option><?php endforeach; ?></select>
    <?php if (isset($errors['role_id'])): ?><p class="field-error"><?= e($errors['role_id']) ?></p><?php endif; ?>
    <label for="full_name">Full name</label><input id="full_name" name="full_name" type="text" value="<?= e($form['full_name']) ?>"><?= isset($errors['full_name']) ? '<p class="field-error">' . e($errors['full_name']) . '</p>' : '' ?>
    <label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($form['email']) ?>"><?= isset($errors['email']) ? '<p class="field-error">' . e($errors['email']) . '</p>' : '' ?>
    <label for="phone">Phone</label><input id="phone" name="phone" type="text" value="<?= e($form['phone']) ?>">
    <label for="password">New password</label><input id="password" name="password" type="password"><p class="field-hint">Leave blank to keep the current password.</p><?= isset($errors['password']) ? '<p class="field-error">' . e($errors['password']) . '</p>' : '' ?>
    <label for="status">Status</label>
    <select id="status" name="status"><?php foreach (['active', 'inactive', 'suspended'] as $status): ?><option value="<?= e($status) ?>"<?= $form['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select>
    <button class="button" type="submit">Save staff account</button>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
