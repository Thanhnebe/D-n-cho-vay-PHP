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
    $currentUserId = $_SESSION['user_id'] ?? null;

    if (!$currentUserId) {
        throw new Exception('Chưa đăng nhập');
    }

    // Lấy thông tin đơn vay
    $application = $db->fetchOne("
        SELECT la.*, c.name as customer_name, c.phone as customer_phone
        FROM loan_applications la
        LEFT JOIN customers c ON la.customer_id = c.id
        WHERE la.id = ?
    ", [$applicationId]);

    if (!$application) {
        throw new Exception('Không tìm thấy đơn vay');
    }

    // Lấy thông tin user hiện tại
    $currentUser = $db->fetchOne("
        SELECT u.*, lau.role_id, lar.name as role_name, lar.min_amount, lar.max_amount
        FROM users u
        LEFT JOIN loan_approval_users lau ON u.id = lau.user_id
        LEFT JOIN loan_approval_roles lar ON lau.role_id = lar.id
        WHERE u.id = ? AND lau.status = 'active'
    ", [$currentUserId]);

    if (!$currentUser) {
        throw new Exception('Không tìm thấy thông tin quyền của user');
    }

    $loanAmount = floatval($application['loan_amount']);
    $userRoleId = $currentUser['role_id'];
    $userMinAmount = floatval($currentUser['min_amount']);
    $userMaxAmount = floatval($currentUser['max_amount']);

    // Debug thông tin
    error_log("Debug approval permissions:");
    error_log("Loan amount: " . $loanAmount);
    error_log("User role ID: " . $userRoleId);
    error_log("User min amount: " . $userMinAmount);
    error_log("User max amount: " . $userMaxAmount);
    error_log("User role name: " . $currentUser['role_name']);

    // Kiểm tra quyền phê duyệt
    $canApprove = false;
    $approvalLevel = 0;
    $approvalRole = '';

    if ($loanAmount >= $userMinAmount && $loanAmount <= $userMaxAmount) {
        $canApprove = true;
        $approvalLevel = $currentUser['role_id'];
        $approvalRole = $currentUser['role_name'];
        error_log("User CAN approve this loan");
    } else {
        error_log("User CANNOT approve this loan");
        error_log("Condition check: " . ($loanAmount >= $userMinAmount ? "true" : "false") . " AND " . ($loanAmount <= $userMaxAmount ? "true" : "false"));
    }

    // Lấy lịch sử phê duyệt
    $approvalHistory = $db->fetchAll("
        SELECT la.*, u.name as approver_name, lar.name as role_name
        FROM loan_approvals la
        LEFT JOIN users u ON la.approver_id = u.id
        LEFT JOIN loan_approval_roles lar ON la.approval_level = lar.id
        WHERE la.application_id = ?
        ORDER BY la.approval_date ASC
    ", [$applicationId]);

    // Kiểm tra trạng thái hiện tại
    $currentStatus = $application['status'];
    $currentApprovalLevel = $application['current_approval_level'];
    $totalApprovalLevels = $application['total_approval_levels'];

    // Xác định cấp phê duyệt tiếp theo dựa trên số tiền
    $nextApprovalLevel = 0;
    if ($loanAmount <= 50000000) {
        $nextApprovalLevel = 1; // Nhân viên
    } elseif ($loanAmount <= 200000000) {
        $nextApprovalLevel = 2; // Trưởng phòng
    } elseif ($loanAmount <= 1000000000) {
        $nextApprovalLevel = 3; // Giám đốc
    } else {
        $nextApprovalLevel = 4; // Tổng giám đốc
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'can_approve' => $canApprove,
            'approval_level' => $approvalLevel,
            'approval_role' => $approvalRole,
            'current_status' => $currentStatus,
            'current_approval_level' => $currentApprovalLevel,
            'next_approval_level' => $nextApprovalLevel,
            'total_approval_levels' => $totalApprovalLevels,
            'loan_amount' => $loanAmount,
            'user_role' => $currentUser['role_name'],
            'approval_history' => $approvalHistory,
            'application' => $application
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
