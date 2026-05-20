<?php
require_once __DIR__ . '/../includes/init.php';

if (logged_in()) {
    redirect('public/account.php');
}

$page_title = 'Register';
$meta_desc  = 'Create a Joetech customer account.';
$errors = [];
$form = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['full_name'] = post('full_name');
    $form['email'] = post('email');
    $form['phone'] = post('phone');
    $password = post('password');
    $password_confirm = post('password_confirm');

    if ($form['full_name'] === '') {
        $errors['full_name'] = 'Please enter your full name.';
    }

    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Passwords do not match.';
    }

    if (!$errors) {
        $existing = db_one('SELECT id FROM users WHERE email = ? LIMIT 1', [$form['email']]);
        if ($existing) {
            $errors['email'] = 'That email is already registered.';
        } else {
            $user_id = db_insert(
                "INSERT INTO users (full_name, email, phone, password_hash, email_verified, status)
                 VALUES (?, ?, ?, ?, 0, 'active')",
                [
                    $form['full_name'],
                    $form['email'],
                    $form['phone'] !== '' ? $form['phone'] : null,
                    hash_password($password),
                ]
            );

            $_SESSION['user_id'] = $user_id;
            flash('success', 'Your account has been created.');
            redirect('public/account.php');
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="section">
  <div class="wrapper auth-grid">
    <div class="story-card" data-reveal="up">
      <p class="eyebrow">Create Account</p>
      <h1>Create an account to track orders and service activity.</h1>
      <p>As more features are added, your account will give you a clear place to track orders and future requests.</p>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <div class="form-intro">
        <p class="eyebrow">Create account</p>
        <h3>Set up your Joetech account for faster checkout and future tracking.</h3>
      </div>

      <form method="post" action="<?= SITE_URL ?>/public/register.php" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>
        <div class="form-section">
          <div class="form-section-head">
            <strong>Personal details</strong>
            <span>Use the same details you want associated with orders and requests.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="full_name">Full name</label>
              <input id="full_name" name="full_name" type="text" value="<?= e($form['full_name']) ?>" required>
              <?php if (isset($errors['full_name'])): ?><p class="field-error"><?= e($errors['full_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= e($form['email']) ?>" required>
              <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field field-wide">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="text" value="<?= e($form['phone']) ?>">
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Security</strong>
            <span>Create a password you can remember and keep private.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="password">Password</label>
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
            <strong>Create account</strong>
            <span>Once registered, you can track orders and future customer activity more easily.</span>
          </div>
          <button class="button" type="submit">Create account</button>
        </div>
        <p><a class="text-link" href="<?= SITE_URL ?>/public/login.php">Already have an account?</a></p>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
