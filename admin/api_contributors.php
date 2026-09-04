<?php
/**
 * Contributors API Endpoint
 * Handles reordering, deletions, and updates via AJAX
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

// 1. Reorder Contributors
if ($action === 'reorder') {
    $input = json_decode(file_get_contents('php://input'), true);
    $contributorIds = $input['order'] ?? [];

    if (!is_array($contributorIds)) {
        echo json_encode(['success' => false, 'error' => 'Invalid order payload']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE contributors SET sort_order = ? WHERE id = ?");
        foreach ($contributorIds as $rank => $id) {
            $stmt->execute([$rank + 1, (int)$id]);
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Contributors order updated successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 2. Delete Contributor
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid contributor ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM contributors WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Contributor removed successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
