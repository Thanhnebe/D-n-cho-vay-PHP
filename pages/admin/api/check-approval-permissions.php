<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    if (!isset($_GET['application_id'])) {
        throw new Exception('Thiếu ID đơn vay');
    }
    
    $applicationId = intval($_GET['application_id']);
    $userId = $_SESSION['user_id'] ?? 0;
    
    // Lấy thông tin đơn vay
    $application = $db->fetchOne("
        SELECT status, current_approval_level, total_approval_levels
        FROM loan_applications 
        WHERE id = ?
    ", [$applicationId]);
    
    if (!$application) {
        throw new Exception('Không tìm thấy đơn vay');
    }
    
    // Kiểm tra quyền phê duyệt
    $canApprove = false;
    
    // Logic kiểm tra quyền (có thể mở rộng theo yêu cầu)
    if ($application['status'] === 'pending') {
        // Kiểm tra role của user
        $user = $db->fetchOne("
            SELECT role, department_id 
            FROM users 
            WHERE id = ?
        ", [$userId]);
        
        if ($user) {
            // Admin có thể phê duyệt tất cả
            if ($user['role'] === 'admin') {
                $canApprove = true;
            }
            // Manager có thể phê duyệt đơn vay trong phòng ban của mình
            elseif ($user['role'] === 'manager') {
                $applicationDept = $db->fetchOne("
                    SELECT department_id 
                    FROM loan_applications 
                    WHERE id = ?
                ", [$applicationId]);
                
                if ($applicationDept && $applicationDept['department_id'] == $user['department_id']) {
                    $canApprove = true;
                }
            }
            // Staff chỉ có thể phê duyệt đơn vay mình tạo
            elseif ($user['role'] === 'staff') {
                $applicationCreator = $db->fetchOne("
                    SELECT created_by 
                    FROM loan_applications 
                    WHERE id = ?
                ", [$applicationId]);
                
                if ($applicationCreator && $applicationCreator['created_by'] == $userId) {
                    $canApprove = true;
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'can_approve' => $canApprove,
        'application_status' => $application['status']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 