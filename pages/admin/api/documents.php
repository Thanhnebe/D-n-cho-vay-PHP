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

    // Define document categories based on the image
    $documentCategories = [
        [
            'id' => 1,
            'name' => 'Ảnh chân dung KH',
            'description' => 'Ảnh chân dung khách hàng',
            'status' => 'completed',
            'count' => 0
        ],
        [
            'id' => 2,
            'name' => 'Giấy tờ khác',
            'description' => 'Các giấy tờ khác',
            'status' => 'pending',
            'count' => 0
        ],
        [
            'id' => 3,
            'name' => 'Xe định vị thiết bị',
            'description' => 'Thiết bị định vị xe',
            'status' => 'pending',
            'count' => 0
        ],
        [
            'id' => 4,
            'name' => 'Ảnh chụp 2 bản gốc CMND/CCCD - Ảnh chụp CVKD',
            'description' => 'Ảnh chụp 2 bản gốc CMND/CCCD và ảnh chụp CVKD',
            'status' => 'pending',
            'count' => 0
        ],
        [
            'id' => 5,
            'name' => 'Ảnh 2 bản gốc ĐKX - CVKD shot',
            'description' => 'Ảnh 2 bản gốc Đăng ký xe - CVKD shot',
            'status' => 'pending',
            'count' => 0
        ],
        [
            'id' => 6,
            'name' => 'Ảnh 2 mặt bản gốc ĐKX qua máy soi/đèn soi',
            'description' => 'Ảnh 2 mặt bản gốc Đăng ký xe qua máy soi/đèn soi',
            'status' => 'pending',
            'count' => 0
        ],
        [
            'id' => 7,
            'name' => 'Ảnh thực tế 2 bên xe, đầu xe và phía sau xe',
            'description' => 'Ảnh thực tế 2 bên xe, đầu xe và phía sau xe',
            'status' => 'pending',
            'count' => 0
        ],
        [
            'id' => 8,
            'name' => 'Số khung hình, số máy',
            'description' => 'Số khung hình, số máy',
            'status' => 'pending',
            'count' => 0
        ]
    ];

    // Check if documents table exists and has data
    try {
        $documentsQuery = "
            SELECT 
                d.id,
                d.document_type,
                d.file_name,
                d.file_path,
                d.upload_date,
                d.status,
                d.description
            FROM documents d
            WHERE d.contract_id = ?
            ORDER BY d.upload_date DESC
        ";

        $documents = $db->fetchAll($documentsQuery, [$contractId]);

        // Update document counts based on actual data
        foreach ($documentCategories as &$category) {
            $category['count'] = count(array_filter($documents, function ($doc) use ($category) {
                return strpos(strtolower($doc['document_type']), strtolower($category['name'])) !== false;
            }));

            // Update status based on count
            if ($category['count'] > 0) {
                $category['status'] = 'completed';
            }
        }
    } catch (Exception $e) {
        // If documents table doesn't exist or has error, use default data
        error_log('Documents table error: ' . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'Lấy danh sách chứng từ thành công',
        'data' => $documentCategories,
        'contract_id' => $contractId
    ]);
} catch (Exception $e) {
    error_log('Documents API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi lấy danh sách chứng từ',
        'data' => []
    ]);
}
