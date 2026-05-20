<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_blog');

$page_title = 'Blog Posts';
$posts = [];
$db_error = null;

try {
    $posts = db_all(
        "SELECT
            bp.id,
            bp.title,
            bp.slug,
            bp.status,
            bp.is_featured,
            bp.views,
            bp.created_at,
            bc.name AS category_name
         FROM blog_posts bp
         INNER JOIN blog_categories bc ON bc.id = bp.category_id
         WHERE bp.deleted_at IS NULL
         ORDER BY bp.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Blog posts are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Content</p>
  <h2>Blog posts</h2>
  <p>Create, edit, and publish articles without leaving the admin workflow.</p>
</div>

<p><a class="button" href="<?= SITE_URL ?>/public/admin/blog-add.php">Add post</a></p>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($posts): ?>
  <div class="table-list">
    <?php foreach ($posts as $post): ?>
      <article class="table-item">
        <div>
          <strong><?= e($post['title']) ?></strong>
          <span><?= e($post['category_name']) ?> | <?= e($post['slug']) ?></span>
        </div>
        <div>
          <strong><?= e(ucfirst((string) $post['status'])) ?></strong>
          <span><?= e((string) $post['views']) ?> views</span>
        </div>
        <div>
          <span class="chip"><?= !empty($post['is_featured']) ? 'Featured' : 'Standard' ?></span>
        </div>
        <div class="table-actions">
          <a class="text-link" href="<?= SITE_URL ?>/public/admin/blog-edit.php?id=<?= e((string) $post['id']) ?>">Edit</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No blog posts yet.</h3>
    <p>Add your first article to start the content workflow.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
