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

if ($action === 'get_detail') {
    try {
        $activityId = intval($_GET['id']);

        // Get activity details with loan application info
        $activity = $db->fetchOne("
            SELECT 
                dca.*,
                la.application_code AS contract_code,
                la.loan_amount AS amount,
                la.loan_amount AS remaining_balance,
                la.customer_name,
                la.customer_phone_main AS customer_phone,
                u.name AS created_by_name
            FROM debt_collection_activities dca
            JOIN loan_applications la ON dca.contract_id = la.id
            LEFT JOIN users u ON dca.created_by = u.id
            WHERE dca.id = ?
        ", [$activityId]);

        if (!$activity) {
            throw new Exception('Không tìm thấy hoạt động thu hồi nợ');
        }

        echo json_encode([
            'success' => true,
            'activity' => $activity
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'update') {
    try {
        $activityId = intval($_POST['id']);
        $activityType = sanitize_input($_POST['activity_type']);
        $activityDate = $_POST['activity_date'];
        $activityTime = $_POST['activity_time'] ?? '';
        $result = sanitize_input($_POST['result']);
        $description = sanitize_input($_POST['description']);
        $nextAction = sanitize_input($_POST['next_action'] ?? '');
        $nextActionDate = $_POST['next_action_date'] ?? '';

        if (!$activityId || !$activityType || !$activityDate || !$result || !$description) {
            throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
        }

        // Get activity
        $activity = $db->fetchOne("SELECT * FROM debt_collection_activities WHERE id = ?", [$activityId]);

        if (!$activity) {
            throw new Exception('Không tìm thấy hoạt động thu hồi nợ');
        }

        // Update activity
        $updateData = [
            'activity_type' => $activityType,
            'activity_date' => $activityDate,
            'activity_time' => $activityTime,
            'result' => $result,
            'description' => $description,
            'next_action' => $nextAction,
            'next_action_date' => $nextActionDate,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $db->update('debt_collection_activities', $updateData, 'id = :id', ['id' => $activityId]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật hoạt động thu hồi nợ thành công!'
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
} elseif ($action === 'create') {
    try {
        $contractId = intval($_POST['contract_id']);
        $activityType = sanitize_input($_POST['activity_type']);
        $activityDate = $_POST['activity_date'];
        $activityTime = $_POST['activity_time'] ?? '';
        $result = sanitize_input($_POST['result']);
        $description = sanitize_input($_POST['description']);
        $nextAction = sanitize_input($_POST['next_action'] ?? '');
        $nextActionDate = $_POST['next_action_date'] ?? '';

        if (!$contractId || !$activityType || !$activityDate || !$result || !$description) {
            throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
        }

        // Get loan application
        $application = $db->fetchOne("SELECT * FROM loan_applications WHERE id = ?", [$contractId]);

        if (!$application) {
            throw new Exception('Không tìm thấy hợp đồng');
        }

        // Insert activity
        $activityData = [
            'contract_id' => $contractId,
            'customer_id' => $application['customer_id'],
            'activity_type' => $activityType,
            'activity_date' => $activityDate,
            'activity_time' => $activityTime,
            'result' => $result,
            'description' => $description,
            'next_action' => $nextAction,
            'next_action_date' => $nextActionDate,
            'created_by' => $_SESSION['user_id'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $activityId = $db->insert('debt_collection_activities', $activityData);

        if ($activityId) {
            echo json_encode([
                'success' => true,
                'message' => 'Thêm hoạt động thu hồi nợ thành công!',
                'activity_id' => $activityId
            ]);
        } else {
            throw new Exception('Có lỗi xảy ra khi thêm hoạt động');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'delete') {
    try {
        $activityId = intval($_POST['id']);

        // Get activity
        $activity = $db->fetchOne("SELECT * FROM debt_collection_activities WHERE id = ?", [$activityId]);

        if (!$activity) {
            throw new Exception('Không tìm thấy hoạt động thu hồi nợ');
        }

        // Delete activity
        $result = $db->delete('debt_collection_activities', 'id = :id', ['id' => $activityId]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa hoạt động thu hồi nợ thành công!'
            ]);
        } else {
            throw new Exception('Có lỗi xảy ra khi xóa');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} elseif ($action === 'get_loan_applications') {
    try {
        // Get loan applications for debt collection
        $applications = $db->fetchAll("
            SELECT 
                id,
                application_code,
                customer_name,
                customer_phone_main,
                loan_amount,
                status
            FROM loan_applications 
            WHERE status IN ('approved', 'disbursed')
            ORDER BY created_at DESC
            LIMIT 50
        ");

        echo json_encode([
            'success' => true,
            'applications' => $applications
        ]);
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
