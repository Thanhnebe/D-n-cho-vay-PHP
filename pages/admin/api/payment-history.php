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
            'data' => []
        ]);
        exit;
    }

    // First, get the contract ID from application_code
    $contractQuery = "SELECT id FROM loan_applications WHERE application_code = ?";
    $contractResult = $db->fetchOne($contractQuery, [$contractId]);

    if (!$contractResult) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy hợp đồng',
            'data' => []
        ]);
        exit;
    }

    $contractId = $contractResult['id'];

    // Fetch payment history
    $query = "
        SELECT 
            p.id,
            p.amount,
            p.payment_date,
            p.payment_method,
            p.reference_number,
            p.description,
            p.created_at,
            pt.name as payment_type_name,
            pt.description as payment_type_description,
            u.name as created_by_name
        FROM payments p
        LEFT JOIN payment_types pt ON p.payment_type_id = pt.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE p.contract_id = ?
        ORDER BY p.payment_date DESC, p.created_at DESC
        LIMIT 100
    ";

    $results = $db->fetchAll($query, [$contractId]);

    // Format the data
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            'id' => $row['id'],
            'amount' => number_format($row['amount'], 0, ',', '.') . ' VNĐ',
            'payment_date' => date('d/m/Y', strtotime($row['payment_date'])),
            'payment_method' => getPaymentMethodText($row['payment_method']),
            'payment_type' => $row['payment_type_name'] ?? 'Không xác định',
            'reference_number' => $row['reference_number'] ?? 'N/A',
            'description' => $row['description'] ?? 'N/A',
            'created_by' => $row['created_by_name'] ?? 'N/A',
            'created_at' => date('d/m/Y H:i', strtotime($row['created_at'])),
            'status' => 'Đã thanh toán',
            'status_class' => 'text-success'
        ];
    }

    // Get payment summary
    $summaryQuery = "
        SELECT 
            COUNT(*) as total_payments,
            SUM(amount) as total_amount,
            MIN(payment_date) as first_payment,
            MAX(payment_date) as last_payment
        FROM payments 
        WHERE contract_id = ?
    ";

    $summary = $db->fetchOne($summaryQuery, [$contractId]);

    echo json_encode([
        'success' => true,
        'message' => 'Lấy lịch sử thanh toán thành công',
        'data' => $formattedResults,
        'summary' => [
            'total_payments' => $summary['total_payments'] ?? 0,
            'total_amount' => $summary['total_amount'] ? number_format($summary['total_amount'], 0, ',', '.') . ' VNĐ' : '0 VNĐ',
            'first_payment' => $summary['first_payment'] ? date('d/m/Y', strtotime($summary['first_payment'])) : 'N/A',
            'last_payment' => $summary['last_payment'] ? date('d/m/Y', strtotime($summary['last_payment'])) : 'N/A'
        ]
    ]);
} catch (Exception $e) {
    error_log('Payment History API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi lấy lịch sử thanh toán',
        'data' => []
    ]);
}

// Helper functions
function getPaymentMethodText($method)
{
    $methodMap = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'mobile_money' => 'Ví điện tử',
        'other' => 'Khác'
    ];
    return $methodMap[$method] ?? 'Không xác định';
}
