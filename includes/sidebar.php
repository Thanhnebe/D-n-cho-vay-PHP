<?php if (isLoggedIn()): ?>
    <div class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh; position: fixed; left: 0; top: 70px; z-index: 1000;">
        <div class="p-3">
            <h6 class="text-uppercase text-muted mb-3">
                <i class="fas fa-bars me-2"></i>MENU CHÍNH
            </h6>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=dashboard">
                        <i class="fas fa-home me-2"></i>Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'customers' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=customers">
                        <i class="fas fa-user me-2"></i>Khách hàng
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'assets' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=assets">
                        <i class="fas fa-file-alt me-2"></i>Tài sản cầm cố
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'debt-collection' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=debt-collection">
                        <i class="fas fa-dollar-sign me-2"></i>Thu hồi nợ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'waiver-tracking' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=waiver-tracking">
                        <i class="fas fa-file-alt me-2"></i>Theo dõi đơn miễn giảm
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'electronic-contracts' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=electronic-contracts">
                        <i class="fas fa-file-alt me-2"></i>Hợp đồng điện tử
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'loan-applications' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=loan-applications">
                        <i class="fas fa-file-alt me-2"></i>Đơn vay
                    </a>
                </li>
            </ul>

            <hr class="text-muted my-4">

            <h6 class="text-uppercase text-muted mb-3">
                <i class="fas fa-cog me-2"></i>QUẢN LÝ HỆ THỐNG
            </h6>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'interest-rates' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=interest-rates">
                        <i class="fas fa-chart-line me-2"></i>Cài đặt lãi suất
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'permissions' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=permissions">
                        <i class="fas fa-shield-alt me-2"></i>Phân quyền
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'settings' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=settings">
                        <i class="fas fa-cog me-2"></i>Cài đặt hệ thống
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'leader-management' ? 'active bg-primary' : ''; ?>" href="index.php?type=admin&page=leader-management">
                        <i class="fas fa-users me-2"></i>Quản lý Leader
                    </a>
                </li>
            </ul>

            <hr class="text-muted my-4">

            <h6 class="text-uppercase text-muted mb-3">
                <i class="fas fa-user me-2"></i>TÀI KHOẢN
            </h6>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'profile' ? 'active bg-primary' : ''; ?>" href="index.php?type=customer&page=profile">
                        <i class=" fas fa-user me-2"></i>Hồ sơ cá nhân
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'notifications' ? 'active bg-primary' : ''; ?>" href="index.php?type=customer&page=notifications">
                        <i class="fas fa-bell me-2"></i>Thông báo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] ?? '') === 'change-password' ? 'active bg-primary' : ''; ?>" href="index.php?type=customer&page=change-password">
                        <i class="fas fa-key me-2"></i>Đổi mật khẩu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?type=customer&page=logout" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?')">
                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main content wrapper -->
    <div class="main-content" style="margin-left: 250px; padding: 20px;">
    <?php else: ?>
        <div class="main-content" style="padding: 20px;">
        <?php endif; ?>

        <style>
            .sidebar {
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            }

            .sidebar .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 0.375rem;
                margin-bottom: 0.25rem;
                transition: all 0.3s ease;
            }

            .sidebar .nav-link:hover {
                background-color: rgba(255, 255, 255, 0.1) !important;
                color: white !important;
            }

            .sidebar .nav-link.active {
                background-color: #007bff !important;
                color: white !important;
            }

            .sidebar h6 {
                font-size: 0.75rem;
                font-weight: 600;
            }

            .sidebar hr {
                border-color: rgba(255, 255, 255, 0.2);
            }

            @media (max-width: 768px) {
                .sidebar {
                    position: fixed;
                    left: -250px;
                    transition: left 0.3s ease;
                }

                .sidebar.show {
                    left: 0;
                }

                .main-content {
                    margin-left: 0 !important;
                }
            }
        </style>

        <script>
            // Mobile sidebar toggle
            function toggleSidebar() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('show');
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.querySelector('.sidebar');
                const toggleBtn = document.querySelector('.sidebar-toggle');

                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        </script>