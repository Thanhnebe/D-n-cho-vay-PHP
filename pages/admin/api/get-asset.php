<?php
// Disable session for API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/functions.php';

$db = getDB();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $assetId = $_GET['id'] ?? '';
    if (empty($assetId)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng cung cấp ID tài sản', 'data' => null]);
        exit;
    }

    $query = "
        SELECT a.*, ac.name as category_name, cu.name as customer_name 
        FROM assets a 
        LEFT JOIN asset_categories ac ON a.category_id = ac.id 
        LEFT JOIN customers cu ON a.customer_id = cu.id 
        WHERE a.id = ?
        LIMIT 1
    ";
    $result = $db->fetchOne($query, [$assetId]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài sản', 'data' => null]);
        exit;
    }

    $formattedData = [
        'id' => $result['id'],
        'name' => $result['name'],
        'customer_id' => $result['customer_id'],
        'category_id' => $result['category_id'],
        'category_name' => $result['category_name'],
        'customer_name' => $result['customer_name'],
        'estimated_value' => $result['estimated_value'],
        'condition_status' => $result['condition_status'],
        'status' => $result['status'],
        'description' => $result['description'],
        'notes' => $result['notes'],
        'created_at' => $result['created_at'],
        'updated_at' => $result['updated_at']
    ];

    echo json_encode(['success' => true, 'message' => 'Lấy thông tin tài sản thành công', 'data' => $formattedData]);
} catch (Exception $e) {
    error_log('Get Asset API Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lấy thông tin tài sản', 'data' => null]);
}
