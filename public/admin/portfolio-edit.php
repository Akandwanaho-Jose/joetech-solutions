<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_portfolio');

$project_id = get_int('id');
$page_title = 'Edit Project';
$errors = [];
$services = db_all("SELECT id, title FROM services WHERE deleted_at IS NULL ORDER BY sort_order ASC, title ASC");
$project = db_one("SELECT * FROM portfolio_projects WHERE id = ? AND deleted_at IS NULL LIMIT 1", [$project_id]);

if (!$project) {
    flash('error', 'Project not found.');
    redirect('public/admin/portfolio.php');
}

$form = [
    'service_id' => (string) $project['service_id'],
    'title' => (string) $project['title'],
    'slug' => (string) $project['slug'],
    'description' => (string) ($project['description'] ?? ''),
    'client_name' => (string) ($project['client_name'] ?? ''),
    'project_url' => (string) ($project['project_url'] ?? ''),
    'completed_date' => (string) ($project['completed_date'] ?? ''),
    'is_featured' => !empty($project['is_featured']) ? '1' : '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($form as $key => $value) $form[$key] = post($key, (string) $value);

    if (post('delete_project') === '1') {
        db_run("UPDATE portfolio_projects SET deleted_at = NOW() WHERE id = ?", [$project_id]);
        flash('success', 'Project archived.');
        redirect('public/admin/portfolio.php');
    }

    if ($form['service_id'] === '') $errors['service_id'] = 'Choose a service.';
    if ($form['title'] === '') $errors['title'] = 'Enter a project title.';
    if ($form['slug'] === '') $form['slug'] = make_slug($form['title']);
    if (db_one("SELECT id FROM portfolio_projects WHERE slug = ? AND id <> ? AND deleted_at IS NULL LIMIT 1", [$form['slug'], $project_id])) $errors['slug'] = 'This slug is already in use.';

    if (!$errors) {
        db_run(
            "UPDATE portfolio_projects
             SET service_id = ?, title = ?, slug = ?, description = ?, client_name = ?, project_url = ?, completed_date = ?, is_featured = ?, updated_at = NOW()
             WHERE id = ?",
            [
                (int) $form['service_id'],
                $form['title'],
                $form['slug'],
                $form['description'] !== '' ? $form['description'] : null,
                $form['client_name'] !== '' ? $form['client_name'] : null,
                $form['project_url'] !== '' ? $form['project_url'] : null,
                $form['completed_date'] !== '' ? $form['completed_date'] : null,
                $form['is_featured'] === '1' ? 1 : 0,
                $project_id,
            ]
        );
        flash('success', 'Portfolio project updated.');
        redirect('public/admin/portfolio.php');
    }
}

include INCLUDES . '/admin_header.php';
?>

<div class="section-heading"><p class="eyebrow">Content</p><h2>Edit portfolio project</h2></div>
<div class="form-card">
  <?php if ($errors): ?><div class="inline-error">Please correct the highlighted project details and try again.</div><?php endif; ?>
  <form method="post" action="<?= SITE_URL ?>/public/admin/portfolio-edit.php?id=<?= e((string) $project_id) ?>" class="contact-form">
    <?= csrf_field(); ?>
    <label for="service_id">Service</label>
    <select id="service_id" name="service_id">
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
    <div class="button-row">
      <button class="button" type="submit">Save project</button>
      <button class="button button-secondary" type="submit" name="delete_project" value="1" onclick="return confirm('Archive this project?');">Archive project</button>
    </div>
  </form>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
