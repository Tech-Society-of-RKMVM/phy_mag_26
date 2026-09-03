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
 * Handle image upload for articles - saves directly to assets/images/
 * Returns array with ['success' => bool, 'path' => string, 'error' => string]
 */
function handle_image_upload($fileInputName)
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

    // Target assets/images/ directly
    $assetsDir = __DIR__ . '/../assets/images/';
    if (!is_dir($assetsDir)) {
        mkdir($assetsDir, 0777, true);
    }

    // Clean original filename or unique name
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $rawBase = pathinfo($file['name'], PATHINFO_FILENAME);
    $cleanBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $rawBase);
    if (empty($cleanBase)) {
        $cleanBase = 'img_' . time();
    }
    
    $filename = $cleanBase . '.' . strtolower($ext);
    $targetPath = $assetsDir . $filename;
    
    // If file already exists with same name, append timestamp
    if (file_exists($targetPath)) {
        $filename = $cleanBase . '_' . time() . '.' . strtolower($ext);
        $targetPath = $assetsDir . $filename;
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => 'assets/images/' . $filename, 'error' => ''];
    } else {
        return ['success' => false, 'path' => '', 'error' => 'Failed to save image to assets/images/'];
    }
}
