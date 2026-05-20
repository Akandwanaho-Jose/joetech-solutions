<?php
require_once __DIR__ . '/../../includes/init.php';
unset($_SESSION['staff_id'], $_SESSION['staff_role'], $_SESSION['staff_permissions']);
session_destroy();
redirect('admin/login.php');
