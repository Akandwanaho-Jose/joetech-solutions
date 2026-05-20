<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_blog');

$page_title = 'Comments';
$comments = [];
$db_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $comment_id = post_int('comment_id');
    $status = post('status');
    $allowed_statuses = ['pending', 'approved', 'rejected', 'spam'];

    if ($comment_id > 0 && in_array($status, $allowed_statuses, true)) {
        try {
            db_run(
                "UPDATE blog_comments
                 SET status = ?, moderation_note = ?
                 WHERE id = ?",
                [
                    $status,
                    post('moderation_note') !== '' ? post('moderation_note') : null,
                    $comment_id,
                ]
            );
            flash('success', 'Comment updated.');
            redirect('public/admin/blog-comments.php');
        } catch (Throwable $e) {
            $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Could not update this comment.';
        }
    }
}

try {
    $comments = db_all(
        "SELECT
            bc.id,
            bc.guest_name,
            bc.guest_email,
            bc.content,
            bc.status,
            bc.created_at,
            bp.title AS post_title
         FROM blog_comments bc
         INNER JOIN blog_posts bp ON bp.id = bc.post_id
         ORDER BY
            CASE bc.status
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4
            END,
            bc.created_at DESC
         LIMIT 100"
    );
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Comments are temporarily unavailable.';
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading">
  <p class="eyebrow">Content</p>
  <h2>Blog comments</h2>
  <p>Moderate reader comments so discussion stays useful and on-brand.</p>
</div>

<?php if ($db_error): ?>
  <div class="inline-error"><?= e($db_error) ?></div>
<?php endif; ?>

<?php if ($comments): ?>
  <div class="table-list">
    <?php foreach ($comments as $comment): ?>
      <article class="story-card">
        <div class="comment-head">
          <div>
            <strong><?= e($comment['guest_name'] ?: 'Guest') ?></strong>
            <span><?= e($comment['guest_email'] ?: 'No email') ?></span>
          </div>
          <div>
            <span class="chip"><?= e(ucfirst((string) $comment['status'])) ?></span>
            <span><?= e(date_fmt((string) $comment['created_at'])) ?></span>
          </div>
        </div>
        <p><strong>Post:</strong> <?= e($comment['post_title']) ?></p>
        <p><?= nl2br(e((string) $comment['content'])) ?></p>

        <form method="post" action="<?= SITE_URL ?>/public/admin/blog-comments.php" class="comment-moderation">
          <?= csrf_field(); ?>
          <input type="hidden" name="comment_id" value="<?= e((string) $comment['id']) ?>">
          <select name="status">
            <?php foreach (['pending', 'approved', 'rejected', 'spam'] as $status): ?>
              <option value="<?= e($status) ?>"<?= $comment['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="moderation_note" placeholder="Optional moderation note">
          <button class="button" type="submit">Save</button>
        </form>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty-state">
    <h3>No comments yet.</h3>
    <p>When blog discussion starts, moderation will happen here.</p>
  </div>
<?php endif; ?>

<?php include INCLUDES . '/admin_footer.php'; ?>
