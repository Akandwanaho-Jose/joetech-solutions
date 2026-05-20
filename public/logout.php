<?php
require_once __DIR__ . '/../includes/init.php';

unset($_SESSION['user_id']);
flash('success', 'You have been logged out.');
redirect('public/');
