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
    case 'create_notification':
        handleCreateNotification();
        break;
    case 'get_notifications':
        handleGetNotifications();
        break;
    case 'mark_read':
        handleMarkAsRead();
        break;
    case 'mark_all_read':
        handleMarkAllAsRead();
        break;
    case 'approval_workflow':
        handleApprovalWorkflow();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function handleCreateNotification()
{
    global $db, $currentUser;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        $applicationId = intval($_POST['application_id'] ?? 0);
        $notificationType = sanitize_input($_POST['notification_type'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');
        $recipientRole = sanitize_input($_POST['recipient_role'] ?? '');
        $recipientDepartment = intval($_POST['recipient_department'] ?? 0);

        if (!$applicationId || !$notificationType || !$message) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Lấy thông tin đơn miễn giảm
        $application = $db->fetchOne("
            SELECT iwa.*, c.contract_code, cu.name as customer_name
            FROM interest_waiver_applications iwa
            JOIN contracts c ON iwa.contract_id = c.id
            JOIN customers cu ON iwa.customer_id = cu.id
            WHERE iwa.id = ?
        ", [$applicationId]);

        if (!$application) {
            throw new Exception('Đơn miễn giảm không tồn tại');
        }

        // Xác định người nhận thông báo
        $recipients = getNotificationRecipients($recipientRole, $recipientDepartment, $application);

        foreach ($recipients as $recipient) {
            $notificationData = [
                'user_id' => $recipient['user_id'],
                'title' => getNotificationTitle($notificationType),
                'message' => $message,
                'type' => $notificationType,
                'icon' => getNotificationIcon($notificationType),
                'related_id' => $applicationId,
                'related_type' => 'waiver_application',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $db->insert('notifications', $notificationData);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Đã tạo thông báo thành công',
            'recipients_count' => count($recipients)
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function handleGetNotifications()
{
    global $db, $currentUser;

    $notifications = $db->fetchAll("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 50
    ", [$currentUser]);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
}

function handleMarkAsRead()
{
    global $db, $currentUser;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        $notificationId = intval($_POST['notification_id'] ?? 0);

        if (!$notificationId) {
            throw new Exception('Thiếu ID thông báo');
        }

        $result = $db->update(
            'notifications',
            ['is_read' => 1],
            'id = ? AND user_id = ?',
            [$notificationId, $currentUser]
        );

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã đánh dấu đã đọc']);
        } else {
            throw new Exception('Không thể cập nhật thông báo');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function handleApprovalWorkflow()
{
    global $db, $currentUser;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        $applicationId = intval($_POST['application_id'] ?? 0);
        $action = sanitize_input($_POST['action'] ?? '');
        $comments = sanitize_input($_POST['comments'] ?? '');

        if (!$applicationId || !$action) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Lấy thông tin đơn miễn giảm
        $application = $db->fetchOne("
            SELECT * FROM interest_waiver_applications WHERE id = ?
        ", [$applicationId]);

        if (!$application) {
            throw new Exception('Đơn miễn giảm không tồn tại');
        }

        // Xử lý phê duyệt/từ chối
        if ($action === 'approve') {
            $nextLevel = $application['current_approval_level'] + 1;
            $status = ($nextLevel >= $application['total_approval_levels']) ? 'approved' : 'pending';

            $updateData = [
                'current_approval_level' => $nextLevel,
                'status' => $status,
                'highest_approval_level' => max($application['highest_approval_level'], $nextLevel)
            ];

            if ($status === 'approved') {
                $updateData['final_decision'] = 'approved';
                $updateData['decision_date'] = date('Y-m-d H:i:s');
                $updateData['decision_notes'] = $comments;
            }

            $db->update('interest_waiver_applications', $updateData, 'id = ?', [$applicationId]);

            // Tạo thông báo cho cấp tiếp theo hoặc thông báo hoàn thành
            if ($status === 'approved') {
                createApprovalCompleteNotification($application);
            } else {
                createNextLevelNotification($application, $nextLevel);
            }
        } elseif ($action === 'reject') {
            $db->update('interest_waiver_applications', [
                'status' => 'rejected',
                'final_decision' => 'rejected',
                'decision_date' => date('Y-m-d H:i:s'),
                'decision_notes' => $comments
            ], 'id = ?', [$applicationId]);

            createRejectionNotification($application);
        }

        // Ghi log phê duyệt
        $approvalData = [
            'application_id' => $applicationId,
            'approver_id' => $currentUser,
            'approval_level' => $application['current_approval_level'],
            'action' => $action,
            'comments' => $comments,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->insert('interest_waiver_approvals', $approvalData);

        echo json_encode([
            'success' => true,
            'message' => 'Xử lý phê duyệt thành công'
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function handleMarkAllAsRead()
{
    global $db, $currentUser;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    try {
        $result = $db->update(
            'notifications',
            ['is_read' => 1],
            'user_id = ? AND is_read = 0',
            [$currentUser]
        );

        echo json_encode(['success' => true, 'message' => 'Đã đánh dấu tất cả thông báo đã đọc']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getNotificationRecipients($role, $department, $application)
{
    global $db;

    $recipients = [];

    switch ($role) {
        case 'department_manager':
            // Trưởng phòng của phòng ban
            $recipients = $db->fetchAll("
                SELECT u.id as user_id, u.name, u.role
                FROM users u
                JOIN user_departments ud ON u.id = ud.user_id
                WHERE ud.department_id = ? AND ud.role_in_department = 'manager'
                AND u.status = 'active'
            ", [$department]);
            break;

        case 'general_manager':
            // Giám đốc chung
            $recipients = $db->fetchAll("
                SELECT id as user_id, name, role
                FROM users 
                WHERE role IN ('general_manager', 'ceo')
                AND status = 'active'
            ");
            break;

        case 'ceo':
            // Tổng giám đốc
            $recipients = $db->fetchAll("
                SELECT id as user_id, name, role
                FROM users 
                WHERE role = 'ceo'
                AND status = 'active'
            ");
            break;

        case 'all_managers':
            // Tất cả cấp quản lý
            $recipients = $db->fetchAll("
                SELECT id as user_id, name, role
                FROM users 
                WHERE role IN ('department_manager', 'general_manager', 'ceo')
                AND status = 'active'
            ");
            break;
    }

    return $recipients;
}

function getNotificationTitle($type)
{
    switch ($type) {
        case 'waiver_created':
            return 'Đơn miễn giảm mới';
        case 'waiver_approved':
            return 'Đơn miễn giảm được phê duyệt';
        case 'waiver_rejected':
            return 'Đơn miễn giảm bị từ chối';
        case 'approval_required':
            return 'Yêu cầu phê duyệt đơn miễn giảm';
        case 'approval_complete':
            return 'Phê duyệt đơn miễn giảm hoàn thành';
        default:
            return 'Thông báo hệ thống';
    }
}

function getNotificationIcon($type)
{
    switch ($type) {
        case 'waiver_created':
            return 'file-alt';
        case 'waiver_approved':
            return 'check-circle';
        case 'waiver_rejected':
            return 'times-circle';
        case 'approval_required':
            return 'exclamation-triangle';
        case 'approval_complete':
            return 'check-double';
        default:
            return 'bell';
    }
}

function createNextLevelNotification($application, $nextLevel)
{
    global $db;

    // Xác định cấp phê duyệt tiếp theo
    $approvalLevels = [
        1 => 'department_manager',
        2 => 'general_manager',
        3 => 'ceo'
    ];

    $nextRole = $approvalLevels[$nextLevel] ?? 'general_manager';

    $message = "Đơn miễn giảm #{$application['application_code']} cần phê duyệt ở cấp {$nextLevel}";

    $recipients = getNotificationRecipients($nextRole, $application['department_id'], $application);

    foreach ($recipients as $recipient) {
        $notificationData = [
            'user_id' => $recipient['user_id'],
            'title' => 'Yêu cầu phê duyệt đơn miễn giảm',
            'message' => $message,
            'type' => 'approval_required',
            'icon' => 'exclamation-triangle',
            'related_id' => $application['id'],
            'related_type' => 'waiver_application',
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->insert('notifications', $notificationData);
    }
}

function createApprovalCompleteNotification($application)
{
    global $db;

    $message = "Đơn miễn giảm #{$application['application_code']} đã được phê duyệt hoàn toàn";

    // Thông báo cho người tạo đơn
    $notificationData = [
        'user_id' => $application['created_by'],
        'title' => 'Đơn miễn giảm được phê duyệt',
        'message' => $message,
        'type' => 'approval_complete',
        'icon' => 'check-double',
        'related_id' => $application['id'],
        'related_type' => 'waiver_application',
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert('notifications', $notificationData);
}

function createRejectionNotification($application)
{
    global $db;

    $message = "Đơn miễn giảm #{$application['application_code']} đã bị từ chối";

    // Thông báo cho người tạo đơn
    $notificationData = [
        'user_id' => $application['created_by'],
        'title' => 'Đơn miễn giảm bị từ chối',
        'message' => $message,
        'type' => 'waiver_rejected',
        'icon' => 'times-circle',
        'related_id' => $application['id'],
        'related_type' => 'waiver_application',
        'is_read' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert('notifications', $notificationData);
}

function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
