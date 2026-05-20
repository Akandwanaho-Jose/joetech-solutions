<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Service Request';
$meta_desc = 'Request a Joetech service and send your project or support need into the admin workflow.';
$errors = [];
$services = [];
$submitted_ref = get('ref');
$selected_service_slug = get('service');
$selected_service_id = '';
$form = [
    'service_id' => '',
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'company_name' => '',
    'project_title' => '',
    'description' => '',
    'budget_min' => '',
    'budget_max' => '',
    'deadline_date' => '',
];

$hero = page_content('service_request', 'hero', [
    'subtitle' => 'Service Request',
    'title' => 'Tell us what you need built, fixed, or supported.',
    'body' => 'This form sends your request directly into the admin workflow so Joetech can review, quote, and follow up.',
]);

$sidebar = page_content('service_request', 'sidebar', [
    'subtitle' => 'Good for',
    'items' => [
        'Website and software enquiries',
        'Networking and office setup',
        'Business support requests that need follow-up',
    ],
]);

$flow = page_content('service_request', 'flow', [
    'subtitle' => 'Request Flow',
    'title' => 'Simple intake, clearer follow-up',
    'body' => 'Your request goes into the admin side where it can be assigned, reviewed, quoted, and tracked.',
    'success_title' => 'Request received',
    'success_body' => 'Keep this reference if you need to follow up with Joetech about this service request.',
]);

try {
    $services = db_all("SELECT id, title, slug FROM services WHERE status = 'active' AND deleted_at IS NULL ORDER BY sort_order ASC, title ASC");
    foreach ($services as $service) {
        if ($selected_service_slug !== '' && $service['slug'] === $selected_service_slug) {
            $selected_service_id = (string) $service['id'];
            $form['service_id'] = $selected_service_id;
            break;
        }
    }
} catch (Throwable $e) {
    $errors['form'] = APP_ENV === 'development' ? $e->getMessage() : 'Services are temporarily unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($errors['form'])) {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);

    if ($form['full_name'] === '') $errors['full_name'] = 'Please enter your name.';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if ($form['description'] === '') $errors['description'] = 'Please describe the service you need.';
    if ($form['budget_min'] !== '' && !is_numeric($form['budget_min'])) $errors['budget_min'] = 'Budget minimum must be a number.';
    if ($form['budget_max'] !== '' && !is_numeric($form['budget_max'])) $errors['budget_max'] = 'Budget maximum must be a number.';

    if (!$errors) {
        try {
            db_insert(
                "INSERT INTO service_requests
                (user_id, service_id, assigned_staff_id, request_ref, full_name, email, phone, company_name, project_title, description, budget_min, budget_max, deadline_date, source)
                 VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'website')",
                [
                    $_SESSION['user_id'] ?? null,
                    $form['service_id'] !== '' ? (int) $form['service_id'] : null,
                    $request_ref = request_ref(),
                    $form['full_name'],
                    $form['email'],
                    $form['phone'] !== '' ? $form['phone'] : null,
                    $form['company_name'] !== '' ? $form['company_name'] : null,
                    $form['project_title'] !== '' ? $form['project_title'] : null,
                    $form['description'],
                    $form['budget_min'] !== '' ? (float) $form['budget_min'] : null,
                    $form['budget_max'] !== '' ? (float) $form['budget_max'] : null,
                    $form['deadline_date'] !== '' ? $form['deadline_date'] : null,
                ]
            );

            notify_service_request($form, $request_ref);

            flash('success', 'Your service request has been received.');
            redirect('public/service-request.php?ref=' . urlencode($request_ref));
        } catch (Throwable $e) {
            $errors['form'] = APP_ENV === 'development' ? 'Request save failed: ' . $e->getMessage() : 'We could not save your request right now.';
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
            <a class="button" href="<?= SITE_URL ?>/public/request-status.php?ref=<?= e($submitted_ref) ?>">Track this request</a>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-card form-card-modern" data-reveal="left">
      <?php if (isset($errors['form'])): ?><div class="inline-error"><?= e($errors['form']) ?></div><?php endif; ?>
      <div class="form-intro">
        <p class="eyebrow">Project intake</p>
        <h3>Give us enough context to quote and guide you properly.</h3>
      </div>

      <form method="post" action="<?= SITE_URL ?>/public/service-request.php" class="contact-form form-shell" novalidate>
        <?= csrf_field(); ?>
        <div class="form-section">
          <div class="form-section-head">
            <strong>Request details</strong>
            <span>Start with the service type and the problem you want solved.</span>
          </div>

          <div class="form-grid">
            <div class="field field-wide">
              <label for="service_id">Service</label>
              <select id="service_id" name="service_id">
                <option value="">Select service</option>
                <?php foreach ($services as $service): ?>
                  <option value="<?= e((string) $service['id']) ?>"<?= $form['service_id'] === (string) $service['id'] ? ' selected' : '' ?>><?= e($service['title']) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="field-hint">Choose the closest fit. We can still guide you if the scope changes.</p>
            </div>

            <div class="field field-wide">
              <label for="description">Describe what you need</label>
              <textarea id="description" name="description" rows="6"><?= e($form['description']) ?></textarea>
              <p class="field-hint">Mention the business need, current problem, and what success looks like.</p>
              <?php if (isset($errors['description'])): ?><p class="field-error"><?= e($errors['description']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="project_title">Project title</label>
              <input id="project_title" name="project_title" type="text" value="<?= e($form['project_title']) ?>">
              <p class="field-hint">Optional, but useful for larger requests.</p>
            </div>

            <div class="field">
              <label for="company_name">Company</label>
              <input id="company_name" name="company_name" type="text" value="<?= e($form['company_name']) ?>">
              <p class="field-hint">Add your business name if this is a team or office request.</p>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-head">
            <strong>Contact and budget</strong>
            <span>Tell us who to reach and the range you are planning for.</span>
          </div>

          <div class="form-grid">
            <div class="field">
              <label for="full_name">Full name</label>
              <input id="full_name" name="full_name" type="text" value="<?= e($form['full_name']) ?>">
              <?php if (isset($errors['full_name'])): ?><p class="field-error"><?= e($errors['full_name']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= e($form['email']) ?>">
              <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="text" value="<?= e($form['phone']) ?>">
              <p class="field-hint">Useful for faster quotation follow-up.</p>
            </div>

            <div class="field">
              <label for="deadline_date">Preferred deadline</label>
              <input id="deadline_date" name="deadline_date" type="date" value="<?= e($form['deadline_date']) ?>">
              <p class="field-hint">Share when you ideally want the work started or delivered.</p>
            </div>

            <div class="field">
              <label for="budget_min">Budget min</label>
              <input id="budget_min" name="budget_min" type="text" value="<?= e($form['budget_min']) ?>">
              <?php if (isset($errors['budget_min'])): ?><p class="field-error"><?= e($errors['budget_min']) ?></p><?php endif; ?>
            </div>

            <div class="field">
              <label for="budget_max">Budget max</label>
              <input id="budget_max" name="budget_max" type="text" value="<?= e($form['budget_max']) ?>">
              <?php if (isset($errors['budget_max'])): ?><p class="field-error"><?= e($errors['budget_max']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>

        <div class="form-submit-row">
          <div class="form-submit-copy">
            <strong>Submit request</strong>
            <span>We will review the scope and come back with the clearest next step.</span>
          </div>
          <button class="button" type="submit">Send request</button>
        </div>
      </form>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
