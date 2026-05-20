<?php
require_once __DIR__ . '/../../includes/init.php';
require_staff();
require_permission('manage_settings');

$page_title = 'Site Settings';
$settings = [];
$db_error = null;

try {
    $rows = db_all("SELECT setting_key, setting_value, setting_type, description FROM site_settings ORDER BY id ASC");
    foreach ($rows as $row) $settings[$row['setting_key']] = $row;
} catch (Throwable $e) {
    $db_error = APP_ENV === 'development' ? $e->getMessage() : 'Could not load site settings.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$db_error) {
    verify_csrf();
    foreach ($settings as $key => $setting) {
        db_run("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [post($key), $key]);
        $settings[$key]['setting_value'] = post($key);
    }
    flash('success', 'Settings updated.');
    redirect('public/admin/settings.php');
}

include INCLUDES . '/admin_header.php';
?>
<div class="section-heading"><p class="eyebrow">Admin</p><h2>Site settings</h2><p>Control site identity, contact details, and operating defaults.</p></div>
<div class="form-card">
  <?php if ($db_error): ?><div class="inline-error"><?= e($db_error) ?></div><?php endif; ?>
  <?php if ($settings): ?>
    <form method="post" action="<?= SITE_URL ?>/public/admin/settings.php" class="contact-form">
      <?= csrf_field(); ?>
      <?php foreach ($settings as $key => $setting): ?>
        <label for="<?= e($key) ?>"><?= e((string) $setting['description']) ?></label>
        <?php if (($setting['setting_type'] ?? 'text') === 'boolean'): ?>
          <select id="<?= e($key) ?>" name="<?= e($key) ?>">
            <option value="0"<?= (string) ($setting['setting_value'] ?? '0') === '0' ? ' selected' : '' ?>>Disabled</option>
            <option value="1"<?= (string) ($setting['setting_value'] ?? '0') === '1' ? ' selected' : '' ?>>Enabled</option>
          </select>
        <?php elseif (str_contains($key, 'description') || str_contains($key, 'address')): ?>
          <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="3"><?= e((string) ($setting['setting_value'] ?? '')) ?></textarea>
        <?php else: ?>
          <input id="<?= e($key) ?>" name="<?= e($key) ?>" type="text" value="<?= e((string) ($setting['setting_value'] ?? '')) ?>">
        <?php endif; ?>
      <?php endforeach; ?>
      <button class="button" type="submit">Save settings</button>
    </form>
  <?php endif; ?>
</div>
<?php include INCLUDES . '/admin_footer.php'; ?>
