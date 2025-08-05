<?php
// Top navigation bar
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-hand-holding-usd me-2"></i>
            <?php echo SITE_NAME; ?>
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
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=customers">
                        <i class="fas fa-users me-1"></i>Khách hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=contracts">
                        <i class="fas fa-file-contract me-1"></i>Hợp đồng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=assets">
                        <i class="fas fa-gem me-1"></i>Tài sản
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=payments">
                        <i class="fas fa-money-bill-wave me-1"></i>Thanh toán
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=reports">
                        <i class="fas fa-chart-bar me-1"></i>Báo cáo
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
                            $unread_count = get_unread_notifications_count($_SESSION['user_id']);
                            if ($unread_count > 0):
                            ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <h6 class="dropdown-header">Thông báo</h6>
                            <?php
                            $db = getDB();
                            $notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']]);
                            if (empty($notifications)):
                            ?>
                                <div class="dropdown-item text-muted">Không có thông báo mới</div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <a class="dropdown-item" href="#" onclick="markNotificationRead(<?php echo $notification['id']; ?>)">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-<?php echo $notification['type']; ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <div class="fw-bold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($notification['message']); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo format_datetime($notification['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="index.php?page=notifications">
                                    Xem tất cả thông báo
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>

                    <!-- User dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['email']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <h6 class="dropdown-header">Tài khoản</h6>
                            <a class="dropdown-item" href="index.php?page=profile">
                                <i class="fas fa-user me-2"></i>Hồ sơ
                            </a>
                            <a class="dropdown-item" href="index.php?page=settings">
                                <i class="fas fa-cog me-2"></i>Cài đặt
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="index.php?page=logout" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?')">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </div>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div style="height: 70px;"></div>

<script>
    // Mark notification as read
    function markNotificationRead(notificationId) {
        fetch('index.php?page=notifications&action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update notification count
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>