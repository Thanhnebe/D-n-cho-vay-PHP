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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lender_id = intval($_POST['lender_id'] ?? 0);

    if (!$lender_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }

    try {
        // Lấy trạng thái hiện tại
        $lender = $db->fetchOne("SELECT verified FROM lenders WHERE id = ?", [$lender_id]);

        if (!$lender) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy người cho vay']);
            exit;
        }

        // Toggle trạng thái xác thực
        $new_verified = $lender['verified'] ? 0 : 1;

        $affected = $db->update('lenders', ['verified' => $new_verified], 'id = ?', [$lender_id]);

        if ($affected > 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $new_verified ? 'Đã xác thực người cho vay' : 'Đã bỏ xác thực người cho vay',
                'verified' => $new_verified
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method không được hỗ trợ']);
}
