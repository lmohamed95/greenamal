-- Flag a starter set of hero products so the "Coups de cœur" section
-- on the homepage isn't empty out of the box. The homepage query is:
--   SELECT ... WHERE status='active' AND is_featured=1
--   ORDER BY sales_count DESC LIMIT 4
--
-- Adjust the selection later by editing each product in /admin/products
-- (toggle "En vedette") or by running another UPDATE here.

UPDATE products SET is_featured = 1
WHERE slug IN (
  'huile-argan',
  'he-lavande',
  'couscous-orge',
  'pack-huiles-essentielles',
  'huile-rose',
  'greenmoukhammaria',
  'pam-alward-rose',
  'savon-noir-beldi-200g'
);
