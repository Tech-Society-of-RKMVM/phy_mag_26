<?php
/**
 * Comic Editor API Endpoint
 * Handles reordering, deletions, metadata, and settings via AJAX
 */

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. Reorder Panels
if ($action === 'reorder') {
    $input = json_decode(file_get_contents('php://input'), true);
    $panelIds = $input['order'] ?? [];

    if (!is_array($panelIds)) {
        echo json_encode(['success' => false, 'error' => 'Invalid order payload']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE comic_panels SET sort_order = ? WHERE id = ?");
        foreach ($panelIds as $rank => $id) {
            $stmt->execute([$rank + 1, (int)$id]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Comic panel order updated successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 2. Delete Panel
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid panel ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM comic_panels WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Panel removed successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 3. Update Panel Details
if ($action === 'update_panel') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');

    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid panel ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE comic_panels SET title = ? WHERE id = ?");
        $stmt->execute([$title, $id]);
        echo json_encode(['success' => true, 'message' => 'Panel updated successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
