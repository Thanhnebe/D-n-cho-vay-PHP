<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Suppress warnings and errors to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

$db = getDB();

// Set JSON header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    try {
        // Validate required fields
        $applicationId = intval($_POST['application_id']);
        $departmentId = intval($_POST['department_id']);
        $waiverType = sanitize_input($_POST['waiver_type']);
        $waiverPercentage = floatval($_POST['waiver_percentage']);
        $waiverAmount = floatval(str_replace(',', '', $_POST['waiver_amount']));
        $effectiveExpiryDate = $_POST['effective_expiry_date'];
        $reason = sanitize_input($_POST['reason']);
        $detailedReason = sanitize_input($_POST['detailed_reason'] ?? '');
        $exceptionNotes = sanitize_input($_POST['exception_notes'] ?? '');

        if (!$applicationId || !$departmentId || !$waiverType || !$waiverAmount || !$effectiveExpiryDate || !$reason) {
            throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
        }

        // Get loan application details
        $application = $db->fetchOne("SELECT * FROM loan_applications WHERE id = ?", [$applicationId]);

        if (!$application) {
            throw new Exception('Không tìm thấy đơn vay');
        }

        if (!in_array($application['status'], ['approved', 'disbursed'])) {
            throw new Exception('Chỉ có thể tạo đơn miễn giảm cho đơn vay đã được duyệt');
        }

        // Generate application code
        $applicationCode = 'MG' . date('Ymd') . rand(1000, 9999);

        // Calculate remaining amount after waiver
        $originalAmount = $application['approved_amount'] ?: $application['loan_amount'];
        $remainingAmountAfterWaiver = $originalAmount - $waiverAmount;

        // Insert waiver application
        $waiverData = [
            'application_code' => $applicationCode,
            'contract_id' => null, // Để null vì không có contract
            'application_id' => $applicationId, // Sử dụng application_id mới
            'customer_id' => $application['customer_id'],
            'department_id' => $departmentId,
            'waiver_type' => $waiverType,
            'waiver_percentage' => $waiverPercentage,
            'waiver_amount' => $waiverAmount,
            'original_amount' => $originalAmount,
            'remaining_amount_after_waiver' => $remainingAmountAfterWaiver,
            'reason' => $reason,
            'detailed_reason' => $detailedReason,
            'exception_notes' => $exceptionNotes,
            'status' => 'pending',
            'current_approval_level' => 1,
            'total_approval_levels' => 3,
            'effective_expiry_date' => $effectiveExpiryDate,
            'created_by' => $_SESSION['user_id'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $waiverId = $db->insert('interest_waiver_applications', $waiverData);

        if ($waiverId) {
            echo json_encode([
                'success' => true,
                'message' => '✅ Tạo đơn miễn giảm thành công!',
                'waiver_id' => $waiverId,
                'application_code' => $applicationCode,
                'details' => [
                    'Mã đơn miễn giảm' => $applicationCode,
                    'Khách hàng' => $application['customer_name'],
                    'Số tiền gốc' => number_format($originalAmount, 0, ',', '.') . ' VND',
                    'Số tiền miễn giảm' => number_format($waiverAmount, 0, ',', '.') . ' VND',
                    'Loại miễn giảm' => $waiverType,
                    'Phần trăm' => $waiverPercentage . '%'
                ]
            ]);
        } else {
            throw new Exception('Có lỗi xảy ra khi tạo đơn miễn giảm');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'approve') {
    try {
        $waiverId = intval($_POST['id'] ?? $_GET['id']);
        $approvedAmount = floatval(str_replace(',', '', $_POST['approved_amount'] ?? 0));
        $approvedPercentage = floatval($_POST['approved_percentage'] ?? 0);
        $comments = sanitize_input($_POST['comments'] ?? '');
        $approvalLevel = intval($_POST['approval_level'] ?? 1);

        // Get waiver application
        $waiver = $db->fetchOne("SELECT * FROM interest_waiver_applications WHERE id = ?", [$waiverId]);

        if (!$waiver) {
            throw new Exception('Không tìm thấy đơn miễn giảm');
        }

        if ($waiver['status'] !== 'pending') {
            throw new Exception('Chỉ có thể phê duyệt đơn ở trạng thái chờ duyệt');
        }

        // Update waiver application
        $updateData = [
            'status' => 'approved',
            'approved_amount' => $approvedAmount,
            'approved_percentage' => $approvedPercentage,
            'final_decision' => 'approved',
            'decision_date' => date('Y-m-d'),
            'decision_notes' => $comments,
            'current_approval_level' => $approvalLevel,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $db->update('interest_waiver_applications', $updateData, 'id = :id', ['id' => $waiverId]);

        if ($result) {
            // Log approval
            $approvalData = [
                'application_id' => $waiverId,
                'approver_id' => $_SESSION['user_id'] ?? 1,
                'approval_level' => $approvalLevel,
                'action' => 'approve',
                'approved_amount' => $approvedAmount,
                'comments' => $comments,
                'approved_at' => date('Y-m-d H:i:s')
            ];

            $db->insert('interest_waiver_approvals', $approvalData);

            echo json_encode([
                'success' => true,
                'message' => 'Phê duyệt đơn miễn giảm thành công!'
            ]);
        } else {
            throw new Exception('Có lỗi xảy ra khi phê duyệt');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'reject') {
    try {
        $waiverId = intval($_POST['id'] ?? $_GET['id']);
        $reason = sanitize_input($_POST['reason'] ?? $_GET['reason'] ?? '');
        $comments = sanitize_input($_POST['comments'] ?? '');
        $rejectionLevel = intval($_POST['rejection_level'] ?? 1);

        if (empty($reason)) {
            throw new Exception('Vui lòng nhập lý do từ chối');
        }

        // Get waiver application
        $waiver = $db->fetchOne("SELECT * FROM interest_waiver_applications WHERE id = ?", [$waiverId]);

        if (!$waiver) {
            throw new Exception('Không tìm thấy đơn miễn giảm');
        }

        if ($waiver['status'] !== 'pending') {
            throw new Exception('Chỉ có thể từ chối đơn ở trạng thái chờ duyệt');
        }

        // Update waiver application
        $updateData = [
            'status' => 'rejected',
            'final_decision' => 'rejected',
            'decision_date' => date('Y-m-d'),
            'decision_notes' => $comments ?: $reason,
            'current_approval_level' => $rejectionLevel,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $db->update('interest_waiver_applications', $updateData, 'id = :id', ['id' => $waiverId]);

        if ($result) {
            // Log rejection
            $rejectionData = [
                'application_id' => $waiverId,
                'approver_id' => $_SESSION['user_id'] ?? 1,
                'approval_level' => $rejectionLevel,
                'action' => 'reject',
                'comments' => $comments ?: $reason,
                'approved_at' => date('Y-m-d H:i:s')
            ];

            $db->insert('interest_waiver_approvals', $rejectionData);

            echo json_encode([
                'success' => true,
                'message' => 'Từ chối đơn miễn giảm thành công!'
            ]);
        } else {
            throw new Exception('Có lỗi xảy ra khi từ chối');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'get_detail') {
    try {
        $waiverId = intval($_GET['id']);

        // Get waiver application details with loan application info
        $waiver = $db->fetchOne("
            SELECT 
                iwa.*,
                la.application_code AS loan_application_code,
                la.customer_name,
                la.customer_phone_main AS customer_phone,
                d.name AS department_name,
                u.name AS created_by_name
            FROM interest_waiver_applications iwa
            LEFT JOIN loan_applications la ON iwa.application_id = la.id
            LEFT JOIN departments d ON iwa.department_id = d.id
            LEFT JOIN users u ON iwa.created_by = u.id
            WHERE iwa.id = ?
        ", [$waiverId]);

        if (!$waiver) {
            throw new Exception('Không tìm thấy đơn miễn giảm');
        }

        echo json_encode([
            'success' => true,
            'application' => $waiver
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'update') {
    try {
        $waiverId = intval($_POST['id']);
        $waiverType = sanitize_input($_POST['waiver_type']);
        $waiverPercentage = floatval($_POST['waiver_percentage']);
        $waiverAmount = floatval(str_replace(',', '', $_POST['waiver_amount']));
        $effectiveExpiryDate = $_POST['effective_expiry_date'];
        $reason = sanitize_input($_POST['reason']);
        $detailedReason = sanitize_input($_POST['detailed_reason'] ?? '');
        $exceptionNotes = sanitize_input($_POST['exception_notes'] ?? '');

        if (!$waiverId || !$waiverType || !$waiverAmount || !$effectiveExpiryDate || !$reason) {
            throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
        }

        // Get waiver application
        $waiver = $db->fetchOne("SELECT * FROM interest_waiver_applications WHERE id = ?", [$waiverId]);

        if (!$waiver) {
            throw new Exception('Không tìm thấy đơn miễn giảm');
        }

        // Only allow editing if status is pending
        if ($waiver['status'] !== 'pending') {
            throw new Exception('Chỉ có thể chỉnh sửa đơn ở trạng thái chờ duyệt');
        }

        // Calculate remaining amount after waiver
        $originalAmount = $waiver['original_amount'];
        $remainingAmountAfterWaiver = $originalAmount - $waiverAmount;

        // Update waiver application
        $updateData = [
            'waiver_type' => $waiverType,
            'waiver_percentage' => $waiverPercentage,
            'waiver_amount' => $waiverAmount,
            'remaining_amount_after_waiver' => $remainingAmountAfterWaiver,
            'reason' => $reason,
            'detailed_reason' => $detailedReason,
            'exception_notes' => $exceptionNotes,
            'effective_expiry_date' => $effectiveExpiryDate,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $db->update('interest_waiver_applications', $updateData, 'id = :id', ['id' => $waiverId]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật đơn miễn giảm thành công!'
            ]);
        } else {
            throw new Exception('Có lỗi xảy ra khi cập nhật');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Action không hợp lệ'
    ]);
}
