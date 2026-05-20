<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Home';
$meta_desc  = 'Joetech Solutions delivers repairs, networking, devices, and practical digital support in Mbarara.';

$hero = page_content('home', 'hero', [
    'subtitle' => 'Joetech Solutions',
    'title' => 'Technology support that keeps people working and businesses moving.',
    'body' => 'From repairs and networking to device supply and digital support, Joetech Solutions helps customers solve real technology problems with clear communication, practical choices, and dependable follow-up.',
    'actions' => [
        ['label' => 'Request service', 'url' => SITE_URL . '/public/service-request.php', 'style' => 'primary'],
        ['label' => 'Book repair', 'url' => SITE_URL . '/public/repair-request.php', 'style' => 'secondary'],
        ['label' => 'Browse products', 'url' => SITE_URL . '/public/shop.php', 'style' => 'secondary'],
        ['label' => 'Track request', 'url' => SITE_URL . '/public/request-status.php', 'style' => 'secondary'],
    ],
    'stats' => [
        ['title' => 'Repairs', 'text' => 'Laptops, desktops, accessories, and upgrades'],
        ['title' => 'Support', 'text' => 'For homes, teams, shops, schools, and offices'],
        ['title' => 'Supply', 'text' => 'Devices, components, and practical tech guidance'],
    ],
]);

$highlights = page_content('home', 'highlights', [
    'subtitle' => 'Why clients choose us',
    'title' => 'Clear help, practical solutions, and a process people can follow.',
    'items' => [
        'Clear service paths for repairs, requests, product enquiries, and tracking',
        'Business-focused support built for homes, shops, schools, and growing teams',
        'A structured admin workflow behind each order, message, request, and repair',
    ],
    'link_label' => 'Learn more about our approach',
    'link_url' => SITE_URL . '/public/about.php',
]);

$journey = page_content('home', 'journey', [
    'subtitle' => 'How it works',
    'title' => 'A straightforward customer journey',
    'body' => 'Good service starts by removing friction and guiding customers to the right action from the first page.',
    'items' => [
        'Choose the service, repair, or product path that matches your need.',
        'Send the request, place the order, or ask for guidance through a clear form.',
        'Receive follow-up, updates, and practical support from the Joetech team.',
    ],
]);

$callout = page_content('home', 'callout', [
    'subtitle' => 'Ready to start?',
    'title' => 'Tell us what you need and we will guide you to the right next step.',
    'body' => 'Use a service request for support work, book a repair for device issues, or browse the shop if you already know what you need.',
    'actions' => [
        ['label' => 'Request service', 'url' => SITE_URL . '/public/service-request.php', 'style' => 'primary'],
        ['label' => 'Book repair', 'url' => SITE_URL . '/public/repair-request.php', 'style' => 'secondary'],
        ['label' => 'Contact Joetech', 'url' => SITE_URL . '/public/contact.php', 'style' => 'secondary'],
    ],
]);

$services = [];
$testimonials = [];
try {
    $services = db_all(
        "SELECT title, slug, description, features
         FROM services
         WHERE status = 'active'
           AND deleted_at IS NULL
         ORDER BY sort_order ASC, title ASC
         LIMIT 4"
    );

    $testimonials = db_all(
        "SELECT t.full_name, t.company_name, t.content, t.rating, p.title AS project_title
         FROM testimonials t
         LEFT JOIN portfolio_projects p ON p.id = t.project_id
         WHERE t.status = 'approved'
         ORDER BY t.is_featured DESC, t.created_at DESC
         LIMIT 5"
    );
} catch (Throwable $e) {
    $services = [];
    $testimonials = [];
}

include INCLUDES . '/header.php';
?>

<section class="hero landing-hero" data-reveal="up">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block hero-copy-block-immersive">
      <p class="eyebrow"><?= e($hero['subtitle']) ?></p>
      <h1><?= e($hero['title']) ?></h1>
      <p class="hero-copy"><?= e($hero['body']) ?></p>

      <div class="hero-actions">
        <?php foreach (($hero['actions'] ?? []) as $action): ?>
          <a class="button<?= ($action['style'] ?? 'secondary') === 'secondary' ? ' button-secondary' : '' ?>" href="<?= e($action['url']) ?>">
            <?= e($action['label']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="stat-strip">
        <?php foreach (($hero['stats'] ?? []) as $stat): ?>
          <div class="stat-card" data-reveal="up">
            <strong><?= e($stat['title']) ?></strong>
            <span><?= e($stat['text']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="hero-panel landing-panel hero-panel-feature" data-reveal="left">
      <p class="panel-kicker"><?= e($highlights['subtitle']) ?></p>
      <h2><?= e($highlights['title']) ?></h2>
      <ul class="step-list">
        <?php foreach (($highlights['items'] ?? []) as $item): ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
      <a class="text-link" href="<?= e($highlights['link_url']) ?>"><?= e($highlights['link_label']) ?></a>
    </aside>
  </div>
</section>

<section class="section" data-reveal="up">
  <div class="wrapper">
    <div class="section-heading">
      <p class="eyebrow">Services</p>
      <h2>Choose the right path quickly</h2>
      <p>Whether you need a repair, a service request, a product, or technical guidance, the next step should be obvious.</p>
    </div>

    <div class="card-grid">
      <?php foreach ($services as $service): ?>
        <?php $features = json_decode((string) ($service['features'] ?? ''), true); ?>
        <article class="card accent-card" data-reveal="up">
          <h3><?= e($service['title']) ?></h3>
          <p><?= e($service['description']) ?></p>
          <?php if (is_array($features) && $features): ?>
            <ul class="feature-list">
              <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                <li><?= e((string) $feature) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <a class="text-link" href="<?= SITE_URL ?>/public/service-request.php?service=<?= e($service['slug']) ?>">Request this service</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-alt" data-reveal="up">
  <div class="wrapper">
    <div class="section-heading">
      <p class="eyebrow"><?= e($journey['subtitle']) ?></p>
      <h2><?= e($journey['title']) ?></h2>
      <p><?= e($journey['body']) ?></p>
    </div>

    <div class="journey-grid">
      <?php foreach (($journey['items'] ?? []) as $index => $step): ?>
        <article class="journey-card" data-reveal="up">
          <span class="journey-number">0<?= $index + 1 ?></span>
          <p><?= e($step) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($testimonials): ?>
  <section class="section section-showcase" data-reveal="up">
    <div class="wrapper">
      <div class="section-heading">
        <p class="eyebrow">Client confidence</p>
        <h2>What clients say after working with Joetech</h2>
        <p>Real service feels more trustworthy when the experience is visible, clear, and tied to outcomes.</p>
      </div>

      <div class="testimonial-slider" data-slider="testimonials" aria-label="Client testimonials">
        <div class="testimonial-track">
          <?php foreach ($testimonials as $index => $testimonial): ?>
            <article class="testimonial-slide<?= $index === 0 ? ' is-active' : '' ?>">
              <div class="testimonial-rating" aria-hidden="true">
                <?php for ($i = 0; $i < max(1, (int) ($testimonial['rating'] ?? 5)); $i++): ?>
                  <span>★</span>
                <?php endfor; ?>
              </div>
              <blockquote><?= e($testimonial['content']) ?></blockquote>
              <div class="testimonial-meta">
                <strong><?= e($testimonial['full_name']) ?></strong>
                <span><?= e($testimonial['company_name'] ?: ($testimonial['project_title'] ?: 'Joetech Client')) ?></span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
        <div class="slider-dots" role="tablist" aria-label="Select testimonial">
          <?php foreach ($testimonials as $index => $testimonial): ?>
            <button class="slider-dot<?= $index === 0 ? ' is-active' : '' ?>" type="button" data-slide-to="<?= e((string) $index) ?>" aria-label="Show testimonial <?= e((string) ($index + 1)) ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<section class="section" data-reveal="up">
  <div class="wrapper split-banner callout-banner">
    <div>
      <p class="eyebrow"><?= e($callout['subtitle']) ?></p>
      <h2><?= e($callout['title']) ?></h2>
      <p><?= e($callout['body']) ?></p>
    </div>
    <div class="hero-actions">
      <?php foreach (($callout['actions'] ?? []) as $action): ?>
        <a class="button<?= ($action['style'] ?? 'secondary') === 'secondary' ? ' button-secondary' : '' ?>" href="<?= e($action['url']) ?>">
          <?= e($action['label']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
