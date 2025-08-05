<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// API endpoint cho interest rates
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
    case 'get_rate':
        handleGetRate($db);
        break;
    case 'get_rates':
        handleGetRates($db);
        break;
    case 'create_rate':
        handleCreateRate($db);
        break;
    case 'update_rate':
        handleUpdateRate($db);
        break;
    case 'delete_rate':
        handleDeleteRate($db);
        break;
    case 'get_current_rates':
        handleGetCurrentRates($db);
        break;
    case 'validate_rate':
        handleValidateRate($db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleGetRate($db)
{
    try {
        $rate_id = intval($_GET['id'] ?? 0);

        if (!$rate_id) {
            throw new Exception('ID lãi suất không hợp lệ');
        }

        $rate = $db->fetchOne("
            SELECT ir.*, u.name as created_by_name 
            FROM interest_rates ir 
            LEFT JOIN users u ON ir.created_by = u.id 
            WHERE ir.id = ?
        ", [$rate_id]);

        if ($rate) {
            echo json_encode([
                'success' => true,
                'data' => $rate
            ]);
        } else {
            throw new Exception('Không tìm thấy lãi suất');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetRates($db)
{
    try {
        $search = $_GET['search'] ?? '';
        $loan_type = $_GET['loan_type'] ?? '';
        $status = $_GET['status'] ?? '';
        $limit = intval($_GET['limit'] ?? 50);

        $where_conditions = [];
        $params = [];

        if ($search) {
            $where_conditions[] = "(ir.description LIKE ? OR ir.loan_type LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($loan_type) {
            $where_conditions[] = "ir.loan_type = ?";
            $params[] = $loan_type;
        }

        if ($status) {
            $where_conditions[] = "ir.status = ?";
            $params[] = $status;
        }

        $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $rates = $db->fetchAll("
            SELECT ir.*, u.name as created_by_name 
            FROM interest_rates ir 
            LEFT JOIN users u ON ir.created_by = u.id 
            {$where_clause} 
            ORDER BY ir.effective_from DESC, ir.created_at DESC 
            LIMIT {$limit}
        ", $params);

        echo json_encode([
            'success' => true,
            'data' => $rates
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleCreateRate($db)
{
    try {
        $rateData = [
            'loan_type' => $_POST['loan_type'],
            'min_amount' => floatval($_POST['min_amount']),
            'max_amount' => floatval($_POST['max_amount']),
            'monthly_rate' => floatval($_POST['monthly_rate']),
            'daily_rate' => floatval($_POST['daily_rate']),
            'grace_period_days' => intval($_POST['grace_period_days']),
            'late_fee_rate' => floatval($_POST['late_fee_rate']),
            'effective_from' => $_POST['effective_from'],
            'effective_to' => $_POST['effective_to'] ?: null,
            'status' => $_POST['status'],
            'description' => $_POST['description'],
            'created_by' => $_SESSION['user_id'] ?? 1
        ];

        // Validate required fields
        if (
            !$rateData['loan_type'] || !$rateData['min_amount'] || !$rateData['max_amount'] ||
            !$rateData['monthly_rate'] || !$rateData['daily_rate'] || !$rateData['effective_from']
        ) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Validate amount range
        if ($rateData['min_amount'] >= $rateData['max_amount']) {
            throw new Exception('Số tiền tối thiểu phải nhỏ hơn số tiền tối đa');
        }

        // Validate overlapping rates
        $overlapping = $db->fetchOne("
            SELECT id FROM interest_rates 
            WHERE loan_type = ? 
            AND status = 'active'
            AND (
                (min_amount <= ? AND max_amount >= ?) OR
                (min_amount <= ? AND max_amount >= ?) OR
                (min_amount >= ? AND max_amount <= ?)
            )
            AND effective_from <= ? 
            AND (effective_to IS NULL OR effective_to >= ?)
        ", [
            $rateData['loan_type'],
            $rateData['min_amount'],
            $rateData['min_amount'],
            $rateData['max_amount'],
            $rateData['max_amount'],
            $rateData['min_amount'],
            $rateData['max_amount'],
            $rateData['effective_from'],
            $rateData['effective_from']
        ]);

        if ($overlapping) {
            throw new Exception('Đã tồn tại lãi suất cho khoảng tiền và thời gian này');
        }

        $rate_id = $db->insert('interest_rates', $rateData);

        echo json_encode([
            'success' => true,
            'message' => 'Tạo lãi suất thành công',
            'rate_id' => $rate_id
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateRate($db)
{
    try {
        $rate_id = intval($_POST['rate_id'] ?? 0);

        if (!$rate_id) {
            throw new Exception('ID lãi suất không hợp lệ');
        }

        $rateData = [
            'loan_type' => $_POST['loan_type'],
            'min_amount' => floatval($_POST['min_amount']),
            'max_amount' => floatval($_POST['max_amount']),
            'monthly_rate' => floatval($_POST['monthly_rate']),
            'daily_rate' => floatval($_POST['daily_rate']),
            'grace_period_days' => intval($_POST['grace_period_days']),
            'late_fee_rate' => floatval($_POST['late_fee_rate']),
            'effective_from' => $_POST['effective_from'],
            'effective_to' => $_POST['effective_to'] ?: null,
            'status' => $_POST['status'],
            'description' => $_POST['description']
        ];

        // Validate required fields
        if (
            !$rateData['loan_type'] || !$rateData['min_amount'] || !$rateData['max_amount'] ||
            !$rateData['monthly_rate'] || !$rateData['daily_rate'] || !$rateData['effective_from']
        ) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Validate amount range
        if ($rateData['min_amount'] >= $rateData['max_amount']) {
            throw new Exception('Số tiền tối thiểu phải nhỏ hơn số tiền tối đa');
        }

        // Validate overlapping rates (excluding current rate)
        $overlapping = $db->fetchOne("
            SELECT id FROM interest_rates 
            WHERE loan_type = ? 
            AND status = 'active'
            AND id != ?
            AND (
                (min_amount <= ? AND max_amount >= ?) OR
                (min_amount <= ? AND max_amount >= ?) OR
                (min_amount >= ? AND max_amount <= ?)
            )
            AND effective_from <= ? 
            AND (effective_to IS NULL OR effective_to >= ?)
        ", [
            $rateData['loan_type'],
            $rate_id,
            $rateData['min_amount'],
            $rateData['min_amount'],
            $rateData['max_amount'],
            $rateData['max_amount'],
            $rateData['min_amount'],
            $rateData['max_amount'],
            $rateData['effective_from'],
            $rateData['effective_from']
        ]);

        if ($overlapping) {
            throw new Exception('Đã tồn tại lãi suất cho khoảng tiền và thời gian này');
        }

        $affected = $db->update('interest_rates', $rateData, 'id = ?', ['id' => $rate_id]);

        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật lãi suất thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy lãi suất để cập nhật');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteRate($db)
{
    try {
        $rate_id = intval($_POST['rate_id'] ?? 0);

        if (!$rate_id) {
            throw new Exception('ID lãi suất không hợp lệ');
        }

        // Kiểm tra xem có hợp đồng nào đang sử dụng lãi suất này không
        $contracts_using_rate = $db->fetchAll("SELECT COUNT(*) as count FROM contracts WHERE interest_rate_id = ?", [$rate_id]);

        if ($contracts_using_rate[0]['count'] > 0) {
            throw new Exception('Không thể xóa lãi suất đang được sử dụng trong hợp đồng');
        }

        $affected = $db->delete('interest_rates', 'id = ?', [$rate_id]);

        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa lãi suất thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy lãi suất để xóa');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetCurrentRates($db)
{
    try {
        $amount = floatval($_GET['amount'] ?? 0);
        $loan_type = $_GET['loan_type'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');

        $where_conditions = [
            "ir.status = 'active'",
            "ir.effective_from <= ?",
            "(ir.effective_to IS NULL OR ir.effective_to >= ?)"
        ];
        $params = [$date, $date];

        if ($amount > 0) {
            $where_conditions[] = "ir.min_amount <= ? AND ir.max_amount >= ?";
            $params[] = $amount;
            $params[] = $amount;
        }

        if ($loan_type) {
            $where_conditions[] = "ir.loan_type = ?";
            $params[] = $loan_type;
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

        $rates = $db->fetchAll("
            SELECT ir.* 
            FROM interest_rates ir 
            {$where_clause} 
            ORDER BY ir.loan_type, ir.min_amount
        ", $params);

        echo json_encode([
            'success' => true,
            'data' => $rates
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleValidateRate($db)
{
    try {
        $loan_type = $_POST['loan_type'] ?? '';
        $min_amount = floatval($_POST['min_amount'] ?? 0);
        $max_amount = floatval($_POST['max_amount'] ?? 0);
        $effective_from = $_POST['effective_from'] ?? '';
        $rate_id = intval($_POST['rate_id'] ?? 0);

        if (!$loan_type || !$min_amount || !$max_amount || !$effective_from) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        if ($min_amount >= $max_amount) {
            throw new Exception('Số tiền tối thiểu phải nhỏ hơn số tiền tối đa');
        }

        // Check for overlapping rates
        $where_conditions = [
            "loan_type = ?",
            "status = 'active'",
            "id != ?",
            "(",
            "  (min_amount <= ? AND max_amount >= ?) OR",
            "  (min_amount <= ? AND max_amount >= ?) OR",
            "  (min_amount >= ? AND max_amount <= ?)",
            ")",
            "effective_from <= ?",
            "(effective_to IS NULL OR effective_to >= ?)"
        ];

        $params = [
            $loan_type,
            $rate_id,
            $min_amount,
            $min_amount,
            $max_amount,
            $max_amount,
            $min_amount,
            $max_amount,
            $effective_from,
            $effective_from
        ];

        $overlapping = $db->fetchOne(
            "
            SELECT id FROM interest_rates 
            WHERE " . implode(' AND ', $where_conditions),
            $params
        );

        if ($overlapping) {
            echo json_encode([
                'success' => false,
                'valid' => false,
                'message' => 'Đã tồn tại lãi suất cho khoảng tiền và thời gian này'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'valid' => true,
                'message' => 'Lãi suất hợp lệ'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
