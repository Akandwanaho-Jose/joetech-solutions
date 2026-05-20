<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_blog');

$page_title = 'New Post';
$errors = [];
$categories = db_all("SELECT id, name FROM blog_categories ORDER BY name ASC");
$form = [
    'category_id' => '',
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'body' => '',
    'status' => 'draft',
    'is_featured' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) {
        $form[$key] = post($key, (string) $value);
    }

    if ($form['category_id'] === '') $errors['category_id'] = 'Choose a category.';
    if ($form['title'] === '') $errors['title'] = 'Enter a post title.';
    if ($form['body'] === '') $errors['body'] = 'Enter the main post content.';
    if ($form['slug'] === '') $form['slug'] = make_slug($form['title']);
    if (db_one("SELECT id FROM blog_posts WHERE slug = ? AND deleted_at IS NULL LIMIT 1", [$form['slug']])) $errors['slug'] = 'This slug is already in use.';

    $cover_image = null;
    if (!$errors && isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $stored_path = upload_image($_FILES['cover_image'], 'blog');
        if ($stored_path === false) {
            $errors['cover_image'] = 'Cover image upload failed. Use JPG, PNG, or WebP under 5MB.';
        } else {
            $cover_image = UPLOAD_URL . '/' . $stored_path;
        }
    }

    if (!$errors) {
        db_insert(
            "INSERT INTO blog_posts
            (staff_id, category_id, title, slug, excerpt, body, cover_image, status, is_featured, read_time_min, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (int) $_SESSION['staff_id'],
                (int) $form['category_id'],
                $form['title'],
                $form['slug'],
                $form['excerpt'] !== '' ? $form['excerpt'] : null,
                $form['body'],
                $cover_image,
                $form['status'],
                $form['is_featured'] === '1' ? 1 : 0,
                read_time($form['body']),
                $form['status'] === 'published' ? date('Y-m-d H:i:s') : null,
            ]
        );
        flash('success', 'Blog post created.');
        redirect('public/admin/blog.php');
    }
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Content</p><h2>Add blog post</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted post details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/blog-add.php" class="contact-form" enctype="multipart/form-data">
    <?= csrf_field(); ?>
    <label for="category_id">Category</label>
    <select id="category_id" name="category_id">
      <option value="">Select category</option>
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
    <label for="cover_image">Cover image</label>
    <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp">
    <?php if (isset($errors['cover_image'])): ?><p class="field-error"><?= e($errors['cover_image']) ?></p><?php endif; ?>
    <label for="status">Status</label>
    <select id="status" name="status">
      <?php foreach (['draft', 'scheduled', 'published', 'archived'] as $status): ?>
        <option value="<?= e($status) ?>"<?= $form['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option>
      <?php endforeach; ?>
    </select>
    <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1"<?= $form['is_featured'] === '1' ? ' checked' : '' ?>><span>Feature this post</span></label>
    <button class="button" type="submit">Create post</button>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
