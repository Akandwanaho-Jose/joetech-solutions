<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'About Us';
$meta_desc  = 'Learn about Joetech Solutions, the way we work, and the support we provide.';

$hero = page_content('about', 'hero', [
    'subtitle' => 'About Joetech',
    'title' => 'A practical technology partner for people, teams, and growing businesses.',
    'body' => 'Joetech Solutions exists to make technology more useful, more dependable, and easier to work with. We focus on repairs, networking, device supply, and digital support that solve real problems without unnecessary complexity.',
]);

$principles = page_content('about', 'principles', [
    'subtitle' => 'Our principles',
    'items' => [
        'Keep technology practical, understandable, and worth the investment.',
        'Recommend the simplest reliable path instead of unnecessary complexity.',
        'Support both individual clients and growing organizations with the same care.',
    ],
]);

$story = page_content('about', 'story', [
    'subtitle' => 'How we work',
    'title' => 'Technology should solve problems without creating new confusion.',
    'body' => 'Our approach is built around clear advice, practical implementation, and support that makes sense for the way clients actually work. We care about outcomes that remain dependable after the initial job is done.',
    'body_2' => 'Whether the work is device repair, office connectivity, product supply, or digital support, the goal is the same: help the client move forward with confidence.',
]);

$steps = page_content('about', 'steps', [
    'items' => [
        'Understand the problem clearly before recommending the solution.',
        'Choose the most reliable and maintainable path that fits the real need.',
        'Deliver support that remains useful after installation, repair, or setup.',
    ],
]);

$strengths = page_content('about', 'strengths', [
    'subtitle' => 'What we bring',
    'title' => 'Core strengths behind our work',
    'items' => [
        ['title' => 'Repair and Recovery', 'text' => 'Helping devices return to dependable, productive use through diagnostics, replacement, upgrades, and cleanup.'],
        ['title' => 'Business Support', 'text' => 'Supporting offices, schools, shops, and teams with stable technology choices and day-to-day operational support.'],
        ['title' => 'Digital Enablement', 'text' => 'Giving businesses practical websites, setup help, and digital systems they can keep managing after launch.'],
    ],
]);

include INCLUDES . '/header.php';
?>

<section class="hero about-hero">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block">
      <p class="eyebrow"><?= e($hero['subtitle']) ?></p>
      <h1><?= e($hero['title']) ?></h1>
      <p class="hero-copy"><?= e($hero['body']) ?></p>
      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/contact.php">Talk to us</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/services.php">Explore services</a>
      </div>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker"><?= e($principles['subtitle']) ?></p>
      <ul class="step-list">
        <?php foreach (($principles['items'] ?? []) as $principle): ?>
          <li><?= e($principle) ?></li>
        <?php endforeach; ?>
      </ul>
    </aside>
  </div>
</section>

<section class="section">
  <div class="wrapper about-grid">
    <div class="story-card">
      <p class="eyebrow"><?= e($story['subtitle']) ?></p>
      <h2><?= e($story['title']) ?></h2>
      <p><?= e($story['body']) ?></p>
      <p><?= e($story['body_2']) ?></p>
    </div>

    <div class="journey-grid">
      <?php foreach (($steps['items'] ?? []) as $index => $step): ?>
        <article class="journey-card">
          <span class="journey-number">0<?= $index + 1 ?></span>
          <p><?= e($step) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrapper">
    <div class="section-heading">
      <p class="eyebrow"><?= e($strengths['subtitle']) ?></p>
      <h2><?= e($strengths['title']) ?></h2>
    </div>

    <div class="card-grid">
      <?php foreach (($strengths['items'] ?? []) as $strength): ?>
        <article class="card accent-card">
          <h3><?= e($strength['title']) ?></h3>
          <p><?= e($strength['text']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
