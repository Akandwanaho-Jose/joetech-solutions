<?php
$site_name = (string) site_setting('site_name', SITE_NAME);
$site_email = (string) site_setting('site_email', SITE_EMAIL);
$site_address = (string) site_setting('site_address', 'Mbarara, Uganda');
?>
<footer class="site-footer">
  <div class="wrapper footer-grid">
    <div>
      <p class="footer-kicker"><?= e($site_name) ?></p>
      <h3>Professional technology support for people, teams, and growing businesses.</h3>
      <p><?= e($site_name) ?> helps clients with repairs, service delivery, product supply, and practical digital support.</p>
    </div>

    <div>
      <h4>Explore</h4>
      <ul class="footer-links">
        <li><a href="<?= SITE_URL ?>/public/services.php">Services</a></li>
        <li><a href="<?= SITE_URL ?>/public/service-request.php">Request Service</a></li>
        <li><a href="<?= SITE_URL ?>/public/repair-request.php">Book Repair</a></li>
        <li><a href="<?= SITE_URL ?>/public/request-status.php">Track Request</a></li>
        <li><a href="<?= SITE_URL ?>/public/shop.php">Shop</a></li>
        <li><a href="<?= SITE_URL ?>/public/blog.php">Blog</a></li>
        <li><a href="<?= SITE_URL ?>/public/contact.php">Contact</a></li>
      </ul>
    </div>

    <div>
      <h4>Contact</h4>
      <p><?= e($site_address) ?></p>
      <p><a href="mailto:<?= e($site_email) ?>"><?= e($site_email) ?></a></p>
      <p><a href="<?= SITE_URL ?>/public/admin/login.php">Admin area</a></p>
    </div>
  </div>

  <div class="wrapper footer-bottom">
    <p>&copy; <?= date('Y') ?> <?= e($site_name) ?>. All rights reserved.</p>
  </div>
</footer>
<?php
$app_js_path = ROOT . '/public/assets/js/app.js';
$app_js_version = is_file($app_js_path) ? filemtime($app_js_path) : time();
?>
<script src="<?= SITE_URL ?>/public/assets/js/app.js?v=<?= e((string) $app_js_version) ?>"></script>
</body>
</html>
