<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kiểm tra quyền truy cập
if (!isLoggedIn()) {
    header('Location: ?page=login');
    exit;
}

$db = getDB();
$currentUser = $_SESSION['user_id'] ?? 0;
$userRole = $_SESSION['user_role'] ?? '';
$userDepartment = $_SESSION['department_id'] ?? 0;

// Xử lý đánh dấu đã đọc
if (isset($_POST['mark_read'])) {
    $notificationId = intval($_POST['notification_id'] ?? 0);
    if ($notificationId) {
        $db->update(
            'notifications',
            ['is_read' => 1],
            'id = ? AND user_id = ?',
            [$notificationId, $currentUser]
        );
    }
}

// Lấy danh sách thông báo
$notifications = $db->fetchAll("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 100
", [$currentUser]);

// Đếm thông báo chưa đọc
$unreadCount = $db->fetchOne("
    SELECT COUNT(*) as cnt FROM notifications 
    WHERE user_id = ? AND is_read = 0
", [$currentUser])['cnt'] ?? 0;

// Hàm helper
function formatDateTime($datetime)
{
    return date('d/m/Y H:i', strtotime($datetime));
}

function getNotificationIcon($type)
{
    switch ($type) {
        case 'waiver_created':
            return 'fas fa-file-alt';
        case 'waiver_approved':
            return 'fas fa-check-circle';
        case 'waiver_rejected':
            return 'fas fa-times-circle';
        case 'approval_required':
            return 'fas fa-exclamation-triangle';
        case 'approval_complete':
            return 'fas fa-check-double';
        default:
            return 'fas fa-bell';
    }
}

function getNotificationColor($type)
{
    switch ($type) {
        case 'waiver_created':
            return 'primary';
        case 'waiver_approved':
            return 'success';
        case 'waiver_rejected':
            return 'danger';
        case 'approval_required':
            return 'warning';
        case 'approval_complete':
            return 'success';
        default:
            return 'info';
    }
}
?>

<!-- CSS cho trang -->
<style>
    .notifications-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }

    .notifications-header {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .notification-item {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border-left: 4px solid #2563eb;
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .notification-item.unread {
        border-left-color: #dc3545;
        background: #fff5f5;
    }

    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .notification-title {
        font-weight: 600;
        color: #333;
        font-size: 1.1rem;
        margin: 0;
    }

    .notification-time {
        color: #666;
        font-size: 0.875rem;
    }

    .notification-message {
        color: #555;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .notification-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: #2563eb;
        color: #fff;
    }

    .btn-secondary {
        background: #6c757d;
        color: #fff;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .bg-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .bg-success {
        background: #d1fae5;
        color: #065f46;
    }

    .bg-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .bg-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .bg-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .notifications-container {
            padding: 10px;
        }

        .notifications-header {
            padding: 16px;
        }

        .notification-item {
            padding: 16px;
        }

        .notification-header {
            flex-direction: column;
            gap: 8px;
        }

        .notification-actions {
            justify-content: flex-start;
        }
    }
</style>

<div class="notifications-container">
    <!-- Header -->
    <div class="notifications-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0;">Thông báo</h1>
                <p style="color: #666; margin: 8px 0 0 0;">
                    <?php echo $unreadCount; ?> thông báo chưa đọc
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn btn-secondary" onclick="markAllAsRead()">
                    <i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc
                </button>
                <button class="btn btn-primary" onclick="refreshNotifications()">
                    <i class="fas fa-sync"></i> Làm mới
                </button>
            </div>
        </div>
    </div>

    <!-- Danh sách thông báo -->
    <div class="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>Không có thông báo</h3>
                <p>Bạn chưa có thông báo nào</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>"
                    data-id="<?php echo $notification['id']; ?>">
                    <div class="notification-header">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="<?php echo getNotificationIcon($notification['type']); ?>"
                                style="color: #<?php echo getNotificationColor($notification['type']) === 'primary' ? '2563eb' : ($notification['type'] === 'waiver_approved' ? '10b981' : ($notification['type'] === 'waiver_rejected' ? 'dc3545' : 'f59e0b')); ?>; font-size: 1.2rem;"></i>
                            <div>
                                <h3 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h3>
                                <div class="notification-time"><?php echo formatDateTime($notification['created_at']); ?></div>
                            </div>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <span class="badge bg-danger">Mới</span>
                        <?php endif; ?>
                    </div>

                    <div class="notification-message">
                        <?php echo htmlspecialchars($notification['message']); ?>
                    </div>

                    <div class="notification-actions">
                        <?php if (!$notification['is_read']): ?>
                            <button class="btn btn-sm btn-secondary" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                <i class="fas fa-check"></i> Đánh dấu đã đọc
                            </button>
                        <?php endif; ?>

                        <?php if ($notification['related_type'] === 'waiver_application' && $notification['related_id']): ?>
                            <a href="?page=waiver-application-detail&id=<?php echo $notification['related_id']; ?>"
                                class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function markAsRead(notificationId) {
        fetch('pages/admin/api/waiver-notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_read&notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật UI
                    const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.classList.remove('unread');
                        const badge = notificationItem.querySelector('.badge');
                        if (badge) badge.remove();

                        // Cập nhật số thông báo chưa đọc
                        updateUnreadCount();
                    }
                }
            });
    }

    function markAllAsRead() {
        if (confirm('Bạn có chắc chắn muốn đánh dấu tất cả thông báo đã đọc?')) {
            // Gọi API đánh dấu tất cả đã đọc
            fetch('pages/admin/api/waiver-notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=mark_all_read'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật UI
                        document.querySelectorAll('.notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                            const badge = item.querySelector('.badge');
                            if (badge) badge.remove();
                        });

                        updateUnreadCount();
                        alert('Đã đánh dấu tất cả thông báo đã đọc');
                    }
                });
        }
    }

    function refreshNotifications() {
        location.reload();
    }

    function updateUnreadCount() {
        const unreadItems = document.querySelectorAll('.notification-item.unread').length;
        const countElement = document.querySelector('.notifications-header p');
        if (countElement) {
            countElement.textContent = `${unreadItems} thông báo chưa đọc`;
        }
    }

    // Auto refresh mỗi 30 giây
    setInterval(() => {
        // Chỉ refresh nếu có thông báo chưa đọc
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        if (unreadCount > 0) {
            fetch('pages/admin/api/waiver-notifications.php?action=get_notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Có thể cập nhật thông báo mới ở đây
                        console.log('Checked for new notifications');
                    }
                });
        }
    }, 30000);
</script>