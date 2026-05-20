<?php
require_once __DIR__ . '/../includes/init.php';

if (logged_in()) {
    redirect('public/account.php');
}

$page_title = 'Login';
$meta_desc  = 'Log in to your Joetech account.';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = post('email');
    $password = post('password');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Please enter your password.';
    }

    if (!$errors) {
        $user = db_one(
            "SELECT * FROM users
             WHERE email = ?
               AND status = 'active'
               AND deleted_at IS NULL
             LIMIT 1",
            [$email]
        );

        if (!$user || !verify_password($password, $user['password_hash'])) {
            $errors['form'] = 'Invalid login details.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            db_run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);

            $redirect_to = $_SESSION['intended'] ?? SITE_URL . '/public/account.php';
            unset($_SESSION['intended']);

            flash('success', 'Welcome back.');
            header('Location: ' . $redirect_to);
            exit;
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper auth-grid">
    <div class="story-card" data-reveal="up">
      <p class="eyebrow">Account Login</p>
      <h1>Sign in to manage your account, orders, and requests.</h1>
      <p>Use your account to keep track of orders and any future personal activity on the site.</p>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
      <div class="form-intro">
        <p class="eyebrow">Welcome back</p>
        <h3>Access your orders, account details, and future tracking features.</h3>
      </div>

      <form method="post" action="<?= SITE_URL ?>/public/login.php" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>
        <div class="form-section">
          <div class="form-grid">
            <div class="field field-wide">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= e($email) ?>" required>
              <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field field-wide">
              <label for="password">Password</label>
              <input id="password" name="password" type="password" required>
              <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-submit-row">
          <div class="form-submit-copy">
            <strong>Secure sign in</strong>
            <span>Your account helps you track purchases and activity over time.</span>
          </div>
          <button class="button" type="submit">Log in</button>
        </div>
        <p><a class="text-link" href="<?= SITE_URL ?>/public/register.php">Create an account</a></p>
        <p><a class="text-link" href="<?= SITE_URL ?>/public/forgot-password.php">Forgot password?</a></p>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
