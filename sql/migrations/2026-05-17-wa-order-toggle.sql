-- 2026-05-17 · WhatsApp redesign
-- 1. Remove the orphan whatsapp_number row (WA number is now derived from contact_phone).
-- 2. Seed the whatsapp_order_enabled toggle to OFF so the public site doesn't
--    suddenly show the FAB + product WA CTA after deploy. Admin can flip it on
--    from Admin → Paramètres → Affichage & démo.

DELETE FROM settings WHERE setting_key = 'whatsapp_number';

INSERT INTO settings (setting_key, setting_value) VALUES ('whatsapp_order_enabled', '0')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
