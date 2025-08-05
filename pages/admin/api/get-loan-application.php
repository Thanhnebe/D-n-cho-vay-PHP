<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

try {
    $db = getDB();

    if (!isset($_GET['id'])) {
        throw new Exception('Thiếu ID đơn vay');
    }

    $applicationId = intval($_GET['id']);

    // Lấy thông tin chi tiết đơn vay
    $application = $db->fetchOne("
        SELECT la.*, 
               c.name as customer_name, 
               c.phone as customer_phone,
               a.name as asset_name,
               ir.description as rate_description,
               u.name as created_by_name,
               d.name as department_name
        FROM loan_applications la
        LEFT JOIN customers c ON la.customer_id = c.id
        LEFT JOIN assets a ON la.asset_id = a.id
        LEFT JOIN interest_rates ir ON la.interest_rate_id = ir.id
        LEFT JOIN users u ON la.created_by = u.id
        LEFT JOIN departments d ON la.department_id = d.id
        WHERE la.id = ?
    ", [$applicationId]);

    if (!$application) {
        throw new Exception('Không tìm thấy đơn vay');
    }

    // Format dữ liệu
    $application['loan_amount_formatted'] = format_currency($application['loan_amount']);
    $application['approved_amount_formatted'] = $application['approved_amount'] ? format_currency($application['approved_amount']) : null;
    $application['customer_income_formatted'] = $application['customer_income'] ? format_currency($application['customer_income']) : null;
    $application['asset_value_formatted'] = $application['asset_value'] ? format_currency($application['asset_value']) : null;
    $application['created_at_formatted'] = date('d/m/Y H:i', strtotime($application['created_at']));

    // Status mapping
    $statusMap = [
        'draft' => ['label' => 'Nháp', 'class' => 'secondary'],
        'pending' => ['label' => 'Chờ duyệt', 'class' => 'warning'],
        'approved' => ['label' => 'Đã duyệt', 'class' => 'success'],
        'rejected' => ['label' => 'Đã từ chối', 'class' => 'danger'],
        'disbursed' => ['label' => 'Đã giải ngân', 'class' => 'info'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'dark']
    ];

    $application['status_label'] = $statusMap[$application['status']]['label'] ?? 'Không xác định';
    $application['status_class'] = $statusMap[$application['status']]['class'] ?? 'secondary';

    // Asset display name
    if ($application['asset_name']) {
        $application['asset_display_name'] = $application['asset_name'];
        if ($application['asset_license_plate']) {
            $application['asset_display_name'] .= ' (' . $application['asset_license_plate'] . ')';
        }
    } else {
        $application['asset_display_name'] = 'Không có tài sản thế chấp';
    }

    echo json_encode([
        'success' => true,
        'data' => $application
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
