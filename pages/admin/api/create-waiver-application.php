<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Kiểm tra quyền truy cập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$currentUser = $_SESSION['user_id'] ?? 0;
$userRole = $_SESSION['user_role'] ?? '';
$userDepartment = $_SESSION['department_id'] ?? 0;

// Xử lý các action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        handleCreateWaiverApplication();
        break;
    case 'calculate':
        handleCalculateWaiver();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function handleCreateWaiverApplication()
{
    global $db, $currentUser, $userDepartment;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        // Validate input
        $contractId = intval($_POST['contract_id'] ?? 0);
        $customerId = intval($_POST['customer_id'] ?? 0);
        $departmentId = intval($_POST['department_id'] ?? 0);
        $reason = sanitize_input($_POST['reason'] ?? '');
        $detailedReason = sanitize_input($_POST['detailed_reason'] ?? '');
        $waiverType = sanitize_input($_POST['waiver_type'] ?? '');
        $documentType = sanitize_input($_POST['document_type'] ?? '');
        $dataDate = $_POST['data_date'] ?? date('Y-m-d');
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d', strtotime('+1 day'));
        $expectedCollectionAmount = floatval($_POST['expected_collection_amount'] ?? 0);

        if (!$contractId || !$customerId || !$departmentId || !$reason || !$waiverType) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Lấy thông tin hợp đồng
        $contract = $db->fetchOne("
            SELECT c.*, cu.name as customer_name 
            FROM contracts c 
            JOIN customers cu ON c.customer_id = cu.id 
            WHERE c.id = ?
        ", [$contractId]);

        if (!$contract) {
            throw new Exception('Hợp đồng không tồn tại');
        }

        // Kiểm tra xem đã có đơn miễn giảm cho hợp đồng này chưa
        $existingApplication = $db->fetchOne("
            SELECT id FROM interest_waiver_applications 
            WHERE contract_id = ? AND status IN ('pending', 'approved')
        ", [$contractId]);

        if ($existingApplication) {
            throw new Exception('Đã có đơn miễn giảm cho hợp đồng này');
        }

        // Tính toán các giá trị miễn giảm
        $waiverCalculation = calculateWaiverAmounts($contract, $waiverType, $expectedCollectionAmount);

        // Tạo mã đơn miễn giảm
        $applicationCode = 'MGP' . date('YmdHis') . rand(1000, 9999);

        // Tạo đơn miễn giảm
        $applicationData = [
            'application_code' => $applicationCode,
            'contract_id' => $contractId,
            'customer_id' => $customerId,
            'waiver_amount' => $waiverCalculation['total_waiver_amount'],
            'waiver_percentage' => $waiverCalculation['waiver_percentage'],
            'waiver_type' => $waiverType,
            'original_amount' => $contract['remaining_balance'],
            'remaining_amount_after_waiver' => $contract['remaining_balance'] - $waiverCalculation['total_waiver_amount'],
            'reason' => $reason,
            'detailed_reason' => $detailedReason,
            'exception_notes' => '',
            'status' => 'pending',
            'current_approval_level' => 1,
            'total_approval_levels' => 3,
            'created_by' => $currentUser,
            'department_id' => $departmentId,
            'effective_expiry_date' => $effectiveDate,
            'expected_collection_amount' => $expectedCollectionAmount,
            'wallet_amount' => 0,
            'highest_approval_level' => 1,
            'data_date' => $dataDate,
            'document_type' => $documentType
        ];

        $applicationId = $db->insert('interest_waiver_applications', $applicationData);

        if ($applicationId) {
            // Tạo thông báo cho cấp phê duyệt đầu tiên
            $notificationMessage = "Đơn miễn giảm {$waiverType} cho hợp đồng #{$contractId} cần phê duyệt";

            // Gọi API tạo thông báo
            $notificationData = [
                'application_id' => $applicationId,
                'notification_type' => 'approval_required',
                'message' => $notificationMessage,
                'recipient_role' => 'department_manager',
                'recipient_department' => $departmentId
            ];

            // Tạo thông báo cho trưởng phòng
            $approvers = $db->fetchAll("
                SELECT u.id as user_id, u.name, u.role
                FROM users u
                JOIN user_departments ud ON u.id = ud.user_id
                WHERE ud.department_id = ? AND ud.role_in_department = 'manager'
                AND u.status = 'active'
            ", [$departmentId]);

            foreach ($approvers as $approver) {
                $notificationData = [
                    'user_id' => $approver['user_id'],
                    'title' => 'Yêu cầu phê duyệt đơn miễn giảm',
                    'message' => $notificationMessage,
                    'type' => 'approval_required',
                    'icon' => 'exclamation-triangle',
                    'related_id' => $applicationId,
                    'related_type' => 'waiver_application',
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $db->insert('notifications', $notificationData);
            }

            // Log activity
            $activityData = [
                'user_id' => $currentUser,
                'action' => 'create_waiver_application',
                'table_name' => 'interest_waiver_applications',
                'record_id' => $applicationId,
                'description' => "Tạo đơn miễn giảm {$applicationCode}",
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->insert('activity_logs', $activityData);

            echo json_encode([
                'success' => true,
                'message' => 'Tạo đơn miễn giảm thành công',
                'application_id' => $applicationId,
                'application_code' => $applicationCode,
                'waiver_amount' => $waiverCalculation['total_waiver_amount'],
                'waiver_percentage' => $waiverCalculation['waiver_percentage']
            ]);
        } else {
            throw new Exception('Không thể tạo đơn miễn giảm');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function handleCalculateWaiver()
{
    global $db;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        $contractId = intval($_POST['contract_id'] ?? 0);
        $waiverType = sanitize_input($_POST['waiver_type'] ?? '');
        $expectedCollectionAmount = floatval($_POST['expected_collection_amount'] ?? 0);

        if (!$contractId || !$waiverType) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Lấy thông tin hợp đồng
        $contract = $db->fetchOne("SELECT * FROM contracts WHERE id = ?", [$contractId]);

        if (!$contract) {
            throw new Exception('Hợp đồng không tồn tại');
        }

        // Tính toán miễn giảm
        $calculation = calculateWaiverAmounts($contract, $waiverType, $expectedCollectionAmount);

        echo json_encode([
            'success' => true,
            'calculation' => $calculation
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function calculateWaiverAmounts($contract, $waiverType, $expectedCollectionAmount)
{
    $remainingBalance = $contract['remaining_balance'];
    $totalObligation = $remainingBalance;

    // Tính toán theo loại miễn giảm
    switch ($waiverType) {
        case 'MGLP toàn bộ HĐ':
            $waiverAmount = $remainingBalance;
            $waiverPercentage = 100;
            break;

        case 'MGLP một phần HĐ':
            $waiverAmount = $remainingBalance * 0.5; // Miễn 50%
            $waiverPercentage = 50;
            break;

        case 'MG lãi suất':
            // Giả sử lãi chiếm 30% dư nợ
            $interestAmount = $remainingBalance * 0.3;
            $waiverAmount = $interestAmount;
            $waiverPercentage = round(($waiverAmount / $totalObligation) * 100, 2);
            break;

        case 'MG phí dịch vụ':
            // Giả sử phí dịch vụ chiếm 20% dư nợ
            $serviceFeeAmount = $remainingBalance * 0.2;
            $waiverAmount = $serviceFeeAmount;
            $waiverPercentage = round(($waiverAmount / $totalObligation) * 100, 2);
            break;

        case 'MG phí phạt':
            // Giả sử phí phạt chiếm 15% dư nợ
            $penaltyFeeAmount = $remainingBalance * 0.15;
            $waiverAmount = $penaltyFeeAmount;
            $waiverPercentage = round(($waiverAmount / $totalObligation) * 100, 2);
            break;

        case 'MG tùy chỉnh':
            // Tính toán dựa trên số tiền dự thu
            if ($expectedCollectionAmount > 0) {
                $waiverAmount = $remainingBalance - $expectedCollectionAmount;
                $waiverPercentage = round(($waiverAmount / $totalObligation) * 100, 2);
            } else {
                $waiverAmount = $remainingBalance * 0.3; // Mặc định 30%
                $waiverPercentage = 30;
            }
            break;

        default:
            $waiverAmount = 0;
            $waiverPercentage = 0;
    }

    // Đảm bảo không miễn giảm quá số tiền còn nợ
    $waiverAmount = min($waiverAmount, $remainingBalance);

    return [
        'total_waiver_amount' => $waiverAmount,
        'waiver_percentage' => $waiverPercentage,
        'remaining_balance' => $remainingBalance,
        'expected_collection' => $expectedCollectionAmount,
        'remaining_after_waiver' => $remainingBalance - $waiverAmount
    ];
}

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
