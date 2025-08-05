<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kiểm tra session timeout
checkSessionTimeout();

// Xử lý logout
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    logout();
}

// Lấy trang hiện tại và loại trang
$page = $_GET['page'] ?? 'home';
$type = $_GET['type'] ?? 'customer'; // 'customer' hoặc 'admin'

// Định nghĩa các trang công khai (không cần đăng nhập)
$public_pages = ['login', 'home', 'search', 'services', 'about', 'contact', 'faq'];

// Định nghĩa các trang admin (cần đăng nhập)
$admin_pages = [
    'dashboard',
    'loan-applications',
    'customers',
    'assets',
    'interest-rates',
    'contract-details',
    'leader-management',
    'permissions',
    'notifications',
    'waiver-tracking',
    'debt-collection',
    'electronic-contracts',
    'loan-approvals',
    'contracts',
    'waiver-application-detail'
];

// Logic routing
if ($type === 'admin') {
    // Trang admin - yêu cầu đăng nhập
    if (!in_array($page, $public_pages)) {
        requireLogin();
    }

    // Kiểm tra quyền truy cập admin
    if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
        header('Location: index.php?type=customer&page=home');
        exit;
    }

    // Include header admin
    include 'includes/header.php';
?>

    <div class="d-flex">
        <!-- Sidebar Admin -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-grow-1">
            <?php
            // Xử lý đặc biệt cho loan-applications modular
            if ($page === 'loan-applications') {
                $page_file = "pages/admin/loan-applications/index.php";
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include 'pages/404.php';
                }
            } else {
                $page_file = "pages/admin/{$page}.php";
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include 'pages/404.php';
                }
            }
            ?>
        </div>
    </div>

<?php
    // Include footer admin
    include 'includes/footer.php';
} else {
    // Trang khách hàng - có thể công khai hoặc cần đăng nhập
    if (!in_array($page, $public_pages)) {
        // Kiểm tra đăng nhập cho các trang khách hàng cần thiết
        $customer_protected_pages = ['profile', 'my-loans', 'apply-loan'];
        if (in_array($page, $customer_protected_pages)) {
            requireLogin();
        }
    }

    // Routing cho trang khách hàng
    switch ($page) {
        case 'home':
            include 'pages/customer/home.php';
            break;

        case 'search':
            include 'pages/customer/search.php';
            break;

        case 'services':
            include 'pages/customer/services.php';
            break;

        case 'about':
            include 'pages/customer/about.php';
            break;

        case 'contact':
            include 'pages/customer/contact.php';
            break;

        case 'faq':
            include 'pages/customer/faq.php';
            break;

        case 'login':
            include 'pages/login.php';
            break;

        case 'profile':
            include 'pages/customer/profile.php';
            break;

        case 'my-loans':
            include 'pages/customer/my-loans.php';
            break;

        case 'apply-loan':
            include 'pages/customer/apply-loan.php';
            break;

        case 'loan-details':
            include 'pages/customer/loan-details.php';
            break;

        case 'calculator':
            include 'pages/customer/calculator.php';
            break;

        case 'register':
            include 'pages/customer/register.php';
            break;

        default:
            // Mặc định hiển thị trang chủ khách hàng
            include 'pages/customer/home.php';
            break;
    }
}
?>