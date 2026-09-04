<?php
/**
 * AJAX API for Background Management
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = get_db_connection();
$action = $_GET['action'] ?? '';

// Reorder background images
if ($action === 'reorder') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (empty($data['order']) || !is_array($data['order'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid order data']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE background_images SET sort_order = ? WHERE id = ?");
        foreach ($data['order'] as $index => $id) {
            $stmt->execute([$index + 1, (int)$id]);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Toggle background active state
if ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $active = (int)($_POST['active'] ?? 0);

    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid background ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE background_images SET is_active = ? WHERE id = ?");
        $stmt->execute([$active ? 1 : 0, $id]);
        echo json_encode(['success' => true, 'is_active' => $active ? 1 : 0]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Delete background
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Invalid background ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT image_path FROM background_images WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $del = $pdo->prepare("DELETE FROM background_images WHERE id = ?");
            $del->execute([$id]);

            // If file was uploaded inside assets/images/backgrounds/, remove it
            $path = $row['image_path'];
            if (strpos($path, 'assets/images/backgrounds/') === 0 && file_exists(__DIR__ . '/../' . $path)) {
                @unlink(__DIR__ . '/../' . $path);
            }
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
