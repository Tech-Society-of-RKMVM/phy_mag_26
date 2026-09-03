<?php
/**
 * API: Reorder and Archive Articles via AJAX
 * Updates sort_order and publication/archive status
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_admin_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!isset($data['articles']) || !is_array($data['articles'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload format.']);
    exit;
}

$pdo = get_db_connection();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE articles 
        SET sort_order = ?, status = ? 
        WHERE id = ?
    ");

    foreach ($data['articles'] as $art) {
        $id = (int)($art['id'] ?? 0);
        $sortOrder = (int)($art['sort_order'] ?? 0);
        $isArchived = !empty($art['is_archived']);
        $status = $isArchived ? 'draft' : 'published';

        if ($id > 0) {
            $stmt->execute([$sortOrder, $status, $id]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Article order and status updated successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
