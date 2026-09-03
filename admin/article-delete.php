<?php
/**
 * Safe Article Delete Action
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrf)) {
        $_SESSION['flash_msg'] = 'Error: Invalid security token.';
        header('Location: articles.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash_msg'] = "Article #{$id} was permanently deleted.";
    }
}

header('Location: articles.php');
exit;
