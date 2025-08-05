<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// API endpoint cho permissions
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
    case 'get_loan_roles':
        handleGetLoanRoles($db);
        break;
    case 'get_debt_roles':
        handleGetDebtRoles($db);
        break;
    case 'get_users':
        handleGetUsers($db);
        break;
    case 'get_waiver_limit':
        handleGetWaiverLimit($db);
        break;
    case 'create_loan_role':
        handleCreateLoanRole($db);
        break;
    case 'update_loan_role':
        handleUpdateLoanRole($db);
        break;
    case 'delete_loan_role':
        handleDeleteLoanRole($db);
        break;
    case 'create_debt_role':
        handleCreateDebtRole($db);
        break;
    case 'update_debt_role':
        handleUpdateDebtRole($db);
        break;
    case 'delete_debt_role':
        handleDeleteDebtRole($db);
        break;
    case 'assign_user_role':
        handleAssignUserRole($db);
        break;
    case 'remove_user_role':
        handleRemoveUserRole($db);
        break;
    case 'update_waiver_limits':
        handleUpdateWaiverLimits($db);
        break;
    case 'delete_waiver_limits':
        handleDeleteWaiverLimits($db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleGetLoanRoles($db) {
    try {
        $roles = $db->fetchAll("SELECT * FROM loan_approval_roles ORDER BY approval_order");
        echo json_encode([
            'success' => true,
            'data' => $roles
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetDebtRoles($db) {
    try {
        $roles = $db->fetchAll("SELECT * FROM debt_collection_roles ORDER BY approval_limit");
        echo json_encode([
            'success' => true,
            'data' => $roles
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetUsers($db) {
    try {
        $users = $db->fetchAll("SELECT id, name, email, role FROM users WHERE status = 'active' ORDER BY name");
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetWaiverLimit($db) {
    try {
        $limit_id = intval($_GET['id'] ?? 0);
        
        if (!$limit_id) {
            throw new Exception('ID giới hạn không hợp lệ');
        }
        
        $limit = $db->fetchOne("SELECT * FROM waiver_approval_limits WHERE id = ?", [$limit_id]);
        
        if ($limit) {
            echo json_encode([
                'success' => true,
                'data' => $limit
            ]);
        } else {
            throw new Exception('Không tìm thấy giới hạn');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleCreateLoanRole($db) {
    try {
        $roleData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'approval_order' => intval($_POST['approval_order']),
            'min_amount' => floatval($_POST['min_amount']),
            'max_amount' => floatval($_POST['max_amount']),
            'status' => $_POST['status']
        ];
        
        $role_id = $db->insert('loan_approval_roles', $roleData);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tạo vai trò phê duyệt khoản vay thành công',
            'role_id' => $role_id
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateLoanRole($db) {
    try {
        $role_id = intval($_POST['role_id'] ?? 0);
        
        if (!$role_id) {
            throw new Exception('ID vai trò không hợp lệ');
        }
        
        $roleData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'approval_order' => intval($_POST['approval_order']),
            'min_amount' => floatval($_POST['min_amount']),
            'max_amount' => floatval($_POST['max_amount']),
            'status' => $_POST['status']
        ];
        
        $affected = $db->update('loan_approval_roles', $roleData, 'id = ?', ['id' => $role_id]);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật vai trò phê duyệt khoản vay thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy vai trò để cập nhật');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteLoanRole($db) {
    try {
        $role_id = intval($_POST['role_id'] ?? 0);
        
        if (!$role_id) {
            throw new Exception('ID vai trò không hợp lệ');
        }
        
        // Kiểm tra xem có user nào đang sử dụng role này không
        $users_with_role = $db->fetchAll("SELECT COUNT(*) as count FROM loan_approval_users WHERE role_id = ?", [$role_id]);
        
        if ($users_with_role[0]['count'] > 0) {
            throw new Exception('Không thể xóa vai trò đang được sử dụng');
        }
        
        $affected = $db->delete('loan_approval_roles', 'id = ?', [$role_id]);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa vai trò phê duyệt khoản vay thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy vai trò để xóa');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleCreateDebtRole($db) {
    try {
        $roleData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'approval_limit' => floatval($_POST['approval_limit']),
            'status' => $_POST['status']
        ];
        
        $role_id = $db->insert('debt_collection_roles', $roleData);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tạo vai trò thu hồi nợ thành công',
            'role_id' => $role_id
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateDebtRole($db) {
    try {
        $role_id = intval($_POST['role_id'] ?? 0);
        
        if (!$role_id) {
            throw new Exception('ID vai trò không hợp lệ');
        }
        
        $roleData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'approval_limit' => floatval($_POST['approval_limit']),
            'status' => $_POST['status']
        ];
        
        $affected = $db->update('debt_collection_roles', $roleData, 'id = ?', ['id' => $role_id]);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật vai trò thu hồi nợ thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy vai trò để cập nhật');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteDebtRole($db) {
    try {
        $role_id = intval($_POST['role_id'] ?? 0);
        
        if (!$role_id) {
            throw new Exception('ID vai trò không hợp lệ');
        }
        
        // Kiểm tra xem có user nào đang sử dụng role này không
        $users_with_role = $db->fetchAll("SELECT COUNT(*) as count FROM debt_collection_users WHERE role_id = ?", [$role_id]);
        
        if ($users_with_role[0]['count'] > 0) {
            throw new Exception('Không thể xóa vai trò đang được sử dụng');
        }
        
        $affected = $db->delete('debt_collection_roles', 'id = ?', [$role_id]);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa vai trò thu hồi nợ thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy vai trò để xóa');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleAssignUserRole($db) {
    try {
        $user_id = intval($_POST['user_id'] ?? 0);
        $role_id = intval($_POST['role_id'] ?? 0);
        $role_type = $_POST['role_type'] ?? '';
        
        if (!$user_id || !$role_id || !$role_type) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }
        
        if ($role_type === 'loan') {
            $table = 'loan_approval_users';
            $role_column = 'role_id';
        } else {
            $table = 'debt_collection_users';
            $role_column = 'role_id';
        }
        
        // Kiểm tra xem user đã có role này chưa
        $existing = $db->fetchOne("SELECT id FROM {$table} WHERE user_id = ? AND {$role_column} = ?", [$user_id, $role_id]);
        
        if (!$existing) {
            $assignData = [
                'user_id' => $user_id,
                $role_column => $role_id,
                'status' => 'active'
            ];
            $db->insert($table, $assignData);
            
            echo json_encode([
                'success' => true,
                'message' => 'Phân quyền thành công'
            ]);
        } else {
            throw new Exception('Người dùng đã có vai trò này');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleRemoveUserRole($db) {
    try {
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        
        if (!$assignment_id || !$type) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }
        
        if ($type === 'loan') {
            $table = 'loan_approval_users';
        } else {
            $table = 'debt_collection_users';
        }
        
        $affected = $db->delete($table, 'id = ?', [$assignment_id]);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa phân quyền thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy phân quyền để xóa');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateWaiverLimits($db) {
    try {
        $year = intval($_POST['year'] ?? 0);
        $month = intval($_POST['month'] ?? 0);
        $level_1_limit = floatval($_POST['level_1_limit'] ?? 0);
        $level_2_limit = floatval($_POST['level_2_limit'] ?? 0);
        $level_3_limit = floatval($_POST['level_3_limit'] ?? 0);
        $limit_id = intval($_POST['limit_id'] ?? 0);
        
        if (!$year || !$month || !$level_1_limit || !$level_2_limit || !$level_3_limit) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }
        
        $limitData = [
            'year' => $year,
            'month' => $month,
            'level_1_limit' => $level_1_limit,
            'level_2_limit' => $level_2_limit,
            'level_3_limit' => $level_3_limit
        ];
        
        if ($limit_id) {
            // Update existing limit
            $affected = $db->update('waiver_approval_limits', $limitData, 'id = ?', ['id' => $limit_id]);
            $message = 'Cập nhật giới hạn phê duyệt thành công';
        } else {
            // Check if limit already exists for this month/year
            $existing = $db->fetchOne("SELECT id FROM waiver_approval_limits WHERE year = ? AND month = ?", [$year, $month]);
            
            if ($existing) {
                $affected = $db->update('waiver_approval_limits', $limitData, 'id = ?', ['id' => $existing['id']]);
                $message = 'Cập nhật giới hạn phê duyệt thành công';
            } else {
                $db->insert('waiver_approval_limits', $limitData);
                $message = 'Tạo giới hạn phê duyệt thành công';
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteWaiverLimits($db) {
    try {
        $limit_id = intval($_POST['limit_id'] ?? 0);
        
        if (!$limit_id) {
            throw new Exception('ID giới hạn không hợp lệ');
        }
        
        $affected = $db->delete('waiver_approval_limits', 'id = ?', [$limit_id]);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa giới hạn phê duyệt thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy giới hạn để xóa');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?> 