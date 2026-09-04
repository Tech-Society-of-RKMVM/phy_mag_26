<?php
/**
 * Utility Functions for Physics Magazine
 */

/**
 * Sanitize string output for safe HTML display
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a clean URL slug from title
 */
function generate_slug($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'article-' . time() : $text;
}

/**
 * Format a date nicely (e.g. "August 15, 2026")
 */
function format_article_date($dateString)
{
    if (empty($dateString))
        return '';
    $timestamp = strtotime($dateString);
    return $timestamp ? date('F j, Y', $timestamp) : $dateString;
}

/**
 * Get setting value from database
 */
function get_setting($key, $default = '')
{
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update or insert setting value
 */
function set_setting($key, $value)
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Convert YouTube / Vimeo / standard URLs to embeddable player URLs
 */
function get_embed_video_url($url)
{
    if (empty($url))
        return '';

    // YouTube standard watch URL: https://www.youtube.com/watch?v=VIDEO_ID
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1] . '?rel=0';
    }

    // Vimeo URL: https://vimeo.com/VIDEO_ID
    if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/i', $url, $matches)) {
        $videoId = end($matches);
        return 'https://player.vimeo.com/video/' . $videoId;
    }

    return $url;
}

/**
 * Handle image upload for articles, comics, or contributors
 * Returns array with ['success' => bool, 'path' => string, 'error' => string]
 */
function handle_image_upload($fileInputName, $subDir = '')
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'path' => '', 'error' => 'No file uploaded'];
    }

    $file = $_FILES[$fileInputName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => '', 'error' => 'Upload error code: ' . $file['error']];
    }

    // Validate mime type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'path' => '', 'error' => 'Invalid file format. Allowed: JPG, PNG, WEBP, GIF.'];
    }

    // Target directory
    $subDirClean = trim($subDir, '/\\');
    $relDir = 'assets/images/' . ($subDirClean ? $subDirClean . '/' : '');
    $targetDir = __DIR__ . '/../' . $relDir;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Clean original filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $rawBase = pathinfo($file['name'], PATHINFO_FILENAME);
    $cleanBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $rawBase);
    if (empty($cleanBase)) {
        $cleanBase = 'img_' . time();
    }

    $filename = $cleanBase . '.' . strtolower($ext);
    $targetPath = $targetDir . $filename;

    // If file already exists with same name, append timestamp
    if (file_exists($targetPath)) {
        $filename = $cleanBase . '_' . time() . '.' . strtolower($ext);
        $targetPath = $targetDir . $filename;
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => $relDir . $filename, 'error' => ''];
    } else {
        return ['success' => false, 'path' => '', 'error' => 'Failed to save image to ' . $relDir];
    }
}

/**
 * Get background system configuration settings
 */
function get_background_config()
{
    $enabled = (int)get_setting('bg_enabled', '1');
    $style = get_setting('bg_overlay_style', 'warm_amber');
    $brightness = (float)get_setting('bg_brightness', '0.65');
    $blur = (int)get_setting('bg_blur', '0');
    $speed = (int)get_setting('bg_transition_speed', '7');

    return [
        'enabled' => $enabled === 1,
        'overlay_style' => $style ?: 'warm_amber',
        'brightness' => $brightness > 0 ? $brightness : 0.65,
        'blur' => max(0, $blur),
        'transition_speed' => max(3, $speed)
    ];
}

/**
 * Fetch all active background images for rotation
 */
function get_active_backgrounds()
{
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT id, image_path, title FROM background_images WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If table is empty, provide default fallback
        if (empty($results)) {
            $defaultImgs = ['assets/images/1000089462.jpg', 'assets/images/dept.jpg', 'assets/images/dept2.jpg'];
            foreach ($defaultImgs as $img) {
                if (file_exists(__DIR__ . '/../' . $img)) {
                    $results[] = ['id' => 0, 'image_path' => $img, 'title' => 'Default Department View'];
                }
            }
        }
        return $results;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Compute custom CSS inline style for homepage <main> articles container
 */
function get_index_main_style()
{
    $type = get_setting('index_main_bg_type', 'transparent');
    if ($type === 'transparent') {
        return '';
    }

    $styles = [];
    $radius = (int)get_setting('index_main_bg_radius', '16');
    $padding = (int)get_setting('index_main_bg_padding', '2');
    $hasBorder = get_setting('index_main_bg_border', '1') === '1';
    $hasShadow = get_setting('index_main_bg_shadow', '1') === '1';

    $styles[] = "border-radius: {$radius}px";
    $styles[] = "padding: {$padding}rem";

    if ($hasShadow) {
        $styles[] = "box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04)";
    }

    if ($type === 'frosted_glass') {
        $color = get_setting('index_main_bg_color', 'rgba(255, 255, 255, 0.85)');
        $blur = (int)get_setting('index_main_bg_blur', '14');
        $styles[] = "background: {$color}";
        $styles[] = "backdrop-filter: blur({$blur}px)";
        $styles[] = "-webkit-backdrop-filter: blur({$blur}px)";
        if ($hasBorder) {
            $styles[] = "border: 1px solid rgba(255, 255, 255, 0.65)";
        }
    } elseif ($type === 'custom_image') {
        $img = get_setting('index_main_bg_image', '');
        $overlayColor = get_setting('index_main_bg_color', 'rgba(255, 248, 238, 0.85)');
        if (!empty($img)) {
            $styles[] = "background-image: linear-gradient({$overlayColor}, {$overlayColor}), url('{$img}')";
            $styles[] = "background-size: cover";
            $styles[] = "background-position: center";
        } else {
            $styles[] = "background: {$overlayColor}";
        }
        $blur = (int)get_setting('index_main_bg_blur', '0');
        if ($blur > 0) {
            $styles[] = "backdrop-filter: blur({$blur}px)";
            $styles[] = "-webkit-backdrop-filter: blur({$blur}px)";
        }
        if ($hasBorder) {
            $styles[] = "border: 1px solid rgba(255, 255, 255, 0.5)";
        }
    } elseif ($type === 'solid_color') {
        $color = get_setting('index_main_bg_color', '#ffffff');
        $styles[] = "background: {$color}";
        if ($hasBorder) {
            $styles[] = "border: 1px solid rgba(0, 0, 0, 0.08)";
        }
    } elseif ($type === 'chalkboard_dark') {
        $styles[] = "background: rgba(15, 23, 42, 0.90)";
        $styles[] = "backdrop-filter: blur(14px)";
        $styles[] = "-webkit-backdrop-filter: blur(14px)";
        if ($hasBorder) {
            $styles[] = "border: 1px solid rgba(255, 255, 255, 0.15)";
        }
        if ($hasShadow) {
            $styles[] = "box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25)";
        }
    }

    return implode('; ', $styles) . ';';
}

