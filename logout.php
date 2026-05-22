<?php
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(customer_logged_in() ? 'mon-compte' : '/');
}
csrf_verify();
customer_logout();
flash_set('success', 'Vous êtes déconnecté(e).');
redirect('connexion');
