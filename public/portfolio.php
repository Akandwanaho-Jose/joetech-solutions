<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Portfolio';
$meta_desc  = 'See selected Joetech projects and the kinds of work the business delivers.';

$projects = [];
$db_error = null;

try {
    $projects = db_all(
        "SELECT
            pp.title,
            pp.slug,
            pp.description,
            pp.client_name,
            pp.project_url,
            pp.completed_date,
            pp.is_featured,
            s.title AS service_title
         FROM portfolio_projects pp
         INNER JOIN services s ON s.id = pp.service_id
         WHERE pp.deleted_at IS NULL
         ORDER BY pp.is_featured DESC, pp.sort_order ASC, pp.created_at DESC
         LIMIT 12"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development'
        ? $e->getMessage()
        : 'Portfolio projects are temporarily unavailable.';
}

include INCLUDES . '/header.php';
?>

<section class="hero portfolio-hero">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block">
      <p class="eyebrow">Portfolio</p>
      <h1>Selected projects that show how Joetech delivers practical technology work.</h1>
      <p class="hero-copy">
        Explore examples of repair, support, networking, and digital service work completed for clients and internal projects.
      </p>
      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/contact.php">Discuss a project</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/services.php">Compare services</a>
      </div>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Why portfolio matters</p>
      <ul class="step-list">
        <li>Shows the types of work Joetech handles</li>
        <li>Builds trust with practical examples</li>
        <li>Supports sales conversations with proof of delivery</li>
      </ul>
    </aside>
  </div>
</section>

<section class="section">
  <div class="wrapper">
    <?php if ($db_error): ?>
      <div class="inline-error"><?= e($db_error) ?></div>
    <?php endif; ?>

    <div class="section-heading">
      <p class="eyebrow">Selected work</p>
      <h2>Project highlights</h2>
      <p>Portfolio projects published from the admin side appear here automatically.</p>
    </div>

    <?php if ($projects): ?>
      <div class="showcase-grid">
        <?php foreach ($projects as $index => $project): ?>
          <article class="showcase-card">
            <div class="showcase-badge"><?= !empty($project['is_featured']) ? 'FT' : '0' . ($index + 1) ?></div>
            <span class="chip"><?= e($project['service_title']) ?></span>
            <h3><?= e($project['title']) ?></h3>
            <p><?= e($project['description'] ?: 'Project details available on request.') ?></p>
            <div class="post-footer">
              <span><?= !empty($project['client_name']) ? e($project['client_name']) : 'Joetech Project' ?></span>
              <?php if (!empty($project['completed_date'])): ?>
                <span><?= e(date_fmt((string) $project['completed_date'])) ?></span>
              <?php endif; ?>
            </div>
            <?php if (!empty($project['project_url'])): ?>
              <a class="text-link" href="<?= e($project['project_url']) ?>" target="_blank" rel="noopener">Visit project</a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h3>No portfolio projects have been published yet.</h3>
        <p>Add portfolio entries from the admin side and they will appear here automatically.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-alt">
  <div class="wrapper split-banner callout-banner">
    <div>
      <p class="eyebrow">Start a conversation</p>
      <h2>Need similar work for your business, office, or device environment?</h2>
      <p>Tell us what you want to improve and we will recommend the most practical next step.</p>
    </div>
    <div class="hero-actions">
      <a class="button" href="<?= SITE_URL ?>/public/contact.php">Start a project conversation</a>
      <a class="button button-secondary" href="<?= SITE_URL ?>/public/service-request.php">Request service</a>
    </div>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
