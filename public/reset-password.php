<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Reset Password';
$meta_desc  = 'Choose a new Joetech account password.';

if (logged_in()) {
    redirect('public/account.php');
}

$token = get('token');
$errors = [];
$reset = false;
$token_valid = false;

if ($token !== '') {
    $token_hash = hash('sha256', $token);
    $reset = db_one(
        "SELECT id, email
         FROM password_resets
         WHERE token_hash = ?
           AND used = 0
           AND expires_at > NOW()
         LIMIT 1",
        [$token_hash]
    );

    if ($reset) {
        $account_key = (string) $reset['email'];
        $account_type = str_starts_with($account_key, 'staff:') ? 'staff' : 'user';
        $account_email = preg_replace('/^(user|staff):/', '', $account_key);
        $table = $account_type === 'staff' ? 'staff' : 'users';

        $account = db_one(
            "SELECT id, full_name
             FROM {$table}
             WHERE email = ?
               AND status = 'active'
               AND deleted_at IS NULL
             LIMIT 1",
            [$account_email]
        );
        $token_valid = (bool) $account;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $token = post('token');
    $password = post('password');
    $password_confirm = post('password_confirm');
    $token_hash = hash('sha256', $token);

    $reset = db_one(
        "SELECT id, email
         FROM password_resets
         WHERE token_hash = ?
           AND used = 0
           AND expires_at > NOW()
         LIMIT 1",
        [$token_hash]
    );

    if (!$reset) {
        $errors['form'] = 'This reset link is invalid or has expired.';
    } else {
        $account_key = (string) $reset['email'];
        $account_type = str_starts_with($account_key, 'staff:') ? 'staff' : 'user';
        $account_email = preg_replace('/^(user|staff):/', '', $account_key);
        $table = $account_type === 'staff' ? 'staff' : 'users';

        $account = db_one(
            "SELECT id
             FROM {$table}
             WHERE email = ?
               AND status = 'active'
               AND deleted_at IS NULL
             LIMIT 1",
            [$account_email]
        );

        if (!$account) {
            $errors['form'] = 'This reset link is invalid or has expired.';
        }
    }

    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    if (!$errors && $reset && isset($account, $table)) {
        db()->beginTransaction();

        try {
            db_run(
                "UPDATE {$table} SET password_hash = ?, updated_at = NOW() WHERE id = ?",
                [hash_password($password), $account['id']]
            );
            db_run('UPDATE password_resets SET used = 1 WHERE id = ?', [$reset['id']]);
            db_run('UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0', [$reset['email']]);
            db()->commit();

            flash('success', 'Your password has been reset. Please log in with the new password.');
            redirect($account_type === 'staff' ? 'public/admin/login.php' : 'public/login.php');
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $errors['form'] = APP_ENV === 'development'
                ? 'Password reset failed: ' . $e->getMessage()
                : 'We could not reset your password right now.';
        }
    }

    $token_valid = !$errors || !isset($errors['form']);
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper auth-grid">
    <div class="story-card" data-reveal="up">
      <p class="eyebrow">New Password</p>
      <h1>Choose a fresh password for your account.</h1>
      <p>Use a password that is easy for you to remember and hard for someone else to guess.</p>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>

      <?php if (!$token_valid): ?>
        <div class="form-intro">
          <p class="eyebrow">Link expired</p>
          <h3>This reset link is invalid or has expired.</h3>
        </div>
        <p><a class="button" href="<?= SITE_URL ?>/public/forgot-password.php">Request a new link</a></p>
      <?php else: ?>
        <div class="form-intro">
          <p class="eyebrow">Reset password</p>
          <h3>Enter and confirm your new password.</h3>
        </div>

        <form method="post" action="<?= SITE_URL ?>/public/reset-password.php" class="contact-form form-shell" novalidate>
          <?= csrf_field(); ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">

          <div class="form-section">
            <div class="form-grid">
              <div class="field">
                <label for="password">New password</label>
                <input id="password" name="password" type="password" required>
                <p class="field-hint">At least 6 characters.</p>
                <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
              </div>

              <div class="field">
                <label for="password_confirm">Confirm password</label>
                <input id="password_confirm" name="password_confirm" type="password" required>
                <?php if (isset($errors['password_confirm'])): ?><p class="field-error"><?= e($errors['password_confirm']) ?></p><?php endif; ?>
              </div>
            </div>
          </div>

          <div class="form-submit-row">
            <div class="form-submit-copy">
              <strong>Save password</strong>
              <span>You will use this password the next time you log in.</span>
            </div>
            <button class="button" type="submit">Reset password</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
