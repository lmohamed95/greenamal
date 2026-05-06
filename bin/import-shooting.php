<?php
/**
 * One-shot importer: shooting images → optimized assets + products SQL.
 *
 * Usage:  php bin/import-shooting.php
 *
 *   1. Reads images from ~/Desktop/Al amal products shooting-2/
 *   2. Copies + optimizes each into /assets/img/uploads/products/
 *   3. Writes /sql/products-shooting.sql with DELETE + INSERT statements.
 */

require_once __DIR__ . '/../includes/image.php';

const SOURCE_DIR  = '/Users/mohamedlagrighi/Desktop/Al amal products shooting-2';
const DEST_REL    = '/assets/img/uploads/products';
const SQL_OUT     = __DIR__ . '/../sql/products-shooting.sql';
const PRICE_MAD   = 100.00;

$dest_abs = realpath(__DIR__ . '/..') . DEST_REL;
if (!is_dir($dest_abs)) mkdir($dest_abs, 0755, true);

/**
 * Product catalogue.
 * folder → [ [name, slug, sku, files[]], ... ]
 * 'files' lists files relative to the folder, in display order. First = main image.
 */
$catalogue = [
    'Other' => [
        'category' => 'divers',
        'products' => [
            ['GreenSmile Poudre Dentaire', 'greensmile-poudre-dentaire', 'COSM-GS-DEN', ['GreenSmile-Poudre-Dentaire-01.jpg','GreenSmile-Poudre-Dentaire-02.jpg']],
        ],
    ],
    'Les poudres' => [
        'category' => 'poudres',
        'products' => [
            ['Réglisse moulu', 'reglisse-moulu', 'POU-REG', ['Reglisse-Moulu-01.jpg','Reglisse-Moulu-02.jpg']],
            ['Menthe pouliot poudre', 'menthe-pouliot-poudre', 'POU-MPL', ['Menthe-Pouliot-Poudre-01.jpg','Menthe-Pouliot-Poudre-02.jpg','Menthe-Pouliot-Poudre-03.jpg','Menthe-Pouliot-Poudre-04.jpg']],
            ['Rose moulue', 'rose-moulue', 'POU-ROS', ['Rose-Moulu-01.jpg','Rose-Moulu-02.jpg']],
            ['Origan poudre', 'origan-poudre', 'POU-ORG', ['Origan-Poudre-01.jpg','Origan-Poudre-02.jpg']],
        ],
    ],
    'Couscous' => [
        'category' => 'couscous',
        'products' => [
            ['Berkoukch', 'berkoukch', 'CSC-BRK', ['Berkoukch.jpg']],
            ['Couscous graines de lin', 'couscous-graines-lin', 'CSC-LIN', ['Couscous-Grains-de-Lin.jpg']],
            ['Couscous maïs', 'couscous-mais', 'CSC-MAI', ['Couscous-Mais.jpg']],
            ['Couscous sauge', 'couscous-sauge', 'CSC-SAU', ['Couscous-Sauge.jpg']],
            ['Couscous d\'orge', 'couscous-orge', 'CSC-ORG', ['Couscous-Orge.jpg']],
            ['Couscous lentilles', 'couscous-lentilles', 'CSC-LEN', ['Couscous-Lentille.jpg']],
            ['Couscous rouge', 'couscous-rouge', 'CSC-ROU', ['Couscous-Rouge.jpg']],
            ['Couscous complet', 'couscous-complet', 'CSC-CPL', ['Couscous-Complet.jpg']],
            ['Couscous khoumasi', 'couscous-khoumasi', 'CSC-KHM', ['Couscous-Khoumasi.jpg']],
            ['Couscous blé', 'couscous-ble', 'CSC-BLE', ['Couscous-Ble.jpg']],
            ['Couscous aux herbes', 'couscous-herbes', 'CSC-HRB', ['Couscous-Herbes.jpg']],
        ],
    ],
    'Farine' => [
        'category' => 'farine',
        'products' => [
            ['Farine d\'orge torréfiée', 'farine-orge-torrefiee', 'FAR-ORG-T', ['Farine-Orge-Torrefiee-01.jpg','Farine-Orge-Torrefiee-02.jpg','Farine-Orge-Torrefiee-03.jpg']],
            ['Talbina nabawiya', 'talbina-nabawiya', 'FAR-TLB', ['Talbina-Nabawiya.jpg']],
            ['Farine de maïs', 'farine-mais', 'FAR-MAI', ['Farine-Mais.jpg']],
            ['Farine de lentilles', 'farine-lentilles', 'FAR-LEN', ['Farine-Lentilles.jpg']],
            ['Farine de blé torréfiée', 'farine-ble-torrefiee', 'FAR-BLE-T', ['Farine-Ble-Torrefiee.jpg']],
            ['Farine de maïs torréfiée', 'farine-mais-torrefiee', 'FAR-MAI-T', ['Farine-Mais-Torrefiee.jpg']],
            ['Farine de pois chiches', 'farine-pois-chiches', 'FAR-PCH', ['Farine-Pois-Chiches.jpg']],
            ['Farine de millet', 'farine-millet', 'FAR-MIL', ['Farine-Millet.jpg']],
            ['Farine de blé complet', 'farine-ble-complet', 'FAR-BLE-C', ['Farine-Ble-Complet.jpg']],
        ],
    ],
    'Eau Floral' => [
        'category' => 'eau-florale',
        'products' => [
            ['Eau florale de bleuet', 'eau-floral-bleuet', 'EAU-BLE', ['Eau-Floral-Bleuet-01.jpg','Eau-Floral-Bleuet-02.jpg']],
            ['Eau florale de fleur d\'oranger', 'eau-floral-fleur-oranger', 'EAU-ORG', ['Eau-Floral-Fleur-Oranger-01.jpg','Eau-Floral-Fleur-Oranger-02.jpg']],
            ['Eau florale de rose', 'eau-floral-rose', 'EAU-ROS', ['Eau-Floral-Rose-01.jpg','Eau-Floral-Rose-02.jpg']],
            ['Eau florale de camomille', 'eau-floral-camomille', 'EAU-CAM', ['Eau-Floral-Camomille-01.jpg','Eau-Floral-Camomille-02.jpg']],
            ['GreenBoost — distillat capillaire', 'greenboost', 'EAU-GBT', ['GreenBoost-01.jpg','GreenBoost-02.jpg']],
            ['GreenEssence — huile parfumée', 'greenessence', 'EAU-GES', ['GreenEssence-01.jpg','GreenEssence-02.jpg']],
            ['GreenSilk — huile post-épilation', 'greensilk', 'EAU-GSK', ['GreenSilk-01.jpg','GreenSilk-02.jpg']],
        ],
    ],
    'Savon' => [
        'category' => 'savons',
        'products' => [
            ['Green Ritual Tabrima', 'green-ritual-tabrima', 'SAV-GRT', ['Green-Ritual-Tabrima-01.jpg','Green-Ritual-Tabrima-02.jpg']],
            ['Poudre de jujubier', 'poudre-jujubier', 'SAV-JUJ', ['Poudre-Jujubier-01.jpg','Poudre-Jujubier-02.jpg']],
            ['Koumaj el jisem', 'koumaj-jisem', 'SAV-KOU', ['Koumaj-Jisem-01.jpg','Koumaj-Jisem-02.jpg']],
            ['Gommage au café', 'gommage-cafe', 'SAV-CAF', ['Gommage-Cafe-01.jpg','Gommage-Cafe-02.jpg']],
            ['Gommage au nila & huiles', 'gommage-nila-huiles', 'SAV-NIL', ['Gommage-Nila-Huile-01.jpg','Gommage-Nila-Huile-02.jpg']],
            ['GreenRitual Savon — bleu', 'greenritual-savon-bleu', 'SAV-GR-B', ['GreenRitual-Savon-Bleu.jpg']],
            ['GreenRitual Savon — rose', 'greenritual-savon-rose', 'SAV-GR-R', ['GreenRitual-Savon-Rose.jpg']],
            ['GreenRitual Savon — herbal', 'greenritual-savon-herbal', 'SAV-GR-H', ['GreenRitual-Savon-Herbal.jpg']],
            ['GreenMoukhammaria', 'greenmoukhammaria', 'SAV-GMK', ['GreenMoukhammaria-01.jpg','GreenMoukhammaria-02.jpg']],
        ],
    ],
    'huile essentielle' => [
        'category' => 'huiles-essentielles',
        'products' => [
            ['Huile essentielle de lavande', 'he-lavande', 'HE-LAV', ['HE-Lavande-01.jpg','HE-Lavande-02.jpg','HE-Lavande-03.jpg']],
            ['Huile essentielle de gingembre', 'he-gingembre', 'HE-GIN', ['HE-Gingembre-01.jpg','HE-Gingembre-02.jpg']],
            ['Huile essentielle de menthe pouliot', 'he-menthe-pouliot', 'HE-MPL', ['HE-Menthe-Pouliot-01.jpg','HE-Menthe-Pouliot-02.jpg']],
            ['Huile essentielle de romarin', 'he-romarin', 'HE-ROM', ['HE-Romarin-01.jpg','HE-Romarin-02.jpg']],
            ['Huile essentielle de menthe poivrée', 'he-menthe-poivre', 'HE-MPV', ['HE-Menthe-Poivre-01.jpg','HE-Menthe-Poivre-02.jpg']],
            ['Huile essentielle d\'eucalyptus', 'he-eucalyptus', 'HE-EUC', ['HE-Eucalyptus-01.jpg','HE-Eucalyptus-02.jpg']],
            ['Huile essentielle de fleur d\'oranger', 'he-fleur-oranger', 'HE-FOR', ['HE-Fleur-Orange-01.jpg','HE-Fleur-Orange-02.jpg']],
            ['Huile essentielle de citron', 'he-citron', 'HE-CIT', ['HE-Citron-01.jpg','HE-Citron-02.jpg']],
        ],
    ],
    'PAM' => [
        'category' => 'pam',
        'products' => [
            ['Bourgeons de rose (Alward)', 'pam-alward-rose', 'PAM-ALW', ['PAM-Alward-Rose.jpg']],
            ['Clous de girofle', 'pam-girofle', 'PAM-GIR', ['PAM-Girofle.jpg']],
            ['Bleuet séché', 'pam-bleuet', 'PAM-BLE', ['PAM-Bleuet.jpg']],
            ['GreenCalme — mélange apaisant', 'pam-greencalme', 'PAM-GCL', ['PAM-GreenCalme-01.jpg','PAM-GreenCalme-02.jpg','PAM-GreenCalme-03.jpg','PAM-GreenCalme-04.jpg','PAM-GreenCalme-05.jpg']],
            ['Romarin séché', 'pam-romarin', 'PAM-ROM', ['PAM-Romarin.jpg']],
            ['Feuilles de jujubier', 'pam-jujubier', 'PAM-JUJ', ['PAM-Jujubier.jpg']],
            ['Lavande séchée', 'pam-lavande', 'PAM-LAV', ['PAM-Lavande.jpg']],
            ['Menthe pouliot séchée', 'pam-menthe-pouliot', 'PAM-MPL', ['PAM-Menthe-Pouliot.jpg']],
            ['Camomille séchée', 'pam-camomille', 'PAM-CAM', ['PAM-Camomille.jpg']],
            ['Marjolaine séchée', 'pam-marjolaine', 'PAM-MAR', ['PAM-Marjolin.jpg']],
            ['Sauge séchée', 'pam-sauge', 'PAM-SAU', ['PAM-Sauge.jpg']],
            ['Mélisse séchée', 'pam-melisse', 'PAM-MEL', ['PAM-Melisse.jpg']],
            ['Thym séché', 'pam-thym', 'PAM-THY', ['PAM-Thym.jpg']],
            ['Verveine séchée', 'pam-verveine', 'PAM-VRV', ['PAM-Verveine.jpg']],
            ['Eucalyptus séché', 'pam-eucalyptus', 'PAM-EUC', ['PAM-Eucalyptus.jpg']],
            ['Moringa séché', 'pam-moringa', 'PAM-MOR', ['PAM-Moringa.jpg']],
            ['Origan séché', 'pam-origan', 'PAM-ORI', ['PAM-Origan.jpg']],
        ],
    ],
    'oil' => [
        'category' => 'huiles-vegetales',
        'products' => [
            ['Huile de massage', 'huile-massage', 'OIL-MAS', ['Huile-Massage-01.jpg','Huile-Massage-02.jpg']],
            ['Huile de graines de lin', 'huile-graines-lin', 'OIL-LIN', ['Huile-Grains-Lin-01.jpg','Huile-Grains-Lin-02.jpg']],
            ['Huile de rose', 'huile-rose', 'OIL-ROS', ['Huile-Rose-01.jpg','Huile-Rose-02.jpg']],
            ['Huile de jojoba', 'huile-jojoba', 'OIL-JOJ', ['Huile-Jojoba-01.jpg','Huile-Jojoba-02.jpg']],
            ['Huile de camomille', 'huile-camomille', 'OIL-CAM', ['Huile-Camomille-01.jpg','Huile-Camomille-02.jpg']],
            ['Huile d\'argan à la fleur d\'oranger', 'huile-argan-fleur-oranger', 'OIL-ARG-F', ['Huile-Argan-Fleur-Oranger-01.jpg','Huile-Argan-Fleur-Oranger-02.jpg']],
            ['Huile de sésame', 'huile-sesame', 'OIL-SES', ['Huile-Sesame-01.jpg','Huile-Sesame-02.jpg']],
            ['Huile d\'argan', 'huile-argan', 'OIL-ARG', ['Huile-Argan-01.jpg','Huile-Argan-02.jpg']],
            ['Huile de graine d\'oignon', 'huile-graine-oignon', 'OIL-OIG', ['Huile-Graine-Oignon-01.jpg','Huile-Graine-Oignon-02.jpg']],
            ['Huile d\'amande amère', 'huile-amande-amere', 'OIL-AMD', ['Huile-Amande-Amere-01.jpg','Huile-Amande-Amere-02.jpg']],
            ['Huile de romarin', 'huile-romarin', 'OIL-ROM', ['Huile-Romarin-01.jpg','Huile-Romarin-02.jpg']],
            ['Huile anti-chute', 'huile-anti-chute', 'OIL-ANT', ['Huile-Anti-Chute-01.jpg','Huile-Anti-Chute-02.jpg']],
            ['Huile de graine noire (nigelle)', 'huile-graine-noire', 'OIL-NIG', ['Huile-Graine-Noire-01.jpg','Huile-Graine-Noire-02.jpg']],
            ['Huile de lavande', 'huile-lavande', 'OIL-LAV', ['Huile-Lavande-01.jpg','Huile-Lavande-02.jpg']],
        ],
    ],
    'les packs' => [
        'category' => 'packs',
        'products' => [
            ['Pack Talbina', 'pack-talbina', 'PCK-TLB', ['Pack-Talbina-01.jpg','Pack-Talbina-02.jpg','Pack-Talbina-Box-01.jpg','Pack-Talbina-Box-02.jpg','Pack-Talbina-Box-03.jpg']],
            ['Pack cosmétique GreenAmal', 'pack-cosmetique', 'PCK-COS', ['Pack-Cosmetique-01.jpg','Pack-Cosmetique-02.jpg','Pack-Cosmetique-03.jpg','Pack-Cosmetique-Box-01.jpg','Pack-Cosmetique-Box-02.jpg','Pack-Cosmetique-Box-03.jpg']],
            ['Pack eaux florales', 'pack-eaux-florales', 'PCK-EAU', ['Pack-Eau-Floral-Bottles-01.jpg','Pack-Eau-Floral-Bottles-02.jpg','Pack-Eau-Floral-Box-01.jpg','Pack-Eau-Floral-Box-02.jpg','Pack-Eau-Floral-Box-03.jpg','Pack-Eau-Floral-Box-04.jpg','Pack-Eau-Floral-Box-05.jpg']],
            ['Pack rose', 'pack-rose', 'PCK-ROS', ['Pack-Rose-01.jpg','Pack-Rose-02.jpg','Pack-Rose-Box-01.jpg','Pack-Rose-Box-02.jpg','Pack-Rose-Box-03.jpg']],
            ['Pack huiles essentielles', 'pack-huiles-essentielles', 'PCK-HE', ['Pack-Huile-Essentielle.jpg']],
            ['Pack huiles végétales', 'pack-huiles-vegetales', 'PCK-OIL', ['Pack-Huiles.jpg']],
            ['Pack poudres', 'pack-poudres', 'PCK-POU', ['Pack-Poudres.jpg']],
            ['Pack gommage café & savon', 'pack-gommage-cafe-savon', 'PCK-GCS', ['Pack-Gommage-Cafe-Savon-01.jpg','Pack-Gommage-Cafe-Savon-02.jpg']],
            ['Pack koumaj & nila', 'pack-koumaj-nila', 'PCK-KNL', ['Pack-Koumaj-Nila-01.jpg','Pack-Koumaj-Nila-02.jpg']],
        ],
    ],
];

/* --- Pass 1: copy + optimize all images, build URL map -------------------- */

$image_url = []; // filename → DB url (relative path with .jpg)
$processed = 0;

foreach ($catalogue as $folder => $info) {
    foreach ($info['products'] as [$name, $slug, $sku, $files]) {
        foreach ($files as $i => $filename) {
            $src = SOURCE_DIR . '/' . $folder . '/' . $filename;
            if (!file_exists($src)) {
                fwrite(STDERR, "MISSING: $src\n");
                continue;
            }
            // Image slug = product-slug + suffix for gallery items
            $img_slug = $slug . ($i === 0 ? '' : '-' . ($i + 1));
            $staging = $dest_abs . '/' . $img_slug . '.tmp';
            copy($src, $staging);
            try {
                image_make_responsive($staging, $dest_abs, $img_slug);
            } catch (Throwable $e) {
                fwrite(STDERR, "OPTIMIZE FAIL [$filename]: {$e->getMessage()}\n");
            }
            @unlink($staging);
            $image_url[$folder . '/' . $filename] = DEST_REL . '/' . $img_slug . '.jpg';
            $processed++;
            echo "  [$processed] $img_slug.jpg\n";
        }
    }
}

echo "\nProcessed $processed images. Generating SQL...\n";

/* --- Pass 2: build SQL ---------------------------------------------------- */

$sql = "-- Generated by bin/import-shooting.php\n";
$sql .= "-- Replaces existing products with the GreenAmal shooting catalogue\n\n";
$sql .= "USE greenamal;\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
$sql .= "DELETE FROM product_images;\n";
$sql .= "DELETE FROM reviews;\n";
$sql .= "UPDATE order_items SET product_id = NULL;\n";
$sql .= "DELETE FROM products;\n";
$sql .= "ALTER TABLE products AUTO_INCREMENT = 1;\n";
$sql .= "ALTER TABLE product_images AUTO_INCREMENT = 1;\n";
$sql .= "SET FOREIGN_KEY_CHECKS=1;\n\n";

foreach ($catalogue as $folder => $info) {
    $cat = $info['category'];
    $sql .= "-- {$folder} → {$cat}\n";
    foreach ($info['products'] as [$name, $slug, $sku, $files]) {
        $main_url = $image_url[$folder . '/' . $files[0]] ?? '';
        $sql .= sprintf(
            "INSERT INTO products (slug, sku, name, category_id, description_short, price, stock, image_main, status) VALUES " .
            "('%s', '%s', '%s', (SELECT id FROM categories WHERE slug='%s'), '%s', %.2f, 0, '%s', 'draft');\n",
            esc($slug), esc($sku), esc($name), esc($cat),
            esc('Produit artisanal de la Coopérative Al Amal — ' . $name . '.'),
            PRICE_MAD,
            esc($main_url)
        );
        // Gallery (skip first; it's already image_main)
        for ($i = 1; $i < count($files); $i++) {
            $u = $image_url[$folder . '/' . $files[$i]] ?? '';
            if (!$u) continue;
            $sql .= sprintf(
                "INSERT INTO product_images (product_id, url, display_order) VALUES " .
                "((SELECT id FROM products WHERE slug='%s'), '%s', %d);\n",
                esc($slug), esc($u), $i
            );
        }
    }
    $sql .= "\n";
}

file_put_contents(SQL_OUT, $sql);
echo "Wrote " . SQL_OUT . " (" . number_format(strlen($sql)) . " bytes)\n";
echo "\nNext: mysql -h 127.0.0.1 -u root greenamal < " . SQL_OUT . "\n";

function esc(string $s): string {
    return str_replace("'", "''", $s);
}
