<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_services');

$page_title = 'Add Service';
$errors = [];
$form = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'price_from' => '',
    'status' => 'active',
    'sort_order' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);
    if ($form['title'] === '') $errors['title'] = 'Enter a service title.';
    if ($form['slug'] === '') $form['slug'] = make_slug($form['title']);
    if ($form['price_from'] !== '' && !is_numeric($form['price_from'])) $errors['price_from'] = 'Enter a valid starting price.';
    if (db_one("SELECT id FROM services WHERE slug = ? AND deleted_at IS NULL LIMIT 1", [$form['slug']])) $errors['slug'] = 'This slug is already in use.';
    if (!$errors) {
        db_insert(
            "INSERT INTO services (title, slug, description, price_from, status, sort_order)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $form['title'],
                $form['slug'],
                $form['description'],
                $form['price_from'] !== '' ? (float) $form['price_from'] : null,
                $form['status'],
                (int) $form['sort_order'],
            ]
        );
        flash('success', 'Service created.');
        redirect('public/admin/services.php');
    }
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading"><p class="eyebrow">Content</p><h2>Add service</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted service details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/service-add.php" class="contact-form">
    <?= csrf_field(); ?>
    <label for="title">Title</label>
    <input id="title" name="title" type="text" value="<?= e($form['title']) ?>">
    <?php if (isset($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
    <label for="slug">Slug</label>
    <input id="slug" name="slug" type="text" value="<?= e($form['slug']) ?>">
    <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="6"><?= e($form['description']) ?></textarea>
    <label for="price_from">Price from</label>
    <input id="price_from" name="price_from" type="text" value="<?= e($form['price_from']) ?>">
    <?php if (isset($errors['price_from'])): ?><p class="field-error"><?= e($errors['price_from']) ?></p><?php endif; ?>
    <label for="sort_order">Sort order</label>
    <input id="sort_order" name="sort_order" type="number" min="0" value="<?= e($form['sort_order']) ?>">
    <label for="status">Status</label>
    <select id="status" name="status">
      <?php foreach (['active', 'inactive'] as $status): ?>
        <option value="<?= e($status) ?>"<?= $form['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="button" type="submit">Create service</button>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
