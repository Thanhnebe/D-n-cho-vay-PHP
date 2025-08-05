<?php

/**
 * Authentication functions
 */

// Kiểm tra đăng nhập
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Yêu cầu đăng nhập
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: pages/login.php');
        exit();
    }
}

// Kiểm tra quyền admin
function isAdmin()
{
    if (!isLoggedIn()) {
        return false;
    }
    
    $adminRoles = ['admin', 'manager', 'director', 'ceo'];
    return isset($_SESSION['role']) && (in_array($_SESSION['role'], $adminRoles) || empty($_SESSION['role']));
}

// Yêu cầu quyền admin
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?error=access_denied');
        exit();
    }
}

// Đăng xuất
function logout()
{
    session_destroy();
    header('Location: pages/login.php');
    exit();
}

// Tạo session cho user
function createUserSession($user)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['name'] ?? $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'user';
    $_SESSION['last_activity'] = time();
}

// Kiểm tra session timeout
function checkSessionTimeout()
{
    $timeout = SESSION_TIMEOUT ?? 3600; // 1 giờ mặc định
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        logout();
    }
    $_SESSION['last_activity'] = time();
}

// Lấy thông tin user hiện tại
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDB();
    return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

// Kiểm tra quyền truy cập
function hasPermission($permission)
{
    if (!isLoggedIn()) {
        return false;
    }

    // Admin có tất cả quyền
    if (isAdmin()) {
        return true;
    }

    // Kiểm tra quyền cụ thể
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }

    // Có thể mở rộng để kiểm tra quyền từ database
    return true;
}
