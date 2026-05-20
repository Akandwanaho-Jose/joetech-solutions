<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Contact';
$meta_desc  = 'Contact Joetech Solutions for repairs, devices, networking, and practical technology support.';

$hero = page_content('contact', 'hero', [
    'subtitle' => 'Contact',
    'title' => 'Tell us what you need and we will guide you to the right next step.',
    'body' => 'Use this page for general enquiries, product questions, business support requests, or any service that does not clearly fit the dedicated request and repair forms.',
]);

$sidebar = page_content('contact', 'sidebar', [
    'subtitle' => 'Best for',
    'items' => [
        'General business enquiries',
        'Product availability and pricing questions',
        'Support needs that require advice before the next action',
    ],
]);

$flow = page_content('contact', 'flow', [
    'subtitle' => 'Reach out',
    'title' => 'Start with a message, not a complicated process',
    'body' => 'We keep communication clear so customers can get help quickly and know where their enquiry is going.',
    'items' => [
        'Share the problem, service need, or product you are interested in.',
        'Your message is recorded so the team can review and follow up properly.',
        'We reply with the clearest next step, whether that is a quote, service path, or direct support.',
    ],
]);

$errors = [];
$form = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['name'] = post('name');
    $form['email'] = post('email');
    $form['phone'] = post('phone');
    $form['subject'] = post('subject');
    $form['message'] = post('message');

    if ($form['name'] === '') {
        $errors['name'] = 'Please enter your name.';
    }

    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($form['subject'] === '') {
        $errors['subject'] = 'Please enter a subject.';
    }

    if ($form['message'] === '') {
        $errors['message'] = 'Please enter your message.';
    }

    if (!$errors) {
        try {
            db_insert(
                'INSERT INTO contact_messages (user_id, assigned_staff_id, name, email, phone, subject, message, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $_SESSION['user_id'] ?? null,
                    null,
                    $form['name'],
                    $form['email'],
                    $form['phone'] !== '' ? $form['phone'] : null,
                    $form['subject'],
                    $form['message'],
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );

            notify_contact_message($form);

            flash('success', 'Your message has been sent. We will follow up as soon as possible.');
            redirect('public/contact.php');
        } catch (Throwable $e) {
            if (APP_ENV === 'development') {
                $errors['form'] = 'Database save failed: ' . $e->getMessage();
            } else {
                $errors['form'] = 'We could not send your message right now. Please try again later.';
            }
        }
    }
}

include INCLUDES . '/header.php';
?>

<section class="hero contact-hero" data-reveal="up">
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

      <div class="contact-points">
        <?php foreach (($flow['items'] ?? []) as $index => $item): ?>
          <article class="journey-card" data-reveal="up">
            <span class="journey-number">0<?= $index + 1 ?></span>
            <p><?= e($item) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <?php if (isset($errors['form'])): ?>
        <div class="inline-error"><?= e($errors['form']) ?></div>
      <?php endif; ?>

      <div class="form-intro">
        <p class="eyebrow">Send enquiry</p>
        <h3>We usually respond with the best next step, not a generic reply.</h3>
      </div>

      <form method="post" action="<?= SITE_URL ?>/public/contact.php" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Your details</strong>
            <span>So we know how to respond and follow up.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="name">Name</label>
              <input id="name" name="name" type="text" value="<?= e($form['name']) ?>" required>
              <p class="field-hint">Use the name you want us to address in the reply.</p>
              <?php if (isset($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= e($form['email']) ?>" required>
              <p class="field-hint">We will send updates and next steps to this address.</p>
              <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field field-wide">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="text" value="<?= e($form['phone']) ?>">
              <p class="field-hint">Optional, but useful if the request is urgent.</p>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Your message</strong>
            <span>A little context helps us guide you to the right service path.</span>
          </div>

          <div class="form-grid">
            <div class="field field-wide">
              <label for="subject">Subject</label>
              <input id="subject" name="subject" type="text" value="<?= e($form['subject']) ?>" required>
              <p class="field-hint">Example: office network setup, repair enquiry, product availability.</p>
              <?php if (isset($errors['subject'])): ?><p class="field-error"><?= e($errors['subject']) ?></p><?php endif; ?>
            </div>

            <div class="field field-wide">
              <label for="message">Message</label>
              <textarea id="message" name="message" rows="6" required><?= e($form['message']) ?></textarea>
              <p class="field-hint">Include the problem, business context, or what outcome you need.</p>
              <?php if (isset($errors['message'])): ?><p class="field-error"><?= e($errors['message']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-submit-row">
          <div class="form-submit-copy">
            <strong>Ready to send?</strong>
            <span>We will review the message and reply with the best next step.</span>
          </div>
          <button class="button" type="submit">Send message</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
