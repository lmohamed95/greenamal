<?php
require_once __DIR__ . '/../../includes/auth.php';
admin_require_login();

$page_title = $page_title ?? 'Admin';
$current = $current ?? '';
$user = admin_user();
$user_initials = initials(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$user_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

// Pending orders badge
$pending_count = (int) db_value("SELECT COUNT(*) FROM orders WHERE status='pending'");
$pending_reviews = (int) db_value("SELECT COUNT(*) FROM reviews WHERE status='pending'");

// Coming-soon mode state
$coming_soon_on = (db_value("SELECT setting_value FROM settings WHERE setting_key='coming_soon_mode'") === '1');

// Flash from toggle endpoint
$admin_flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($page_title) ?> · GreenAmal Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<link rel="stylesheet" href="assets/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js" defer></script>
</head>
<body>

<div class="admin-app">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span class="sidebar-brand-mark">G</span>
      <div class="sidebar-brand-text">GreenAmal<small>Admin</small></div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <a href="index.php" class="sidebar-link<?= $current === 'dashboard' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Tableau de bord
        </a>
        <a href="orders.php" class="sidebar-link<?= $current === 'orders' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          Commandes
          <?php if ($pending_count > 0): ?><span class="badge"><?= $pending_count ?></span><?php endif; ?>
        </a>
        <a href="products.php" class="sidebar-link<?= $current === 'products' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
          Produits
        </a>
        <a href="categories.php" class="sidebar-link<?= $current === 'categories' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
          Catégories
        </a>
        <a href="customers.php" class="sidebar-link<?= $current === 'customers' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Clients
        </a>
        <a href="#" class="sidebar-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          Avis
          <?php if ($pending_reviews > 0): ?><span class="badge"><?= $pending_reviews ?></span><?php endif; ?>
        </a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-title">Marketing</div>
        <a href="coupons.php" class="sidebar-link<?= $current === 'coupons' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8H6a2 2 0 010-4h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/></svg>
          Coupons
        </a>
        <a href="#" class="sidebar-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Newsletter
        </a>
      </div>
      <div class="sidebar-section">
        <div class="sidebar-section-title">Réglages</div>
        <a href="customization.php" class="sidebar-link<?= $current === 'customization' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.9 4.4 4.7.4-3.6 3.1 1.1 4.6L12 13l-4.1 2.5 1.1-4.6-3.6-3.1 4.7-.4z"/><path d="M5 21l1.5-3M19 21l-1.5-3"/></svg>
          Personnalisation
        </a>
        <a href="settings.php" class="sidebar-link<?= $current === 'settings' ? ' active' : '' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06"/></svg>
          Paramètres
        </a>
      </div>
    </nav>
    <div class="sidebar-foot">
      <span class="sidebar-user-avatar"><?= e($user_initials) ?></span>
      <div class="sidebar-user-info">
        <strong><?= e($user_name) ?></strong>
        <span><?= e(ucfirst(str_replace('_', ' ', $user['role']))) ?></span>
      </div>
      <a href="logout.php" title="Déconnexion" style="color: rgba(250,246,240,0.6); width: 30px; height: 30px; border-radius: var(--radius-sm); display: grid; place-items: center;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </aside>

  <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

  <div class="main">
    <header class="topbar">
      <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="topbar-search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Rechercher commandes, produits, clients...">
        <kbd>⌘K</kbd>
      </div>
      <div class="topbar-actions">
        <form method="post" action="toggle-coming-soon.php" class="coming-soon-toggle"
              onsubmit="return confirm('<?= $coming_soon_on
                  ? "Réactiver la boutique pour tous les visiteurs ?"
                  : "Activer le mode « Bientôt disponible » ? La boutique sera masquée et un écran d'attente sera affiché aux visiteurs." ?>');">
          <?= csrf_field() ?>
          <input type="hidden" name="back" value="<?= e(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php')) ?>">
          <button type="submit" class="cs-toggle-btn<?= $coming_soon_on ? ' is-on' : '' ?>"
                  title="<?= $coming_soon_on ? 'Boutique masquée · cliquer pour la réactiver' : 'Activer le mode bientôt disponible' ?>">
            <span class="cs-dot"></span>
            <span class="cs-label"><?= $coming_soon_on ? 'Bientôt disponible' : 'Boutique en ligne' ?></span>
          </button>
        </form>
        <a href="../<?= $coming_soon_on ? 'coming-soon.php' : 'index.php' ?>" target="_blank" class="topbar-btn" title="Voir la boutique">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        <button class="topbar-btn" title="Notifications">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
          <?php if ($pending_count > 0): ?><span class="dot"></span><?php endif; ?>
        </button>
        <div class="topbar-divider"></div>
        <div class="topbar-user">
          <span class="topbar-user-avatar"><?= e($user_initials) ?></span>
          <span class="topbar-user-name"><?= e($user_name) ?></span>
        </div>
      </div>
    </header>

    <?php if ($admin_flash): ?>
      <div class="admin-flash flash-<?= e($admin_flash['type']) ?>"><?= e($admin_flash['msg']) ?></div>
    <?php endif; ?>

    <?php if ($coming_soon_on): ?>
      <div class="cs-banner">
        <svg class="icon-inline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><strong>Mode « Bientôt disponible » actif</strong> · la boutique est masquée pour les visiteurs publics. Vous voyez les pages admin normalement.
      </div>
    <?php endif; ?>
