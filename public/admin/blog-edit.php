<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_blog');

$post_id = get_int('id');
$page_title = 'Edit Post';
$errors = [];
$categories = db_all("SELECT id, name FROM blog_categories ORDER BY name ASC");
$post = db_one("SELECT * FROM blog_posts WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$post_id]);

if (!$post) {
    flash('error', 'Post not found.');
    redirect('public/admin/blog.php');
}

$form = [
    'category_id' => (string) $post['category_id'],
    'title' => (string) $post['title'],
    'slug' => (string) $post['slug'],
    'excerpt' => (string) ($post['excerpt'] ?? ''),
    'body' => (string) $post['body'],
    'status' => (string) $post['status'],
    'is_featured' => !empty($post['is_featured']) ? '1' : '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);

    if ($form['category_id'] === '') $errors['category_id'] = 'Choose a category.';
    if ($form['title'] === '') $errors['title'] = 'Enter a post title.';
    if ($form['body'] === '') $errors['body'] = 'Enter the main post content.';
    if ($form['slug'] === '') $form['slug'] = make_slug($form['title']);
    if (db_one("SELECT id FROM blog_posts WHERE slug = ? AND id <> ? AND deleted_at IS NULL LIMIT 1", [$form['slug'], $post_id])) $errors['slug'] = 'This slug is already in use.';

    $cover_image = $post['cover_image'] ?? null;
    if (!$errors) {
        if (post('remove_cover_image') === '1') $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $stored_path = upload_image($_FILES['cover_image'], 'blog');
            if ($stored_path === false) {
                $errors['cover_image'] = 'Cover image upload failed. Use JPG, PNG, or WebP under 5MB.';
            } else {
                $cover_image = UPLOAD_URL . '/' . $stored_path;
            }
        }
    }

    if (!$errors) {
        db_run(
            "UPDATE blog_posts
             SET category_id = ?, title = ?, slug = ?, excerpt = ?, body = ?, cover_image = ?, status = ?, is_featured = ?, read_time_min = ?, published_at = CASE WHEN ? = 'published' AND published_at IS NULL THEN NOW() ELSE published_at END
             WHERE id = ?",
            [
                (int) $form['category_id'],
                $form['title'],
                $form['slug'],
                $form['excerpt'] !== '' ? $form['excerpt'] : null,
                $form['body'],
                $cover_image,
                $form['status'],
                $form['is_featured'] === '1' ? 1 : 0,
                read_time($form['body']),
                $form['status'],
                $post_id,
            ]
        );
        flash('success', 'Blog post updated.');
        redirect('public/admin/blog.php');
    }
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Content</p><h2>Edit blog post</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted post details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/blog-edit.php?id=<?= e((string) $post_id) ?>" class="contact-form" enctype="multipart/form-data">
    <?= csrf_field(); ?>
    <label for="category_id">Category</label>
    <select id="category_id" name="category_id">
      <?php foreach ($categories as $category): ?>
        <option value="<?= e((string) $category['id']) ?>"<?= $form['category_id'] === (string) $category['id'] ? ' selected' : '' ?>><?= e($category['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($errors['category_id'])): ?><p class="field-error"><?= e($errors['category_id']) ?></p><?php endif; ?>
    <label for="title">Title</label>
    <input id="title" name="title" type="text" value="<?= e($form['title']) ?>">
    <?php if (isset($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
    <label for="slug">Slug</label>
    <input id="slug" name="slug" type="text" value="<?= e($form['slug']) ?>">
    <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
    <label for="excerpt">Excerpt</label>
    <textarea id="excerpt" name="excerpt" rows="3"><?= e($form['excerpt']) ?></textarea>
    <label for="body">Body</label>
    <textarea id="body" name="body" rows="10"><?= e($form['body']) ?></textarea>
    <?php if (isset($errors['body'])): ?><p class="field-error"><?= e($errors['body']) ?></p><?php endif; ?>
    <div class="image-field">
      <label for="cover_image">Cover image</label>
      <?php if (!empty($post['cover_image'])): ?>
        <div class="admin-image-preview"><img src="<?= e((string) $post['cover_image']) ?>" alt="<?= e($form['title']) ?>"></div>
        <label class="checkbox-row"><input type="checkbox" name="remove_cover_image" value="1"><span>Remove current cover image</span></label>
      <?php endif; ?>
      <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp">
      <?php if (isset($errors['cover_image'])): ?><p class="field-error"><?= e($errors['cover_image']) ?></p><?php endif; ?>
    </div>
    <label for="status">Status</label>
    <select id="status" name="status">
      <?php foreach (['draft', 'scheduled', 'published', 'archived'] as $status): ?>
        <option value="<?= e($status) ?>"<?= $form['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option>
      <?php endforeach; ?>
    </select>
    <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1"<?= $form['is_featured'] === '1' ? ' checked' : '' ?>><span>Feature this post</span></label>
    <button class="button" type="submit">Save post</button>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
