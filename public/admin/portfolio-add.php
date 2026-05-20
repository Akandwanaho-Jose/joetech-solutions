<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_portfolio');

$page_title = 'Add Project';
$errors = [];
$services = db_all("SELECT id, title FROM services WHERE deleted_at IS NULL ORDER BY sort_order ASC, title ASC");
$form = [
    'service_id' => '',
    'title' => '',
    'slug' => '',
    'description' => '',
    'client_name' => '',
    'project_url' => '',
    'completed_date' => '',
    'is_featured' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);
    if ($form['service_id'] === '') $errors['service_id'] = 'Choose a service.';
    if ($form['title'] === '') $errors['title'] = 'Enter a project title.';
    if ($form['slug'] === '') $form['slug'] = make_slug($form['title']);
    if (db_one("SELECT id FROM portfolio_projects WHERE slug = ? AND deleted_at IS NULL LIMIT 1", [$form['slug']])) $errors['slug'] = 'This slug is already in use.';
    if (!$errors) {
        db_insert(
            "INSERT INTO portfolio_projects (service_id, title, slug, description, client_name, project_url, completed_date, is_featured)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (int) $form['service_id'],
                $form['title'],
                $form['slug'],
                $form['description'] !== '' ? $form['description'] : null,
                $form['client_name'] !== '' ? $form['client_name'] : null,
                $form['project_url'] !== '' ? $form['project_url'] : null,
                $form['completed_date'] !== '' ? $form['completed_date'] : null,
                $form['is_featured'] === '1' ? 1 : 0,
            ]
        );
        flash('success', 'Portfolio project created.');
        redirect('public/admin/portfolio.php');
    }
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Content</p><h2>Add portfolio project</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted project details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/portfolio-add.php" class="contact-form">
    <?= csrf_field(); ?>
    <label for="service_id">Service</label>
    <select id="service_id" name="service_id">
      <option value="">Select service</option>
      <?php foreach ($services as $service): ?>
        <option value="<?= e((string) $service['id']) ?>"<?= $form['service_id'] === (string) $service['id'] ? ' selected' : '' ?>><?= e($service['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (isset($errors['service_id'])): ?><p class="field-error"><?= e($errors['service_id']) ?></p><?php endif; ?>
    <label for="title">Title</label>
    <input id="title" name="title" type="text" value="<?= e($form['title']) ?>">
    <?php if (isset($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
    <label for="slug">Slug</label>
    <input id="slug" name="slug" type="text" value="<?= e($form['slug']) ?>">
    <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="6"><?= e($form['description']) ?></textarea>
    <label for="client_name">Client name</label>
    <input id="client_name" name="client_name" type="text" value="<?= e($form['client_name']) ?>">
    <label for="project_url">Project URL</label>
    <input id="project_url" name="project_url" type="text" value="<?= e($form['project_url']) ?>">
    <label for="completed_date">Completed date</label>
    <input id="completed_date" name="completed_date" type="date" value="<?= e($form['completed_date']) ?>">
    <label class="checkbox-row"><input type="checkbox" name="is_featured" value="1"<?= $form['is_featured'] === '1' ? ' checked' : '' ?>><span>Feature this project</span></label>
    <button class="button" type="submit">Create project</button>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
