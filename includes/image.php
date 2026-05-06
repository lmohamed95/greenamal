<?php
/**
 * Image optimization + responsive helpers.
 *
 * Convention: a stored image at `/path/to/foo.jpg` has companion files:
 *   /path/to/foo.webp        — same dimensions, modern WebP encoding
 *   /path/to/foo-md.webp     — half-width mobile WebP
 *   /path/to/foo-md.jpg      — half-width mobile JPEG (fallback)
 *
 * The .jpg path is what we store in the DB. picture_tag() generates the rest.
 */

require_once __DIR__ . '/helpers.php';

const IMG_MAX_WIDTH    = 1600;
const IMG_MOBILE_WIDTH = 800;
const IMG_JPEG_QUALITY = 82;
const IMG_WEBP_QUALITY = 78;

/**
 * Generate the full responsive set from a source image.
 *
 * @return array{jpg:string,webp:string,webp_mobile:string,jpg_mobile:string,bytes:int} relative paths + total bytes written
 */
function image_make_responsive(string $source_abs, string $out_dir_abs, string $slug): array {
    if (!is_dir($out_dir_abs)) {
        mkdir($out_dir_abs, 0755, true);
    }

    $info = @getimagesize($source_abs);
    if (!$info) {
        throw new RuntimeException("Cannot read image: $source_abs");
    }
    [$src_w, $src_h] = $info;
    $mime = $info['mime'] ?? '';

    $src = match (true) {
        $mime === 'image/jpeg' => imagecreatefromjpeg($source_abs),
        $mime === 'image/png'  => imagecreatefrompng($source_abs),
        $mime === 'image/webp' => imagecreatefromwebp($source_abs),
        $mime === 'image/gif'  => imagecreatefromgif($source_abs),
        default => throw new RuntimeException("Unsupported image type: $mime"),
    };

    if (!$src) throw new RuntimeException("Cannot decode image: $source_abs");

    // Auto-rotate JPEGs based on EXIF orientation
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($source_abs);
        if (!empty($exif['Orientation'])) {
            $src = match ((int) $exif['Orientation']) {
                3 => imagerotate($src, 180, 0),
                6 => imagerotate($src, -90, 0),
                8 => imagerotate($src, 90, 0),
                default => $src,
            };
            // Refresh dimensions after rotation
            $src_w = imagesx($src);
            $src_h = imagesy($src);
        }
    }

    $bytes = 0;

    $sizes = [
        ['suffix' => '',     'max' => IMG_MAX_WIDTH],
        ['suffix' => '-md',  'max' => IMG_MOBILE_WIDTH],
    ];

    $paths = ['jpg' => '', 'webp' => '', 'webp_mobile' => '', 'jpg_mobile' => ''];

    foreach ($sizes as $size) {
        // Compute target size, preserving aspect ratio. Don't upscale.
        $target_w = min($size['max'], $src_w);
        $target_h = (int) round($src_h * ($target_w / $src_w));

        $resized = imagecreatetruecolor($target_w, $target_h);

        // Preserve PNG transparency
        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $src, 0, 0, 0, 0, $target_w, $target_h, $src_w, $src_h);

        $jpg_path  = "$out_dir_abs/$slug{$size['suffix']}.jpg";
        $webp_path = "$out_dir_abs/$slug{$size['suffix']}.webp";

        // Output JPEG (always, as fallback)
        // Convert PNG transparency to white background for JPEG
        if ($mime === 'image/png') {
            $jpeg_canvas = imagecreatetruecolor($target_w, $target_h);
            $white = imagecolorallocate($jpeg_canvas, 255, 255, 255);
            imagefilledrectangle($jpeg_canvas, 0, 0, $target_w, $target_h, $white);
            imagecopy($jpeg_canvas, $resized, 0, 0, 0, 0, $target_w, $target_h);
            imagejpeg($jpeg_canvas, $jpg_path, IMG_JPEG_QUALITY);
        } else {
            imagejpeg($resized, $jpg_path, IMG_JPEG_QUALITY);
        }

        // Output WebP
        imagewebp($resized, $webp_path, IMG_WEBP_QUALITY);


        $bytes += filesize($jpg_path) + filesize($webp_path);

        if ($size['suffix'] === '') {
            $paths['jpg']  = $jpg_path;
            $paths['webp'] = $webp_path;
        } else {
            $paths['jpg_mobile']  = $jpg_path;
            $paths['webp_mobile'] = $webp_path;
        }
    }

    $paths['bytes'] = $bytes;
    return $paths;
}

/**
 * Render a responsive <picture> tag.
 *
 * @param string $jpg_url   The canonical JPEG URL (e.g. "/assets/img/categories/savons.jpg")
 * @param string $alt       Alt text
 * @param array  $opts      Options:
 *   - lazy (bool)          Default true. False for above-the-fold LCP candidates.
 *   - fetchpriority (str)  'auto' (default), 'high', 'low'.
 *   - sizes (str)          CSS sizes attribute. Default '100vw'.
 *   - class (str)          Class for the <img> tag.
 *   - width (int)          Intrinsic width hint (prevents CLS).
 *   - height (int)         Intrinsic height hint.
 *   - decoding (str)       'auto' (default), 'async', 'sync'.
 */
function picture_tag(string $jpg_url, string $alt = '', array $opts = []): string {
    if ($jpg_url === '') return '';

    // External URLs (Unsplash hotlinks etc.) — can't generate derivatives, fall back to plain <img>
    if (preg_match('#^https?://#i', $jpg_url)) {
        return _img_tag($jpg_url, $alt, $opts);
    }

    // Strip extension and build derivative URLs
    $no_ext = preg_replace('/\.(jpe?g|png|webp|gif)$/i', '', $jpg_url);
    $base   = $no_ext;
    $jpg_full     = $base . '.jpg';
    $webp_full    = $base . '.webp';
    $webp_mobile  = $base . '-md.webp';
    $jpg_mobile   = $base . '-md.jpg';

    // Check derivatives exist on disk; if not, fall back to plain <img>
    $abs_root = dirname(__DIR__);
    $webp_exists       = file_exists($abs_root . $webp_full);
    $webp_mobile_exists = file_exists($abs_root . $webp_mobile);
    $jpg_mobile_exists = file_exists($abs_root . $jpg_mobile);

    if (!$webp_exists && !$webp_mobile_exists) {
        return _img_tag($jpg_url, $alt, $opts);
    }

    $sizes = $opts['sizes'] ?? '100vw';

    $webp_srcset = [];
    if ($webp_mobile_exists) $webp_srcset[] = e($webp_mobile) . ' ' . IMG_MOBILE_WIDTH . 'w';
    if ($webp_exists)        $webp_srcset[] = e($webp_full) . ' ' . IMG_MAX_WIDTH . 'w';

    $jpg_srcset = [];
    if ($jpg_mobile_exists) $jpg_srcset[] = e($jpg_mobile) . ' ' . IMG_MOBILE_WIDTH . 'w';
    $jpg_srcset[] = e($jpg_full) . ' ' . IMG_MAX_WIDTH . 'w';

    $sources = '';
    if ($webp_srcset) {
        $sources .= sprintf(
            '<source type="image/webp" srcset="%s" sizes="%s">',
            implode(', ', $webp_srcset),
            e($sizes)
        );
    }
    $sources .= sprintf(
        '<source type="image/jpeg" srcset="%s" sizes="%s">',
        implode(', ', $jpg_srcset),
        e($sizes)
    );

    return "<picture>{$sources}" . _img_tag($jpg_full, $alt, $opts) . '</picture>';
}

function _img_tag(string $url, string $alt, array $opts): string {
    $attrs = [];
    $attrs[] = 'src="' . e($url) . '"';
    $attrs[] = 'alt="' . e($alt) . '"';

    $lazy = $opts['lazy'] ?? true;
    $attrs[] = 'loading="' . ($lazy ? 'lazy' : 'eager') . '"';

    $decoding = $opts['decoding'] ?? 'async';
    $attrs[] = 'decoding="' . e($decoding) . '"';

    if (!empty($opts['fetchpriority'])) {
        $attrs[] = 'fetchpriority="' . e($opts['fetchpriority']) . '"';
    }
    if (!empty($opts['width']))  $attrs[] = 'width="'  . (int) $opts['width']  . '"';
    if (!empty($opts['height'])) $attrs[] = 'height="' . (int) $opts['height'] . '"';
    if (!empty($opts['class']))  $attrs[] = 'class="'  . e($opts['class'])  . '"';
    if (!empty($opts['style']))  $attrs[] = 'style="'  . e($opts['style'])  . '"';

    return '<img ' . implode(' ', $attrs) . '>';
}
