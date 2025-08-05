<?php
// Disable session for API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Initialize database connection
$db = getDB();

// Set content type to JSON
header('Content-Type: application/json');

// Allow CORS for customer pages
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get contract ID
    $contractId = $_GET['contract_id'] ?? '';

    if (empty($contractId)) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng cung cấp mã hợp đồng',
            'data' => null
        ]);
        exit;
    }

    // Fetch customer card data
    $query = "
        SELECT 
            la.id,
            la.application_code,
            la.loan_amount,
            la.loan_term_months,
            la.status,
            la.created_at,
            la.updated_at,
            la.customer_name,
            la.customer_phone_main as customer_phone,
            la.customer_cmnd as identity_number,
            la.customer_address,
            la.customer_birth_date,
            la.customer_id_issued_place,
            la.customer_id_issued_date,
            la.customer_email,
            la.customer_job,
            la.customer_income,
            la.customer_company,
            la.monthly_rate,
            la.daily_rate,
            la.loan_purpose,
            la.asset_name,
            la.asset_quantity,
            la.asset_license_plate,
            la.asset_frame_number,
            la.asset_engine_number,
            la.asset_registration_number,
            la.asset_registration_date,
            la.asset_value,
            la.asset_description,
            la.emergency_contact_name,
            la.emergency_contact_phone,
            la.emergency_contact_relationship,
            la.emergency_contact_address,
            la.emergency_contact_note,
            la.has_health_insurance,
            la.has_life_insurance,
            la.has_vehicle_insurance,
            la.current_approval_level,
            la.highest_approval_level,
            la.total_approval_levels,
            la.created_by,
            la.department_id,
            la.final_decision,
            la.decision_date,
            la.approved_amount,
            la.decision_notes
        FROM loan_applications la
        WHERE la.application_code = ?
        LIMIT 1
    ";

    $result = $db->fetchOne($query, [$contractId]);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy thông tin hợp đồng',
            'data' => null
        ]);
        exit;
    }

    // Format the data
    $formattedData = [
        'id' => $result['id'],
        'application_code' => $result['application_code'],
        'loan_amount' => number_format($result['loan_amount'], 0, ',', '.') . ' VNĐ',
        'loan_term' => $result['loan_term_months'] . ' tháng',
        'interest_rate' => $result['monthly_rate'] . '%/tháng',
        'daily_rate' => $result['daily_rate'] . '%/ngày',
        'loan_purpose' => $result['loan_purpose'] ?? 'N/A',
        'status' => $result['status'],
        'status_text' => getStatusText($result['status']),
        'created_date' => date('d/m/Y', strtotime($result['created_at'])),
        'updated_date' => date('d/m/Y', strtotime($result['updated_at'])),

        // Customer information
        'customer_name' => $result['customer_name'],
        'customer_phone' => $result['customer_phone'] ?? 'N/A',
        'identity_number' => $result['identity_number'],
        'customer_address' => $result['customer_address'] ?? 'N/A',
        'customer_birth_date' => $result['customer_birth_date'] ? date('d/m/Y', strtotime($result['customer_birth_date'])) : 'N/A',
        'customer_id_issued_place' => $result['customer_id_issued_place'] ?? 'N/A',
        'customer_id_issued_date' => $result['customer_id_issued_date'] ? date('d/m/Y', strtotime($result['customer_id_issued_date'])) : 'N/A',
        'customer_email' => $result['customer_email'] ?? 'N/A',
        'customer_job' => $result['customer_job'] ?? 'N/A',
        'customer_income' => $result['customer_income'],
        'customer_company' => $result['customer_company'] ?? 'N/A',

        // Asset information
        'asset_name' => $result['asset_name'] ?? 'N/A',
        'asset_quantity' => $result['asset_quantity'] ?? 1,
        'asset_license_plate' => $result['asset_license_plate'] ?? 'N/A',
        'asset_frame_number' => $result['asset_frame_number'] ?? 'N/A',
        'asset_engine_number' => $result['asset_engine_number'] ?? 'N/A',
        'asset_registration_number' => $result['asset_registration_number'] ?? 'N/A',
        'asset_registration_date' => $result['asset_registration_date'] ? date('d/m/Y', strtotime($result['asset_registration_date'])) : 'N/A',
        'asset_value' => $result['asset_value'] ? number_format($result['asset_value'], 0, ',', '.') . ' VNĐ' : 'N/A',
        'asset_description' => $result['asset_description'] ?? 'N/A',

        // Emergency contact
        'emergency_contact' => [
            'name' => $result['emergency_contact_name'] ?? 'N/A',
            'phone' => $result['emergency_contact_phone'] ?? 'N/A',
            'relationship' => $result['emergency_contact_relationship'] ?? 'N/A',
            'address' => $result['emergency_contact_address'] ?? 'N/A',
            'note' => $result['emergency_contact_note'] ?? 'N/A'
        ],

        // Insurance
        'insurance' => [
            'health' => $result['has_health_insurance'] ? 'Có' : 'Không',
            'life' => $result['has_life_insurance'] ? 'Có' : 'Không',
            'vehicle' => $result['has_vehicle_insurance'] ? 'Có' : 'Không'
        ],

        // Approval information
        'current_approval_level' => $result['current_approval_level'] ?? 'N/A',
        'highest_approval_level' => $result['highest_approval_level'] ?? 'N/A',
        'total_approval_levels' => $result['total_approval_levels'] ?? 'N/A',
        'created_by' => $result['created_by'] ?? 'N/A',
        'department' => 'PDV TP.HCM', // Default value
        'final_decision' => $result['final_decision'] ?? 'N/A',
        'decision_date' => $result['decision_date'] ? date('d/m/Y', strtotime($result['decision_date'])) : 'N/A',
        'approved_amount' => $result['approved_amount'],
        'decision_notes' => $result['decision_notes'] ?? 'N/A'
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Lấy thông tin thẻ khách hàng thành công',
        'data' => $formattedData
    ]);
} catch (Exception $e) {
    error_log('Customer Card API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi lấy thông tin thẻ khách hàng',
        'data' => null
    ]);
}

// Helper functions
function getStatusText($status)
{
    $statusMap = [
        'pending' => 'Đang xử lý',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $statusMap[$status] ?? 'Không xác định';
}
