<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $lender_id = intval($_GET['id'] ?? 0);
    
    if (!$lender_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }

    try {
        // Lấy thông tin người cho vay
        $lender = $db->fetchOne("
            SELECT l.*, 
                   COUNT(ec.id) as contract_count,
                   SUM(ec.loan_amount) as total_loaned
            FROM lenders l 
            LEFT JOIN electronic_contracts ec ON l.id = ec.lender_id
            WHERE l.id = ?
            GROUP BY l.id
        ", [$lender_id]);

        if (!$lender) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người cho vay']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $lender]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method không được hỗ trợ']);
}
?> 