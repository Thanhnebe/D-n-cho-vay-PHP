<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>

    <!-- Critical CSS - Load immediately -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- Non-critical CSS - Load asynchronously -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <!-- Critical JavaScript - Load in header -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap CSS -->
    <!-- Bootstrap Bundle JS (bao gồm Popper) -->

    <!-- Performance optimization -->
    <script>
        // Preload critical resources
        const criticalResources = [
            'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.vi.min.js',
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'
        ];

        // Load non-critical resources asynchronously
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Load resources when needed
        window.loadResources = async function() {
            try {
                await Promise.all(criticalResources.map(loadScript));
                console.log('All resources loaded successfully');
            } catch (error) {
                console.error('Error loading resources:', error);
            }
        };
    </script>
</head>

<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hand-holding-usd me-2"></i>
                $ VayCamCo Smart Management
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Tổng quan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=customers">
                            <i class="fas fa-users me-1"></i>Khách hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contracts">
                            <i class="fas fa-file-contract me-1"></i>Hợp đồng vay
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=assets">
                            <i class="fas fa-gem me-1"></i>Tài sản cầm cố
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=debt-collection">
                            <i class="fas fa-dollar-sign me-1"></i>Thu hồi nợ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=waiver-tracking">
                            <i class="fas fa-clipboard-list me-1"></i>Miễn Giảm Lãi
                        </a>
                    </li>
                </ul>

                <!-- User menu -->
                <?php if (isLoggedIn()): ?>
                    <ul class="navbar-nav">
                        <!-- Notifications -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <?php
                                $notificationCount = getNotificationCount();
                                if ($notificationCount > 0): ?>
                                    <span class="badge bg-danger"><?php echo $notificationCount; ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php
                                $notifications = getRecentNotifications(5);
                                foreach ($notifications as $notification): ?>
                                    <li><a class="dropdown-item" href="#"><?php echo htmlspecialchars($notification['message']); ?></a></li>
                                <?php endforeach; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Xem tất cả thông báo</a></li>
                            </ul>
                        </li>

                        <!-- User dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                                <li><a class="dropdown-item" href="index.php?page=settings"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                            </ul>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="d-flex">
        <div class="sidebar bg-dark">
            <div class="sidebar-header">
                <h5 class="text-white">Menu</h5>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=customers">
                        <i class="fas fa-users me-2"></i>Khách hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=contracts">
                        <i class="fas fa-file-contract me-2"></i>Hợp đồng vay
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=assets">
                        <i class="fas fa-gem me-2"></i>Tài sản cầm cố
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=debt-collection">
                        <i class="fas fa-dollar-sign me-2"></i>Thu hồi nợ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=waiver-tracking">
                        <i class="fas fa-clipboard-list me-2"></i>Miễn Giảm Lãi
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <div class="container-fluid">