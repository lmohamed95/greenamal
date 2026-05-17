-- 2026-05-17 · Copywriting cleanup
-- 1. Replace "berbère" with "amazigh" wherever it appears in product descriptions.
-- 2. Strip the em-dash (—) from any saved home_* setting that still has one.
-- Re-runnable: REPLACE is a no-op if the substring isn't present.

UPDATE products
SET description_short = REPLACE(REPLACE(description_short, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE description_short LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE products
SET description_long = REPLACE(REPLACE(description_long, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE description_long LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE products
SET meta_title = REPLACE(REPLACE(meta_title, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE meta_title LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE products
SET meta_description = REPLACE(REPLACE(meta_description, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE meta_description LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE products
SET tags = REPLACE(REPLACE(tags, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE tags LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE products
SET name = REPLACE(REPLACE(name, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE name LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE categories
SET description = REPLACE(REPLACE(description, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE description LIKE '%berb%' COLLATE utf8mb4_general_ci;

UPDATE categories
SET name = REPLACE(REPLACE(name, 'berbère', 'amazighe'), 'Berbère', 'Amazighe')
WHERE name LIKE '%berb%' COLLATE utf8mb4_general_ci;

-- Em-dash → comma in settings (only one row affected today, but safe to run repeatedly)
UPDATE settings
SET setting_value = REPLACE(setting_value, 'histoire — celle', 'histoire, celle')
WHERE setting_value LIKE '%histoire — celle%';

UPDATE settings
SET setting_value = REPLACE(setting_value, 'top — 36h', 'top, 36h')
WHERE setting_value LIKE '%top — 36h%';
