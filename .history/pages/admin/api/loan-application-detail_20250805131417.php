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

    // Lấy thông tin đơn vay từ bảng loan_applications (đã có đầy đủ thông tin)
    $application = $db->fetchOne("
        SELECT la.*,
               c.name as customer_name_from_customer,
               c.phone as customer_phone_from_customer,
               c.email as customer_email_from_customer,
               ir.description as rate_description,
               ir.monthly_rate as interest_monthly_rate,
               u.name as created_by_name,
               d.name as department_name
        FROM loan_applications la
        LEFT JOIN customers c ON la.customer_id = c.id
        LEFT JOIN interest_rates ir ON la.interest_rate_id = ir.id
        LEFT JOIN users u ON la.created_by = u.id
        LEFT JOIN departments d ON la.department_id = d.id
        WHERE la.id = ?
    ", [$applicationId]);

    if (!$application) {
        throw new Exception('Không tìm thấy đơn vay');
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
