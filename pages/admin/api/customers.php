<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

try {
    $db = getDB();

    $action = $_GET['action'] ?? '';

    if ($action === 'get_all') {
        $customers = $db->fetchAll("
            SELECT id, name, cmnd, id_number, phone, email
            FROM customers
            ORDER BY name
        ");

        echo json_encode([
            'success' => true,
            'data' => $customers
        ]);
    } else {
        throw new Exception('Action không hợp lệ');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
