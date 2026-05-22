<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(admin_logged_in() ? 'index.php' : 'login.php');
}
csrf_verify();
admin_logout();
redirect('login.php');
