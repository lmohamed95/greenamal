<?php
require_once __DIR__ . '/includes/helpers.php';
customer_logout();
flash_set('success', 'Vous êtes déconnecté(e).');
redirect('login');
