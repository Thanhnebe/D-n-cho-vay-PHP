<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// API endpoint cho contract details
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_detail':
        handleGetDetail($db);
        break;
    case 'get_details':
        handleGetDetails($db);
        break;
    case 'create_detail':
        handleCreateDetail($db);
        break;
    case 'update_detail':
        handleUpdateDetail($db);
        break;
    case 'delete_detail':
        handleDeleteDetail($db);
        break;
    case 'get_contracts_without_details':
        handleGetContractsWithoutDetails($db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleGetDetail($db)
{
    try {
        $detail_id = intval($_GET['id'] ?? 0);

        if (!$detail_id) {
            throw new Exception('ID chi tiết không hợp lệ');
        }

        $detail = $db->fetchOne("
            SELECT 
                cd.*,
                c.contract_code,
                c.amount,
                c.status as contract_status,
                c.start_date,
                c.end_date,
                cu.name as customer_name,
                cu.phone as customer_phone,
                cu.cif as customer_cif
            FROM contract_details cd 
            JOIN contracts c ON cd.contract_id = c.id 
            JOIN customers cu ON c.customer_id = cu.id 
            WHERE cd.id = ?
        ", [$detail_id]);

        if ($detail) {
            echo json_encode([
                'success' => true,
                'data' => $detail
            ]);
        } else {
            throw new Exception('Không tìm thấy chi tiết hợp đồng');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetDetails($db)
{
    try {
        $search = $_GET['search'] ?? '';
        $contract_id = $_GET['contract_id'] ?? '';
        $insurance_status = $_GET['insurance_status'] ?? '';
        $has_location_tracking = $_GET['has_location_tracking'] ?? '';
        $limit = intval($_GET['limit'] ?? 50);

        $where_conditions = [];
        $params = [];

        if ($search) {
            $where_conditions[] = "(cd.store_name LIKE ? OR cd.store_code LIKE ? OR c.contract_code LIKE ? OR cu.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($contract_id) {
            $where_conditions[] = "cd.contract_id = ?";
            $params[] = $contract_id;
        }

        if ($insurance_status) {
            $where_conditions[] = "cd.insurance_status = ?";
            $params[] = $insurance_status;
        }

        if ($has_location_tracking) {
            $where_conditions[] = "cd.has_location_tracking = ?";
            $params[] = $has_location_tracking;
        }

        $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $details = $db->fetchAll("
            SELECT 
                cd.*,
                c.contract_code,
                c.amount,
                c.status as contract_status,
                c.start_date,
                c.end_date,
                cu.name as customer_name,
                cu.phone as customer_phone,
                cu.cif as customer_cif
            FROM contract_details cd 
            JOIN contracts c ON cd.contract_id = c.id 
            JOIN customers cu ON c.customer_id = cu.id 
            {$where_clause} 
            ORDER BY cd.created_at DESC 
            LIMIT {$limit}
        ", $params);

        echo json_encode([
            'success' => true,
            'data' => $details
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleCreateDetail($db)
{
    try {
        $detailData = [
            'contract_id' => intval($_POST['contract_id']),
            'material_insurance' => $_POST['material_insurance'],
            'hospital_insurance' => $_POST['hospital_insurance'],
            'insurance_status' => $_POST['insurance_status'],
            'digital_signature' => $_POST['digital_signature'],
            'store_name' => $_POST['store_name'],
            'store_code' => $_POST['store_code'],
            'has_location_tracking' => $_POST['has_location_tracking'],
            'location_tracking_type' => $_POST['location_tracking_type'],
            'tima_customer_link' => $_POST['tima_customer_link'],
            'tima_agent_code' => $_POST['tima_agent_code'],
            'ndt_customer_link' => $_POST['ndt_customer_link'],
            're_data_enabled' => $_POST['re_data_enabled']
        ];

        // Validate required fields
        if (!$detailData['contract_id']) {
            throw new Exception('Hợp đồng là bắt buộc');
        }

        // Kiểm tra xem đã có chi tiết cho hợp đồng này chưa
        $existing = $db->fetchOne("SELECT id FROM contract_details WHERE contract_id = ?", [$detailData['contract_id']]);

        if ($existing) {
            throw new Exception('Đã tồn tại chi tiết cho hợp đồng này');
        }

        // Kiểm tra xem hợp đồng có tồn tại không
        $contract = $db->fetchOne("SELECT id FROM contracts WHERE id = ?", [$detailData['contract_id']]);

        if (!$contract) {
            throw new Exception('Hợp đồng không tồn tại');
        }

        $detail_id = $db->insert('contract_details', $detailData);

        echo json_encode([
            'success' => true,
            'message' => 'Tạo chi tiết hợp đồng thành công',
            'detail_id' => $detail_id
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateDetail($db)
{
    try {
        $detail_id = intval($_POST['detail_id'] ?? 0);

        if (!$detail_id) {
            throw new Exception('ID chi tiết không hợp lệ');
        }

        $detailData = [
            'contract_id' => intval($_POST['contract_id']),
            'material_insurance' => $_POST['material_insurance'],
            'hospital_insurance' => $_POST['hospital_insurance'],
            'insurance_status' => $_POST['insurance_status'],
            'digital_signature' => $_POST['digital_signature'],
            'store_name' => $_POST['store_name'],
            'store_code' => $_POST['store_code'],
            'has_location_tracking' => $_POST['has_location_tracking'],
            'location_tracking_type' => $_POST['location_tracking_type'],
            'tima_customer_link' => $_POST['tima_customer_link'],
            'tima_agent_code' => $_POST['tima_agent_code'],
            'ndt_customer_link' => $_POST['ndt_customer_link'],
            're_data_enabled' => $_POST['re_data_enabled']
        ];

        // Validate required fields
        if (!$detailData['contract_id']) {
            throw new Exception('Hợp đồng là bắt buộc');
        }

        // Kiểm tra xem có hợp đồng khác đã có chi tiết này chưa (trừ hợp đồng hiện tại)
        $existing = $db->fetchOne("SELECT id FROM contract_details WHERE contract_id = ? AND id != ?", [$detailData['contract_id'], $detail_id]);

        if ($existing) {
            throw new Exception('Đã tồn tại chi tiết cho hợp đồng này');
        }

        // Kiểm tra xem hợp đồng có tồn tại không
        $contract = $db->fetchOne("SELECT id FROM contracts WHERE id = ?", [$detailData['contract_id']]);

        if (!$contract) {
            throw new Exception('Hợp đồng không tồn tại');
        }

        $affected = $db->update('contract_details', $detailData, 'id = ?', ['id' => $detail_id]);

        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật chi tiết hợp đồng thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy chi tiết để cập nhật');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteDetail($db)
{
    try {
        $detail_id = intval($_POST['detail_id'] ?? 0);

        if (!$detail_id) {
            throw new Exception('ID chi tiết không hợp lệ');
        }

        $affected = $db->delete('contract_details', 'id = ?', [$detail_id]);

        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa chi tiết hợp đồng thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy chi tiết để xóa');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetContractsWithoutDetails($db)
{
    try {
        $contracts = $db->fetchAll("
            SELECT c.id, c.contract_code, cu.name as customer_name, c.amount
            FROM contracts c 
            JOIN customers cu ON c.customer_id = cu.id 
            WHERE c.id NOT IN (SELECT contract_id FROM contract_details)
            ORDER BY c.created_at DESC
        ");

        echo json_encode([
            'success' => true,
            'data' => $contracts
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
