<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// API endpoint cho leader management
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
    case 'get_leader':
        handleGetLeader($db);
        break;
    case 'get_leaders':
        handleGetLeaders($db);
        break;
    case 'create_leader':
        handleCreateLeader($db);
        break;
    case 'update_leader':
        handleUpdateLeader($db);
        break;
    case 'delete_leader':
        handleDeleteLeader($db);
        break;
    case 'reset_password':
        handleResetPassword($db);
        break;
    case 'get_departments':
        handleGetDepartments($db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleGetLeader($db)
{
    try {
        $user_id = intval($_GET['id'] ?? 0);

        if (!$user_id) {
            throw new Exception('ID leader không hợp lệ');
        }

        $leader = $db->fetchOne("
            SELECT 
                u.*,
                ud.role_in_department,
                ud.department_id,
                d.name as department_name
            FROM users u 
            LEFT JOIN user_departments ud ON u.id = ud.user_id
            LEFT JOIN departments d ON ud.department_id = d.id
            WHERE u.id = ?
        ", [$user_id]);

        if ($leader) {
            echo json_encode([
                'success' => true,
                'data' => $leader
            ]);
        } else {
            throw new Exception('Không tìm thấy leader');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetLeaders($db)
{
    try {
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $department = $_GET['department'] ?? '';
        $limit = intval($_GET['limit'] ?? 50);

        $where_conditions = [];
        $params = [];

        if ($search) {
            $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR u.department LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($role) {
            $where_conditions[] = "u.role = ?";
            $params[] = $role;
        }

        if ($status) {
            $where_conditions[] = "u.status = ?";
            $params[] = $status;
        }

        if ($department) {
            $where_conditions[] = "u.department = ?";
            $params[] = $department;
        }

        $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $leaders = $db->fetchAll("
            SELECT 
                u.*,
                ud.role_in_department,
                ud.department_id,
                d.name as department_name
            FROM users u 
            LEFT JOIN user_departments ud ON u.id = ud.user_id
            LEFT JOIN departments d ON ud.department_id = d.id
            {$where_clause} 
            ORDER BY u.created_at DESC 
            LIMIT {$limit}
        ", $params);

        echo json_encode([
            'success' => true,
            'data' => $leaders
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleCreateLeader($db)
{
    try {
        $leaderData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'username' => $_POST['username'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => $_POST['role'],
            'department' => $_POST['department'],
            'status' => $_POST['status']
        ];

        // Validate required fields
        if (!$leaderData['name'] || !$leaderData['email'] || !$leaderData['password'] || !$leaderData['role']) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Kiểm tra email đã tồn tại
        $existing_email = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$leaderData['email']]);
        if ($existing_email) {
            throw new Exception('Email đã tồn tại trong hệ thống');
        }

        // Kiểm tra username đã tồn tại
        if ($leaderData['username']) {
            $existing_username = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$leaderData['username']]);
            if ($existing_username) {
                throw new Exception('Username đã tồn tại trong hệ thống');
            }
        }

        $user_id = $db->insert('users', $leaderData);

        // Tạo liên kết với phòng ban nếu có
        if ($_POST['department_id']) {
            $departmentData = [
                'user_id' => $user_id,
                'department_id' => intval($_POST['department_id']),
                'role_in_department' => $_POST['role_in_department'],
                'status' => 'active'
            ];
            $db->insert('user_departments', $departmentData);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Tạo leader thành công',
            'user_id' => $user_id
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleUpdateLeader($db)
{
    try {
        $user_id = intval($_POST['user_id'] ?? 0);

        if (!$user_id) {
            throw new Exception('ID leader không hợp lệ');
        }

        $leaderData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'username' => $_POST['username'],
            'role' => $_POST['role'],
            'department' => $_POST['department'],
            'status' => $_POST['status']
        ];

        // Validate required fields
        if (!$leaderData['name'] || !$leaderData['email'] || !$leaderData['role']) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Kiểm tra email đã tồn tại (trừ user hiện tại)
        $existing_email = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$leaderData['email'], $user_id]);
        if ($existing_email) {
            throw new Exception('Email đã tồn tại trong hệ thống');
        }

        // Kiểm tra username đã tồn tại (trừ user hiện tại)
        if ($leaderData['username']) {
            $existing_username = $db->fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$leaderData['username'], $user_id]);
            if ($existing_username) {
                throw new Exception('Username đã tồn tại trong hệ thống');
            }
        }

        // Cập nhật mật khẩu nếu có
        if (!empty($_POST['password'])) {
            $leaderData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $affected = $db->update('users', $leaderData, 'id = ?', ['id' => $user_id]);

        if ($affected > 0) {
            // Cập nhật liên kết phòng ban
            if ($_POST['department_id']) {
                // Xóa liên kết cũ
                $db->delete('user_departments', 'user_id = ?', [$user_id]);

                // Tạo liên kết mới
                $departmentData = [
                    'user_id' => $user_id,
                    'department_id' => intval($_POST['department_id']),
                    'role_in_department' => $_POST['role_in_department'],
                    'status' => 'active'
                ];
                $db->insert('user_departments', $departmentData);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật leader thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy leader để cập nhật');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDeleteLeader($db)
{
    try {
        $user_id = intval($_POST['user_id'] ?? 0);

        if (!$user_id) {
            throw new Exception('ID leader không hợp lệ');
        }

        // Kiểm tra xem có phải admin không
        $user = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$user_id]);
        if ($user && $user['role'] === 'admin') {
            throw new Exception('Không thể xóa tài khoản admin');
        }

        // Xóa liên kết phòng ban
        $db->delete('user_departments', 'user_id = ?', [$user_id]);

        // Xóa user
        $affected = $db->delete('users', 'id = ?', [$user_id]);

        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa leader thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy leader để xóa');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleResetPassword($db)
{
    try {
        $user_id = intval($_POST['user_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if (!$user_id) {
            throw new Exception('ID leader không hợp lệ');
        }

        if (!$password) {
            throw new Exception('Mật khẩu không được để trống');
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $affected = $db->update('users', ['password' => $hashed_password], 'id = ?', ['id' => $user_id]);

        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Đặt lại mật khẩu thành công'
            ]);
        } else {
            throw new Exception('Không tìm thấy leader để cập nhật');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleGetDepartments($db)
{
    try {
        $departments = $db->fetchAll("
            SELECT * FROM departments 
            WHERE status = 'active' 
            ORDER BY name
        ");

        echo json_encode([
            'success' => true,
            'data' => $departments
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
