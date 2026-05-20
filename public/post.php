<?php
require_once __DIR__ . '/../includes/init.php';

$slug = get('slug');
$post = null;
$recent_posts = [];
$comments = [];
$db_error = null;
$comment_errors = [];
$comment_form = [
    'guest_name' => '',
    'guest_email' => '',
    'content' => '',
];

try {
    if ($slug !== '') {
        $post = db_one(
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
                bc.name AS category_name
             FROM blog_posts bp
             INNER JOIN blog_categories bc ON bc.id = bp.category_id
             WHERE bp.slug = ?
               AND bp.status = 'published'
               AND bp.deleted_at IS NULL
             LIMIT 1",
            [$slug]
        );
    }

    if ($post) {
        db_run('UPDATE blog_posts SET views = views + 1 WHERE id = ?', [$post['id']]);

        $recent_posts = db_all(
            "SELECT title, slug, published_at
             FROM blog_posts
             WHERE status = 'published'
               AND deleted_at IS NULL
               AND id <> ?
             ORDER BY COALESCE(published_at, created_at) DESC
             LIMIT 3",
            [$post['id']]
        );
    }
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development'
        ? $e->getMessage()
        : 'This article is temporarily unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $post) {
    verify_csrf();

    $comment_form['guest_name'] = post('guest_name');
    $comment_form['guest_email'] = post('guest_email');
    $comment_form['content'] = post('content');

    if ($comment_form['guest_name'] === '') {
        $comment_errors['guest_name'] = 'Please enter your name.';
    }

    if ($comment_form['guest_email'] === '' || !filter_var($comment_form['guest_email'], FILTER_VALIDATE_EMAIL)) {
        $comment_errors['guest_email'] = 'Please enter a valid email address.';
    }

    if ($comment_form['content'] === '') {
        $comment_errors['content'] = 'Please enter your comment.';
    }

    if (!$comment_errors) {
        try {
            db_insert(
                "INSERT INTO blog_comments (post_id, user_id, guest_name, guest_email, content, status, ip_address)
                 VALUES (?, ?, ?, ?, ?, 'pending', ?)",
                [
                    (int) $post['id'],
                    $_SESSION['user_id'] ?? null,
                    $comment_form['guest_name'],
                    $comment_form['guest_email'],
                    $comment_form['content'],
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );

            notify_blog_comment($post, $comment_form);

            flash('success', 'Your comment has been received and is awaiting moderation.');
            redirect('public/post.php?slug=' . $post['slug']);
        } catch (Throwable $e) {
            $comment_errors['form'] = APP_ENV === 'development'
                ? 'Comment save failed: ' . $e->getMessage()
                : 'We could not save your comment right now.';
        }
    }
}

if ($post) {
    try {
        $comments = db_all(
            "SELECT guest_name, content, created_at
             FROM blog_comments
             WHERE post_id = ?
               AND status = 'approved'
             ORDER BY created_at DESC
             LIMIT 20",
            [$post['id']]
        );
    } catch (Throwable $e) {
        if ($db_error === null) {
            $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Comments are temporarily unavailable.';
        }
    }
}

$page_title = $post ? $post['title'] : 'Article';
$meta_desc = $post && $post['excerpt'] !== '' ? $post['excerpt'] : 'Joetech article details.';

include INCLUDES . '/header.php';
?>

<?php if (!$post): ?>
  <section class="section">
    <div class="wrapper">
      <?php if ($db_error): ?>
        <div class="inline-error"><?= e($db_error) ?></div>
      <?php endif; ?>

      <div class="empty-state empty-state-large">
        <p class="eyebrow">Blog</p>
        <h1>Article not found</h1>
        <p>The article you are looking for is unavailable, unpublished, or the link may be incorrect.</p>
        <div class="hero-actions">
          <a class="button" href="<?= SITE_URL ?>/public/blog.php">Back to blog</a>
          <a class="button button-secondary" href="<?= SITE_URL ?>/public/contact.php">Ask a question</a>
        </div>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="section">
    <div class="wrapper post-detail-grid">
      <article class="post-article">
        <p class="eyebrow"><?= e($post['category_name'] ?? 'Article') ?></p>
        <h1><?= e($post['title']) ?></h1>

        <div class="post-footer">
          <span><?= e(date_fmt((string) $post['published_at'])) ?></span>
          <span><?= e((string) ($post['read_time_min'] ?? read_time($post['body']))) ?> min read</span>
        </div>

        <?php if ($db_error): ?>
          <div class="inline-error"><?= e($db_error) ?></div>
        <?php endif; ?>

        <?php if (!empty($post['cover_image'])): ?>
          <div class="detail-media">
            <img src="<?= e($post['cover_image']) ?>" alt="<?= e($post['title']) ?>">
          </div>
        <?php endif; ?>

        <div class="article-body">
          <?php foreach (preg_split("/\r\n|\n|\r/", (string) $post['body']) as $paragraph): ?>
            <?php $paragraph = trim($paragraph); ?>
            <?php if ($paragraph !== ''): ?>
              <p><?= nl2br(e($paragraph)) ?></p>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <section class="comment-section">
          <div class="section-heading">
            <p class="eyebrow">Comments</p>
            <h2>Join the conversation</h2>
            <p>Comments go through moderation before they appear publicly.</p>
          </div>

          <?php if ($comments): ?>
            <div class="comment-list">
              <?php foreach ($comments as $comment): ?>
                <article class="comment-card">
                  <strong><?= e($comment['guest_name']) ?></strong>
                  <span><?= e(date_fmt((string) $comment['created_at'])) ?></span>
                  <p><?= nl2br(e((string) $comment['content'])) ?></p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p>No approved comments yet.</p>
          <?php endif; ?>

          <div class="form-card">
            <?php if (isset($comment_errors['form'])): ?><div class="inline-error"><?= e($comment_errors['form']) ?></div><?php endif; ?>
            <form method="post" action="<?= SITE_URL ?>/public/post.php?slug=<?= e($post['slug']) ?>" class="contact-form" novalidate>
              <?= csrf_field(); ?>
              <label for="guest_name">Name</label>
              <input id="guest_name" name="guest_name" type="text" value="<?= e($comment_form['guest_name']) ?>">
              <?php if (isset($comment_errors['guest_name'])): ?><p class="field-error"><?= e($comment_errors['guest_name']) ?></p><?php endif; ?>

              <label for="guest_email">Email</label>
              <input id="guest_email" name="guest_email" type="email" value="<?= e($comment_form['guest_email']) ?>">
              <?php if (isset($comment_errors['guest_email'])): ?><p class="field-error"><?= e($comment_errors['guest_email']) ?></p><?php endif; ?>

              <label for="content">Comment</label>
              <textarea id="content" name="content" rows="5"><?= e($comment_form['content']) ?></textarea>
              <?php if (isset($comment_errors['content'])): ?><p class="field-error"><?= e($comment_errors['content']) ?></p><?php endif; ?>

              <button class="button" type="submit">Submit comment</button>
            </form>
          </div>
        </section>
      </article>

      <aside class="hero-panel landing-panel">
        <p class="panel-kicker">Keep reading</p>
        <?php if ($recent_posts): ?>
          <div class="side-list">
            <?php foreach ($recent_posts as $recent): ?>
              <a class="side-item" href="<?= SITE_URL ?>/public/post.php?slug=<?= e($recent['slug']) ?>">
                <strong><?= e($recent['title']) ?></strong>
                <span><?= e(date_fmt((string) $recent['published_at'])) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p>New articles will appear here as more posts are published.</p>
        <?php endif; ?>

        <a class="button button-secondary" href="<?= SITE_URL ?>/public/blog.php">Back to blog</a>
      </aside>
    </div>
  </section>
<?php endif; ?>

<?php include INCLUDES . '/footer.php'; ?>
