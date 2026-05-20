<?php
// Boot file — include this at the top of every page
// Usage: require_once '../../includes/init.php';

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/auth.php';

// Error display based on environment
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Set timezone
date_default_timezone_set('Africa/Kampala');
