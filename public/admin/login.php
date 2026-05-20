<?php
require_once __DIR__ . '/../../includes/init.php';

if (admin_logged_in()) {
    redirect('public/admin/index.php');
}

$page_title = 'Admin Login';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = post('email');
    $password = post('password');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid staff email.';
    }

    if ($password === '') {
        $errors['password'] = 'Enter your password.';
    }

    if (!$errors) {
        $staff = db_one(
            "SELECT s.id, s.full_name, s.email, s.password_hash, r.name AS role_name
             FROM staff s
             INNER JOIN roles r ON r.id = s.role_id
             WHERE s.email = ?
               AND s.status = 'active'
               AND s.deleted_at IS NULL
             LIMIT 1",
            [$email]
        );

        if (!$staff || !verify_password($password, $staff['password_hash'])) {
            $errors['form'] = 'Invalid staff login.';
        } else {
            $permissions = db_all(
                "SELECT p.perm_key
                 FROM role_permissions rp
                 INNER JOIN permissions p ON p.id = rp.permission_id
                 WHERE rp.role_id = (
                     SELECT role_id FROM staff WHERE id = ?
                 )",
                [$staff['id']]
            );

            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_role'] = $staff['role_name'];
            $_SESSION['staff_permissions'] = array_map(
                static fn(array $row): string => (string) $row['perm_key'],
                $permissions
            );

            db_run('UPDATE staff SET last_login_at = NOW() WHERE id = ?', [$staff['id']]);

            flash('success', 'Welcome to the admin area.');
            redirect('public/admin/index.php');
        }
    }
}

$app_css_path = ROOT . '/public/assets/css/app.css';
$app_css_version = is_file($app_css_path) ? filemtime($app_css_path) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> | <?= e(SITE_NAME) ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/public/assets/css/app.css?v=<?= e((string) $app_css_version) ?>">
</head>
<body>
<section class="section">
  <div class="wrapper auth-grid">
    <div class="story-card">
      <p class="eyebrow">Admin Access</p>
      <h1>Sign in to receive and manage customer orders.</h1>
      <p>This is the staff entry point for the ecommerce back office.</p>
    </div>

    <div class="form-card">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
      <form method="post" action="<?= SITE_URL ?>/public/admin/login.php" class="contact-form" novalidate>
        <?= csrf_field(); ?>

        <label for="email">Staff email</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" required>
        <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>
        <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>

        <button class="button" type="submit">Log in</button>
        <p><a class="text-link" href="<?= SITE_URL ?>/public/forgot-password.php?account=staff">Forgot staff password?</a></p>
      </form>
    </div>
  </div>
</section>
</body>
</html>
