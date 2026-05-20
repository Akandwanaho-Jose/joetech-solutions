<?php
require_once __DIR__ . '/../includes/init.php';

$page_title = 'Blog';
$meta_desc  = 'Read practical Joetech articles, guides, and technology tips.';

$selected_category = get('category');
$posts = [];
$categories = [];
$db_error = null;

try {
    $categories = db_all(
        "SELECT id, name, slug
         FROM blog_categories
         ORDER BY name ASC"
    );

    $where = ["bp.status = 'published'", "bp.deleted_at IS NULL"];
    $params = [];

    if ($selected_category !== '') {
        $where[] = 'bc.slug = ?';
        $params[] = $selected_category;
    }

    $posts = db_all(
        "SELECT
            bp.id,
            bp.title,
            bp.slug,
            bp.excerpt,
            bp.body,
            bp.cover_image,
            bp.views,
            bp.read_time_min,
            bp.published_at,
            bc.name AS category_name,
            bc.slug AS category_slug
         FROM blog_posts bp
         INNER JOIN blog_categories bc ON bc.id = bp.category_id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY COALESCE(bp.published_at, bp.created_at) DESC
         LIMIT 12",
        $params
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development'
        ? $e->getMessage()
        : 'The blog is temporarily unavailable.';
}

$has_filter = $selected_category !== '';

include INCLUDES . '/header.php';
?>

<section class="hero blog-hero">
  <div class="wrapper hero-grid">
    <div class="hero-copy-block">
      <p class="eyebrow">Blog</p>
      <h1>Technology insights, practical advice, and useful business guidance.</h1>
      <p class="hero-copy">
        The Joetech blog shares useful knowledge for customers who want to make better technology decisions and keep their systems working well.
      </p>
      <div class="hero-actions">
        <a class="button" href="<?= SITE_URL ?>/public/contact.php">Ask a tech question</a>
        <a class="button button-secondary" href="<?= SITE_URL ?>/public/services.php">See services</a>
      </div>
    </div>

    <aside class="hero-panel landing-panel">
      <p class="panel-kicker">Topics</p>
      <div class="chip-list">
        <a class="chip<?= $selected_category === '' ? ' chip-active' : '' ?>" href="<?= SITE_URL ?>/public/blog.php">All</a>
        <?php foreach ($categories as $category): ?>
          <a class="chip<?= $selected_category === $category['slug'] ? ' chip-active' : '' ?>" href="<?= SITE_URL ?>/public/blog.php?category=<?= e($category['slug']) ?>">
            <?= e($category['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>
</section>

<section class="section">
  <div class="wrapper">
    <?php if ($db_error): ?>
      <div class="inline-error"><?= e($db_error) ?></div>
    <?php endif; ?>

    <div class="section-heading">
      <p class="eyebrow">Recent posts</p>
      <h2>Guides, tips, and practical advice</h2>
      <p>Articles published by Joetech appear here automatically.</p>
    </div>

    <?php if ($posts): ?>
      <div class="post-grid">
        <?php foreach ($posts as $post): ?>
          <article class="post-card">
            <div class="post-media">
              <?php if (!empty($post['cover_image'])): ?>
                <img src="<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>">
              <?php else: ?>
                <div class="post-placeholder"><?= e(substr($post['title'], 0, 1)) ?></div>
              <?php endif; ?>
            </div>

            <div class="post-body">
              <div class="product-meta">
                <span class="chip"><?= e($post['category_name'] ?? 'Article') ?></span>
                <span class="stock-tag"><?= e((string) ($post['read_time_min'] ?? read_time($post['body'] ?? ''))) ?> min read</span>
              </div>

              <h3><?= e($post['title']) ?></h3>
              <p><?= e($post['excerpt'] !== '' && $post['excerpt'] !== null ? $post['excerpt'] : excerpt($post['body'] ?? '', 24)) ?></p>

              <div class="post-footer">
                <span><?= !empty($post['published_at']) ? e(date_fmt((string) $post['published_at'])) : 'Publish date pending' ?></span>
                <a class="text-link" href="<?= SITE_URL ?>/public/post.php?slug=<?= e($post['slug']) ?>">Read post</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h3><?= $has_filter ? 'No articles matched this topic.' : 'No blog posts have been published yet.' ?></h3>
        <p><?= $has_filter ? 'Try another topic or view all posts.' : 'Published posts from the admin side will appear here automatically.' ?></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include INCLUDES . '/footer.php'; ?>
