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

// Only allow GET requests for search
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get search parameters
    $searchInput = $_GET['search'] ?? '';
    $searchType = $_GET['type'] ?? 'all';
    $statusFilter = $_GET['status'] ?? 'all';
    $dateFilter = $_GET['date'] ?? 'all';

    if (empty($searchInput)) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng nhập thông tin tìm kiếm',
            'data' => []
        ]);
        exit;
    }

    // Build search query based on parameters
    $whereConditions = [];
    $params = [];

    // Base search condition
    switch ($searchType) {
        case 'contract':
            $whereConditions[] = "la.application_code LIKE ?";
            $params[] = "%$searchInput%";
            break;
        case 'id':
            $whereConditions[] = "la.customer_cmnd LIKE ?";
            $params[] = "%$searchInput%";
            break;
        case 'phone':
            $whereConditions[] = "la.customer_phone_main LIKE ?";
            $params[] = "%$searchInput%";
            break;
        default:
            // Search in all fields
            $whereConditions[] = "(la.application_code LIKE ? OR la.customer_cmnd LIKE ? OR la.customer_phone_main LIKE ? OR la.customer_name LIKE ?)";
            $params[] = "%$searchInput%";
            $params[] = "%$searchInput%";
            $params[] = "%$searchInput%";
            $params[] = "%$searchInput%";
            break;
    }

    // Status filter
    if ($statusFilter !== 'all') {
        $statusMap = [
            'approved' => 'approved',
            'pending' => 'pending',
            'rejected' => 'rejected'
        ];
        if (isset($statusMap[$statusFilter])) {
            $whereConditions[] = "la.status = ?";
            $params[] = $statusMap[$statusFilter];
        }
    }

    // Date filter
    if ($dateFilter !== 'all') {
        $dateConditions = [
            'today' => 'DATE(la.created_at) = CURDATE()',
            'week' => 'la.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)',
            'month' => 'la.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)',
            'year' => 'la.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)'
        ];
        if (isset($dateConditions[$dateFilter])) {
            $whereConditions[] = $dateConditions[$dateFilter];
        }
    }

    // Build the complete query
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

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
            la.asset_value,
            la.emergency_contact_name,
            la.emergency_contact_phone,
            la.has_health_insurance,
            la.has_life_insurance,
            la.has_vehicle_insurance
        FROM loan_applications la
        $whereClause
        ORDER BY la.created_at DESC
        LIMIT 50
    ";

    // Execute query
    $results = $db->fetchAll($query, $params);

    // Format results
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            'contract_id' => $row['application_code'],
            'customer_name' => $row['customer_name'] ?? 'N/A',
            'customer_phone' => $row['customer_phone'] ?? 'N/A',
            'identity_number' => $row['identity_number'] ?? 'N/A',
            'customer_address' => $row['customer_address'] ?? 'N/A',
            'loan_amount' => number_format($row['loan_amount'], 0, ',', '.') . ' VNĐ',
            'loan_term' => $row['loan_term_months'] . ' tháng',
            'interest_rate' => $row['monthly_rate'] . '%/tháng',
            'daily_rate' => $row['daily_rate'] . '%/ngày',
            'rate_name' => 'Vay vốn',
            'department' => 'PDV TP.HCM',
            'status' => $row['status'],
            'status_text' => getStatusText($row['status']),
            'status_class' => getStatusBadgeClass($row['status']),
            'created_date' => date('d/m/Y', strtotime($row['created_at'])),
            'updated_date' => date('d/m/Y', strtotime($row['updated_at'])),
            'loan_type' => 'Vay vốn',
            'loan_purpose' => $row['loan_purpose'] ?? 'N/A',
            'asset_name' => $row['asset_name'] ?? 'N/A',
            'asset_value' => $row['asset_value'] ? number_format($row['asset_value'], 0, ',', '.') . ' VNĐ' : 'N/A',
            'emergency_contact' => [
                'name' => $row['emergency_contact_name'] ?? 'N/A',
                'phone' => $row['emergency_contact_phone'] ?? 'N/A'
            ],
            'insurance' => [
                'health' => $row['has_health_insurance'] ? 'Có' : 'Không',
                'life' => $row['has_life_insurance'] ? 'Có' : 'Không',
                'vehicle' => $row['has_vehicle_insurance'] ? 'Có' : 'Không'
            ],
            'actions' => [
                'view_details' => true,
                'download_contract' => $row['status'] === 'approved',
                'track_progress' => $row['status'] === 'pending',
                'contact_support' => true
            ]
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tìm kiếm thành công',
        'data' => $formattedResults,
        'total' => count($formattedResults)
    ]);
} catch (Exception $e) {
    error_log('Search API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại sau.',
        'data' => []
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

function getStatusBadgeClass($status)
{
    $classMap = [
        'pending' => 'status-pending',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        'completed' => 'status-approved',
        'cancelled' => 'status-rejected'
    ];
    return $classMap[$status] ?? 'status-pending';
}

function getLoanType($rateName)
{
    if (strpos(strtolower($rateName), 'cầm cố') !== false) {
        return 'Cầm cố';
    } elseif (strpos(strtolower($rateName), 'tín chấp') !== false) {
        return 'Tín chấp';
    } elseif (strpos(strtolower($rateName), 'thế chấp') !== false) {
        return 'Thế chấp';
    } else {
        return 'Vay vốn';
    }
}
