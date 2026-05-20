<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Forgot Password';
$meta_desc  = 'Request a password reset link for your Joetech account.';

if (logged_in()) {
    redirect('public/account.php');
}

$errors = [];
$email = '';
$notice = '';
$dev_reset_link = '';
$account_type = get('account') === 'staff' ? 'staff' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(post('email'));
    $account_type = post('account_type') === 'staff' ? 'staff' : 'user';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        if ($account_type === 'staff') {
            $account = db_one(
                "SELECT id, full_name, email
                 FROM staff
                 WHERE email = ?
                   AND status = 'active'
                   AND deleted_at IS NULL
                 LIMIT 1",
                [$email]
            );
        } else {
            $account = db_one(
                "SELECT id, full_name, email
                 FROM users
                 WHERE email = ?
                   AND status = 'active'
                   AND deleted_at IS NULL
                 LIMIT 1",
                [$email]
            );
        }

        if ($account) {
            $token = generate_token();
            $token_hash = hash('sha256', $token);
            $account_key = $account_type . ':' . $account['email'];
            $reset_link = SITE_URL . '/public/reset-password.php?token=' . urlencode($token);

            db_run('UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0', [$account_key]);
            db_insert(
                'INSERT INTO password_resets (email, token_hash, expires_at, used)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)',
                [$account_key, $token_hash]
            );

            $subject = 'Reset your Joetech password';
            $message = "Hello " . $account['full_name'] . ",\n\n"
                . "Use this link to reset your Joetech account password:\n"
                . $reset_link . "\n\n"
                . "This link expires in 1 hour. If you did not request it, you can ignore this email.\n\n"
                . SITE_NAME;
            $sent = send_app_mail((string) $account['email'], $subject, $message, SITE_EMAIL);

            if (!$sent && APP_ENV === 'development') {
                $dev_reset_link = $reset_link;
            }
        }

        $notice = 'If that email belongs to an active account, we have sent a password reset link.';
        $email = '';
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper auth-grid">
    <div class="story-card" data-reveal="up">
      <p class="eyebrow">Password Help</p>
      <h1>Get a secure link to reset your <?= $account_type === 'staff' ? 'staff' : 'account' ?> password.</h1>
      <p>Enter the email address connected to your Joetech <?= $account_type === 'staff' ? 'staff account' : 'account' ?> and we will send a reset link if it is active.</p>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <?php if ($notice): ?><div class="flash flash-success"><?= e($notice) ?></div><?php endif; ?>
      <?php if ($dev_reset_link): ?>
        <div class="flash flash-info">
          Email is not configured locally. Development reset link:
          <a class="text-link" href="<?= e($dev_reset_link) ?>">Open reset link</a>
        </div>
      <?php endif; ?>

      <div class="form-intro">
        <p class="eyebrow">Reset password</p>
        <h3>We will send a time-limited reset link.</h3>
      </div>

      <form method="post" action="<?= SITE_URL ?>/public/forgot-password.php<?= $account_type === 'staff' ? '?account=staff' : '' ?>" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>
        <input type="hidden" name="account_type" value="<?= e($account_type) ?>">
        <div class="form-section">
          <div class="form-grid">
            <div class="field field-wide">
              <label for="email">Account email</label>
              <input id="email" name="email" type="email" value="<?= e($email) ?>" required>
              <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-submit-row">
          <div class="form-submit-copy">
            <strong>Send reset link</strong>
            <span>The link expires after 1 hour.</span>
          </div>
          <button class="button" type="submit">Send link</button>
        </div>
        <p><a class="text-link" href="<?= SITE_URL ?>/public/login.php">Back to login</a></p>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
