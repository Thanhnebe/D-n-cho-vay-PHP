<?php

/**
 * Helper functions for VayCamCo application
 */

/**
 * Get notification count for current user
 */
function getNotificationCount()
{
    global $db;
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }

    try {
        // Initialize database connection if not exists
        if (!$db) {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
        }

        if (!$db) {
            return 0;
        }

        $count = $db->fetchOne("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND read_at IS NULL
        ", [$_SESSION['user_id']]);

        return $count ? $count['count'] : 0;
    } catch (Exception $e) {
        error_log('Error getting notification count: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get recent notifications for current user
 */
function getRecentNotifications($limit = 5)
{
    global $db;
    if (!isset($_SESSION['user_id'])) {
        return [];
    }

    try {
        // Initialize database connection if not exists
        if (!$db) {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
        }

        if (!$db) {
            return [];
        }

        $notifications = $db->fetchAll("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$_SESSION['user_id'], $limit]);

        return $notifications ?: [];
    } catch (Exception $e) {
        error_log('Error getting recent notifications: ' . $e->getMessage());
        return [];
    }
}





/**
 * Generate random string
 */
function generate_random_string($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/**
 * Check if user has permission
 */
function has_permission($permission)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Add your permission logic here
    return true; // Temporary return true
}

/**
 * Log activity
 */
function log_activity($action, $details = '')
{
    global $db;

    try {
        // Initialize database connection if not exists
        if (!$db) {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
        }

        if (!$db) {
            return false;
        }

        $data = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $db->insert('activity_logs', $data);
    } catch (Exception $e) {
        error_log('Error logging activity: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send notification
 */
function send_notification($user_id, $title, $message, $type = 'info')
{
    global $db;

    try {
        // Initialize database connection if not exists
        if (!$db) {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
        }

        if (!$db) {
            return false;
        }

        $data = [
            'user_id' => $user_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $db->insert('notifications', $data);
    } catch (Exception $e) {
        error_log('Error sending notification: ' . $e->getMessage());
        return false;
    }
}

/**
 * Validate email format
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number format
 */
function is_valid_phone($phone)
{
    // Vietnamese phone number format
    $pattern = '/^(0|\+84)(3[2-9]|5[689]|7[06-9]|8[1-689]|9[0-46-9])[0-9]{7}$/';
    return preg_match($pattern, $phone);
}

/**
 * Calculate loan interest
 */
function calculate_loan_interest($principal, $rate, $term_months)
{
    $monthly_rate = $rate / 100 / 12;
    $interest = $principal * $monthly_rate * $term_months;
    return $interest;
}

/**
 * Calculate monthly payment
 */
function calculate_monthly_payment($principal, $rate, $term_months)
{
    $monthly_rate = $rate / 100 / 12;
    if ($monthly_rate == 0) {
        return $principal / $term_months;
    }

    $payment = $principal * ($monthly_rate * pow(1 + $monthly_rate, $term_months)) /
        (pow(1 + $monthly_rate, $term_months) - 1);

    return $payment;
}

/**
 * Get application status badge class
 */
function get_status_badge_class($status)
{
    $classes = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'disbursed' => 'info',
        'draft' => 'secondary',
        'cancelled' => 'dark'
    ];

    return $classes[$status] ?? 'secondary';
}

/**
 * Get application status text
 */
function get_status_text($status)
{
    $texts = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Đã từ chối',
        'disbursed' => 'Đã giải ngân',
        'draft' => 'Nháp',
        'cancelled' => 'Đã hủy'
    ];

    return $texts[$status] ?? 'Không xác định';
}

/**
 * Check if current user is admin
 */
function is_admin()
{
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $adminRoles = ['admin', 'manager', 'director', 'ceo'];
    return in_array($_SESSION['user_role'], $adminRoles) || empty($_SESSION['user_role']);
}

/**
 * Get current page name
 */
function get_current_page()
{
    return $_GET['page'] ?? 'dashboard';
}

/**
 * Get breadcrumb data
 */
function get_breadcrumb()
{
    $page = get_current_page();
    $breadcrumbs = [
        'dashboard' => ['Tổng quan', 'fas fa-tachometer-alt'],
        'customers' => ['Khách hàng', 'fas fa-users'],
        'contracts' => ['Hợp đồng vay', 'fas fa-file-contract'],
        'assets' => ['Tài sản cầm cố', 'fas fa-gem'],
        'debt-collection' => ['Thu hồi nợ', 'fas fa-dollar-sign'],
        'waiver-tracking' => ['Miễn Giảm Lãi', 'fas fa-clipboard-list'],
        'loan-applications' => ['Đơn vay', 'fas fa-file-alt'],
        'leader-management' => ['Quản lý người cho vay', 'fas fa-user-tie'],
        'interest-rates' => ['Lãi suất', 'fas fa-percentage'],
        'permissions' => ['Phân quyền', 'fas fa-shield-alt']
    ];

    return $breadcrumbs[$page] ?? ['Trang', 'fas fa-home'];
}
