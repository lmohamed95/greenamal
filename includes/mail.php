<?php
/**
 * GreenAmal · Email helpers (transactional)
 *
 * Uses PHP mail() by default. On Namecheap shared hosting this works out of the
 * box once SPF/DKIM are configured (cPanel → Email Deliverability).
 *
 * For production-grade deliverability, swap send_mail_raw() to call a service
 * like Resend (https://resend.com/docs/api-reference/emails/send-email) by
 * setting RESEND_API_KEY in config.local.php.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

const MAIL_FROM_NAME = 'GreenAmal';

function mail_from_address(): string {
    return defined('MAIL_FROM') ? MAIL_FROM : (CONTACT_EMAIL ?: 'noreply@greenamal.com');
}

/**
 * Wraps the body in the standard responsive email shell.
 */
function mail_layout(string $title, string $inner): string {
    $logo  = SITE_NAME;
    $year  = date('Y');
    $brand = '#3A5A40';
    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:#FAF6F0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:#2A2A2A;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF6F0;padding:24px 12px;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #EDE6D6;">
        <tr><td style="background:{$brand};padding:20px 28px;color:#FAF6F0;font-size:18px;font-weight:600;">{$logo}</td></tr>
        <tr><td style="padding:28px;line-height:1.55;font-size:15px;">
          {$inner}
        </td></tr>
        <tr><td style="background:#F4ECD8;padding:18px 28px;font-size:12px;color:#7A7A7A;">
          GreenAmal · Coopérative Al Amal · Azrou, Maroc<br>
          Une question ? Écrivez-nous à <a href="mailto:contact@greenamal.com" style="color:{$brand};">contact@greenamal.com</a><br>
          &copy; {$year} GreenAmal. Tous droits réservés.
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Send an HTML email. Returns true on success.
 *
 * In APP_DEBUG=true mode, instead of actually sending, the email is appended
 * to /tmp/greenamal-mail.log so you can inspect it during local development.
 */
function send_mail(string $to, string $subject, string $html): bool {
    $from = mail_from_address();
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <{$from}>\r\n";
    $headers .= "Reply-To: <{$from}>\r\n";
    $headers .= "X-Mailer: GreenAmal\r\n";

    if (APP_DEBUG) {
        $log = "/tmp/greenamal-mail.log";
        $sep = str_repeat('=', 60);
        @file_put_contents($log, "{$sep}\n[" . date('Y-m-d H:i:s') . "] To: {$to}\nSubject: {$subject}\n\n{$html}\n", FILE_APPEND);
        return true;
    }

    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
}

/* ---------------------------------------------------------------------------
 *  Templates
 * ------------------------------------------------------------------------- */

/** Order confirmation to customer */
function mail_order_confirmation(array $order, array $items): bool {
    $rows = '';
    foreach ($items as $i) {
        $rows .= sprintf(
            '<tr><td style="padding:8px 0;border-bottom:1px solid #EDE6D6;">%s × %d</td><td style="padding:8px 0;border-bottom:1px solid #EDE6D6;text-align:right;">%s</td></tr>',
            e($i['product_name']), (int) $i['quantity'],
            e(price((float) $i['total']))
        );
    }
    $view_link = url('order-confirmation.php?order=' . urlencode($order['order_number']) . '&t=' . order_view_token($order['order_number']));
    $inner = '<h1 style="margin:0 0 12px;font-size:22px;color:#3A5A40;">Merci pour votre commande !</h1>'
        . '<p>Bonjour ' . e($order['shipping_name']) . ',<br>'
        . 'Votre commande <strong>' . e($order['order_number']) . '</strong> a bien été enregistrée.</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:16px 0;">'
        . $rows
        . '<tr><td style="padding-top:10px;">Sous-total</td><td style="padding-top:10px;text-align:right;">' . e(price((float) $order['subtotal'])) . '</td></tr>'
        . '<tr><td>Livraison</td><td style="text-align:right;">' . e(price((float) $order['shipping'])) . '</td></tr>'
        . ($order['discount'] > 0 ? '<tr><td>Réduction</td><td style="text-align:right;">−' . e(price((float) $order['discount'])) . '</td></tr>' : '')
        . '<tr><td style="padding-top:8px;font-weight:600;">Total</td><td style="padding-top:8px;text-align:right;font-weight:600;">' . e(price((float) $order['total'])) . '</td></tr>'
        . '</table>'
        . '<p>Mode de paiement : <strong>' . (($order['payment_method'] === 'cod') ? 'À la livraison' : strtoupper($order['payment_method'])) . '</strong></p>'
        . '<p>Adresse de livraison :<br>' . nl2br(e($order['shipping_address'])) . '<br>' . e($order['shipping_city']) . ' ' . e($order['shipping_postcode']) . '</p>'
        . '<p style="margin-top:20px;"><a href="' . $view_link . '" style="color:#3A5A40;font-weight:600;">Voir le détail de ma commande →</a></p>'
        . '<p style="margin-top:24px;">Nous vous tiendrons informé(e) dès l\'expédition. Pour toute question, répondez simplement à cet email.</p>';
    return send_mail($order['shipping_email'], 'Confirmation de commande ' . $order['order_number'], mail_layout('Confirmation', $inner));
}

/** New-order notification to admin */
function mail_admin_new_order(array $order): bool {
    $admin = CONTACT_EMAIL ?: 'admin@greenamal.com';
    $inner = '<h1 style="margin:0 0 12px;font-size:20px;color:#3A5A40;">Nouvelle commande</h1>'
        . '<p><strong>' . e($order['order_number']) . '</strong> · ' . e(price((float) $order['total'])) . '</p>'
        . '<p>Client : ' . e($order['shipping_name']) . ' · ' . e($order['shipping_phone']) . '<br>'
        . 'Email : ' . e($order['shipping_email']) . '<br>'
        . 'Ville : ' . e($order['shipping_city']) . '</p>'
        . '<p><a href="' . url('admin/order-detail.php?id=' . (int) $order['id']) . '" style="color:#3A5A40;font-weight:600;">Voir dans l\'admin →</a></p>';
    return send_mail($admin, 'Nouvelle commande ' . $order['order_number'], mail_layout('Nouvelle commande', $inner));
}

/** Status change to customer */
function mail_order_status(array $order, string $new_status): bool {
    [$lbl, ] = order_status_label($new_status);
    $msg = match ($new_status) {
        'processing' => 'Votre commande est en cours de préparation. Nous vous notifierons dès l\'expédition.',
        'shipped'    => 'Votre colis a été expédié ! Vous le recevrez dans 2 à 5 jours ouvrables.',
        'delivered'  => 'Votre commande a été livrée. Merci pour votre confiance · n\'hésitez pas à laisser un avis !',
        'cancelled'  => 'Votre commande a été annulée. Si vous n\'êtes pas à l\'origine de cette action, contactez-nous immédiatement.',
        default      => 'Le statut de votre commande a été mis à jour.',
    };
    $inner = '<h1 style="margin:0 0 12px;font-size:20px;color:#3A5A40;">Mise à jour : ' . e($lbl) . '</h1>'
        . '<p>Commande <strong>' . e($order['order_number']) . '</strong></p>'
        . '<p>' . e($msg) . '</p>'
        . '<p><a href="' . url('order-confirmation.php?order=' . urlencode($order['order_number']) . '&t=' . order_view_token($order['order_number'])) . '" style="color:#3A5A40;font-weight:600;">Voir ma commande →</a></p>';
    return send_mail($order['shipping_email'], '[' . $order['order_number'] . '] ' . $lbl, mail_layout($lbl, $inner));
}

/** Password reset */
function mail_password_reset(string $email, string $token): bool {
    $link = url('reset-password.php?token=' . urlencode($token));
    $inner = '<h1 style="margin:0 0 12px;font-size:20px;color:#3A5A40;">Réinitialisation de mot de passe</h1>'
        . '<p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :</p>'
        . '<p style="margin:18px 0;"><a href="' . $link . '" style="display:inline-block;background:#3A5A40;color:#FAF6F0;padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:600;">Réinitialiser mon mot de passe</a></p>'
        . '<p style="font-size:13px;color:#7A7A7A;">Ce lien expire dans 1 heure. Si vous n\'avez pas fait cette demande, ignorez simplement cet email.</p>';
    return send_mail($email, 'Réinitialisation de votre mot de passe GreenAmal', mail_layout('Mot de passe', $inner));
}

/** Welcome / account created */
function mail_welcome(string $email, string $first_name): bool {
    $inner = '<h1 style="margin:0 0 12px;font-size:22px;color:#3A5A40;">Bienvenue chez GreenAmal !</h1>'
        . '<p>Bonjour ' . e($first_name) . ',</p>'
        . '<p>Votre compte a bien été créé. Vous pouvez maintenant suivre vos commandes, sauvegarder vos adresses et utiliser le code <strong>FIRST25</strong> pour −25 % sur votre première commande.</p>'
        . '<p style="margin:18px 0;"><a href="' . url('shop.php') . '" style="display:inline-block;background:#3A5A40;color:#FAF6F0;padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:600;">Découvrir la boutique</a></p>';
    return send_mail($email, 'Bienvenue chez GreenAmal', mail_layout('Bienvenue', $inner));
}
