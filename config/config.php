<?php
// Cấu hình hệ thống VayCamCo
define('SITE_NAME', 'VayCamCo - Hệ thống quản lý cho vay cầm cố');
define('SITE_URL', 'http://localhost/vaycamco');
define('ADMIN_EMAIL', 'admin@vaycamco.com');

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_NAME', 'vaycamco_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Cấu hình bảo mật
define('CSRF_TOKEN_SECRET', 'vaycamco_secret_key_2024');
define('SESSION_TIMEOUT', 3600); // 1 giờ

// Cấu hình upload
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Cấu hình phân trang
define('ITEMS_PER_PAGE', 20);

// Cấu hình tiền tệ
define('CURRENCY', 'VND');
define('CURRENCY_SYMBOL', '₫');

// Cấu hình lãi suất mặc định
define('DEFAULT_INTEREST_RATE', 1.5); // 1.5% mỗi tháng

// Cấu hình thời gian
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bật error reporting cho development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Khởi tạo session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hàm helper
function sanitize_input($data)
{
    if ($data === null) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function format_currency($amount)
{
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function format_date($date)
{
    return date('d/m/Y', strtotime($date));
}

function format_datetime($datetime)
{
    return date('d/m/Y H:i', strtotime($datetime));
}

function calculate_interest($principal, $rate, $months)
{
    return $principal * ($rate / 100) * $months;
}

function calculate_total_payment($principal, $interest)
{
    return $principal + $interest;
}
