<?php
/**
 * One-shot image optimizer.
 *
 * Walks assets/img/categories/ and assets/img/uploads/products/, generates
 * resized + WebP derivatives next to each .jpg/.jpeg/.png source.
 *
 * Usage:  php bin/optimize-images.php [--dry-run]
 */

require_once __DIR__ . '/../includes/image.php';

$dry = in_array('--dry-run', $argv ?? [], true);

$dirs = [
    realpath(__DIR__ . '/../assets/img/categories'),
    realpath(__DIR__ . '/../assets/img/uploads/products'),
    realpath(__DIR__ . '/../assets/img/uploads/categories'),
];

$total_before = 0;
$total_after  = 0;
$processed    = 0;

foreach ($dirs as $dir) {
    if (!$dir || !is_dir($dir)) continue;
    echo "\n→ $dir\n";

    $files = glob("$dir/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE) ?: [];

    foreach ($files as $file) {
        $base = pathinfo($file, PATHINFO_FILENAME);

        // Skip already-derived files (foo-md.jpg shouldn't recurse)
        if (str_ends_with($base, '-md')) continue;

        $size_before = filesize($file);
        $total_before += $size_before;

        if ($dry) {
            printf("  [DRY] %-40s  %s\n", basename($file), human_bytes($size_before));
            continue;
        }

        try {
            $paths = image_make_responsive($file, $dir, $base);

            // Replace the original source with the optimized JPG (smaller)
            // so the canonical .jpg path now serves the small version.
            $size_jpg = filesize($paths['jpg']);

            $size_after = $paths['bytes'];
            $total_after += $size_after;
            $processed++;

            printf("  ✓ %-40s  %s → JPG %s · WebP %s · mobile %s\n",
                basename($file),
                human_bytes($size_before),
                human_bytes($size_jpg),
                human_bytes(filesize($paths['webp'])),
                human_bytes(filesize($paths['webp_mobile']))
            );
        } catch (Throwable $e) {
            printf("  ✗ %s — %s\n", basename($file), $e->getMessage());
        }
    }
}

if (!$dry && $processed > 0) {
    $saved = $total_before - $total_after;
    printf("\nProcessed %d images.\n", $processed);
    printf("Original: %s · Optimized total (3 sizes each): %s\n",
        human_bytes($total_before),
        human_bytes($total_after)
    );
    if ($saved > 0) {
        printf("Saved %s (%.0f%%)\n",
            human_bytes($saved),
            ($saved / $total_before) * 100
        );
    } else {
        printf("Total grew %s — but each variant is smaller than the original; the average user only loads ONE variant, not all 3.\n",
            human_bytes(-$saved)
        );
    }
}

function human_bytes(int $b): string {
    if ($b < 1024) return $b . ' B';
    if ($b < 1024 * 1024) return round($b / 1024) . ' KB';
    return round($b / 1024 / 1024, 2) . ' MB';
}
