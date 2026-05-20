<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Services';
$meta_desc  = 'Explore Joetech services for repairs, networking, digital support, and device supply.';

$hero = page_content('services', 'hero', [
    'subtitle' => 'Services',
    'title' => 'Support that helps people fix problems, choose the right technology, and keep work moving.',
    'body' => 'Joetech Solutions provides practical support across repairs, networking, digital services, and product supply. Each service path is designed to be clear from the first click to the final follow-up.',
]);

$sidebar = page_content('services', 'sidebar', [
    'subtitle' => 'What to expect',
    'items' => [
        'Clear explanations before technical work begins',
        'Support designed for real day-to-day business needs',
        'A request flow that routes work into the right admin follow-up process',
    ],
]);

$process = page_content('services', 'process', [
    'subtitle' => 'Our process',
    'title' => 'Small steps, practical results',
    'items' => [
        'You explain the problem, need, or goal.',
        'We guide you to the right service path and next action.',
        'Your request is reviewed, assigned, and followed through with clear communication.',
    ],
]);

$callout = page_content('services', 'callout', [
    'subtitle' => 'Start a request',
    'title' => 'Need help now? Send a structured request and we will follow up with the right next step.',
    'body' => 'Use the service request form for general support work or the repair intake form for device issues that need diagnosis and handling.',
]);

$service_groups = [];
try {
    $service_groups = db_all(
        "SELECT title, slug, description, price_from, currency, features
         FROM services
         WHERE status = 'active'
           AND deleted_at IS NULL
         ORDER BY sort_order ASC, title ASC"
    );
} catch (Throwable $e) {
    $service_groups = [];
}

include INCLUDES . '/header.php';
?>

<section class="hero service-hero">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block">
      <p class="eyebrow"><?= e($hero['subtitle']) ?></p>
      <h1><?= e($hero['title']) ?></h1>
      <p class="hero-copy"><?= e($hero['body']) ?></p>
      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/service-request.php">Request a service</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/repair-request.php">Book a repair</a>
      </div>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker"><?= e($sidebar['subtitle']) ?></p>
      <ul class="step-list">
        <?php foreach (($sidebar['items'] ?? []) as $point): ?>
          <li><?= e($point) ?></li>
        <?php endforeach; ?>
      </ul>
    </aside>
  </div>
</section>

<section class="section">
  <div class="wrapper">
    <div class="section-heading">
      <p class="eyebrow">Service areas</p>
      <h2>Clear categories for the work we do</h2>
      <p>Choose the area that best matches your need and we will take it from there.</p>
    </div>

    <div class="service-grid">
      <?php foreach ($service_groups as $group): ?>
        <?php $features = json_decode((string) ($group['features'] ?? ''), true); ?>
        <article class="service-card">
          <h3><?= e($group['title']) ?></h3>
          <p><?= e($group['description']) ?></p>
          <?php if ($group['price_from'] !== null): ?>
            <p><strong>From <?= e(money((float) $group['price_from'], (string) ($group['currency'] ?? 'UGX'))) ?></strong></p>
          <?php endif; ?>
          <?php if (is_array($features) && $features): ?>
            <ul class="feature-list">
              <?php foreach ($features as $item): ?>
                <li><?= e((string) $item) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrapper">
    <div class="section-heading">
      <p class="eyebrow"><?= e($process['subtitle']) ?></p>
      <h2><?= e($process['title']) ?></h2>
    </div>

    <div class="journey-grid">
      <?php foreach (($process['items'] ?? []) as $index => $step): ?>
        <article class="journey-card">
          <span class="journey-number">0<?= $index + 1 ?></span>
          <p><?= e($step) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrapper split-banner callout-banner">
    <div>
      <p class="eyebrow"><?= e($callout['subtitle']) ?></p>
      <h2><?= e($callout['title']) ?></h2>
      <p><?= e($callout['body']) ?></p>
    </div>
    <div class="hero-actions">
      <a class="button" href="<?= SITE_URL ?>/public/service-request.php">Request service</a>
      <a class="button button-secondary" href="<?= SITE_URL ?>/public/repair-request.php">Book a repair</a>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
