<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Trang quản lý thu hồi nợ
$db = getDB();

// Xử lý tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$activity_type = $_GET['activity_type'] ?? '';
$result = $_GET['result'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;

$where = [];
$params = [];

if ($search) {
    $where[] = "(la.application_code LIKE ? OR la.customer_name LIKE ? OR la.customer_phone_main LIKE ? OR dca.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($activity_type) {
    $where[] = "dca.activity_type = ?";
    $params[] = $activity_type;
}

if ($result) {
    $where[] = "dca.result = ?";
    $params[] = $result;
}

if ($date_from) {
    $where[] = "dca.activity_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where[] = "dca.activity_date <= ?";
    $params[] = $date_to;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Đếm tổng số hoạt động thu hồi nợ
$totalResult = $db->fetchOne("
    SELECT COUNT(*) as cnt 
    FROM debt_collection_activities dca
    JOIN loan_applications la ON dca.contract_id = la.id
    $whereSql
", $params);
$totalActivities = $totalResult['cnt'] ?? 0;
$totalPages = max(1, ceil($totalActivities / $perPage));
$offset = ($page - 1) * $perPage;

// Lấy danh sách hoạt động thu hồi nợ
$sql = "
    SELECT
        dca.id,
        dca.activity_type,
        dca.activity_date,
        dca.activity_time,
        dca.description,
        dca.result,
        dca.next_action,
        dca.next_action_date,
        dca.created_at,
        la.application_code AS contract_code,
        la.loan_amount AS amount,
        la.loan_amount AS remaining_balance,
        la.customer_name,
        la.customer_phone_main AS customer_phone,
        u.name AS created_by_name
    FROM debt_collection_activities dca
    JOIN loan_applications la ON dca.contract_id = la.id
    LEFT JOIN users u ON dca.created_by = u.id
    $whereSql
    ORDER BY dca.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$activities = $db->fetchAll($sql, $params);

// Thống kê tổng thể
$statsResult = $db->fetchAll("
    SELECT 
        activity_type,
        result,
        COUNT(*) as count
    FROM debt_collection_activities 
    GROUP BY activity_type, result
");

$stats = [
    'total' => 0,
    'successful' => 0,
    'unsuccessful' => 0,
    'pending' => 0,
    'rescheduled' => 0,
    'calls' => 0,
    'visits' => 0,
    'sms' => 0,
    'email' => 0,
    'legal' => 0,
    'other' => 0
];

foreach ($statsResult as $stat) {
    $stats['total'] += $stat['count'];
    $stats[$stat['result']] += $stat['count'];
    $stats[$stat['activity_type'] . 's'] += $stat['count'];
}

// Lấy danh sách hợp đồng quá hạn để tạo hoạt động mới
$overdueContracts = $db->fetchAll("
    SELECT 
        c.id,
        c.contract_code,
        c.remaining_balance,
        cu.name AS customer_name,
        cu.phone AS customer_phone,
        DATEDIFF(CURDATE(), c.end_date) as days_overdue
    FROM contracts c
    JOIN customers cu ON c.customer_id = cu.id
    WHERE c.status IN ('overdue', 'defaulted')
    ORDER BY c.remaining_balance DESC
    LIMIT 20
");

function getActivityTypeLabel($type)
{
    switch ($type) {
        case 'call':
            return '<span class="badge bg-primary">Gọi điện</span>';
        case 'visit':
            return '<span class="badge bg-success">Thăm viếng</span>';
        case 'sms':
            return '<span class="badge bg-info">SMS</span>';
        case 'email':
            return '<span class="badge bg-warning">Email</span>';
        case 'legal_notice':
            return '<span class="badge bg-danger">Thông báo pháp lý</span>';
        case 'other':
            return '<span class="badge bg-secondary">Khác</span>';
        default:
            return '<span class="badge bg-secondary">Khác</span>';
    }
}

function getResultLabel($result)
{
    switch ($result) {
        case 'successful':
            return '<span class="badge bg-success">Thành công</span>';
        case 'unsuccessful':
            return '<span class="badge bg-danger">Không thành công</span>';
        case 'pending':
            return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'rescheduled':
            return '<span class="badge bg-info">Đổi lịch</span>';
        default:
            return '<span class="badge bg-secondary">Khác</span>';
    }
}

function formatCurrencyVND($amount)
{
    return number_format($amount, 0, ',', '.') . ' VND';
}

function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime)
{
    return date('d/m/Y H:i', strtotime($datetime));
}
?>

<div class="debt-collection-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="font-size:2rem;font-weight:700;">Thu hồi nợ</h1>
        <div style="color:#666;">Quản lý hoạt động thu hồi nợ và theo dõi tiến độ</div>
    </div>

</div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;">
    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng hoạt động</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $stats['total']; ?></div>
        <div style="color:#2563eb;font-size:0.95rem;">Hoạt động thu hồi</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Thành công</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $stats['successful']; ?></div>
        <div style="color:#10b981;font-size:0.95rem;"><?php echo $stats['total'] > 0 ? round(($stats['successful'] / $stats['total']) * 100, 1) : 0; ?>% tỷ lệ</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Đang chờ</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $stats['pending']; ?></div>
        <div style="color:#f59e0b;font-size:0.95rem;">Cần xử lý</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Hợp đồng quá hạn</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo count($overdueContracts); ?></div>
        <div style="color:#dc3545;font-size:0.95rem;">Cần thu hồi</div>
    </div>
</div>

<!-- View Activity Modal -->
<div id="viewActivityModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:600px;max-width:90vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#2563eb;">
                <i class="fas fa-eye"></i> Chi tiết hoạt động thu hồi nợ
            </h3>
            <button onclick="hideViewActivityModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="viewActivityModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Edit Activity Modal -->
<div id="editActivityModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:700px;max-width:95vw;max-height:95vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#10b981;">
                <i class="fas fa-edit"></i> Chỉnh sửa hoạt động thu hồi nợ
            </h3>
            <button onclick="hideEditActivityModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="editActivityModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Add Activity Modal -->
<div id="addActivityModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:700px;max-width:95vw;max-height:95vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#2563eb;">
                <i class="fas fa-plus"></i> Thêm hoạt động thu hồi nợ
            </h3>
            <button onclick="hideAddActivityModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="addActivityModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div style="background:#fff;padding:24px;border-radius:12px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <form method="GET" action="" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:16px;align-items:end;">
        <input type="hidden" name="page" value="debt-collection">

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Mã HĐ, tên KH, SĐT, mô tả..."
                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Loại hoạt động</label>
            <select name="activity_type" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <option value="call" <?php echo $activity_type === 'call' ? 'selected' : ''; ?>>Gọi điện</option>
                <option value="visit" <?php echo $activity_type === 'visit' ? 'selected' : ''; ?>>Thăm viếng</option>
                <option value="sms" <?php echo $activity_type === 'sms' ? 'selected' : ''; ?>>SMS</option>
                <option value="email" <?php echo $activity_type === 'email' ? 'selected' : ''; ?>>Email</option>
                <option value="legal_notice" <?php echo $activity_type === 'legal_notice' ? 'selected' : ''; ?>>Thông báo pháp lý</option>
                <option value="other" <?php echo $activity_type === 'other' ? 'selected' : ''; ?>>Khác</option>
            </select>
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Kết quả</label>
            <select name="result" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <option value="successful" <?php echo $result === 'successful' ? 'selected' : ''; ?>>Thành công</option>
                <option value="unsuccessful" <?php echo $result === 'unsuccessful' ? 'selected' : ''; ?>>Không thành công</option>
                <option value="pending" <?php echo $result === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                <option value="rescheduled" <?php echo $result === 'rescheduled' ? 'selected' : ''; ?>>Đổi lịch</option>
            </select>
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Từ ngày</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Đến ngày</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
        </div>

        <button type="submit" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </form>
</div>

<!-- Activities Table -->
<div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <div style="padding:24px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:1.25rem;font-weight:600;">Danh sách hoạt động thu hồi nợ (<?php echo $totalActivities; ?> hoạt động)</h3>
        <button onclick="showAddActivityModal()" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
            <i class="fas fa-plus"></i> Thêm hoạt động
        </button>
    </div>

    <?php if (empty($activities)): ?>
        <div style="padding:48px;text-align:center;color:#666;">
            <i class="fas fa-phone" style="font-size:3rem;margin-bottom:16px;color:#ddd;"></i>
            <p>Không tìm thấy hoạt động thu hồi nợ nào</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Ngày</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Loại</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Hợp đồng</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Khách hàng</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Mô tả</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Kết quả</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Hành động tiếp theo</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo formatDate($activity['activity_date']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">
                                    <?php echo $activity['activity_time'] ? date('H:i', strtotime($activity['activity_time'])) : ''; ?>
                                </div>
                            </td>
                            <td style="padding:16px;">
                                <?php echo getActivityTypeLabel($activity['activity_type']); ?>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;color:#2563eb;"><?php echo htmlspecialchars($activity['contract_code']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">Dư nợ: <?php echo formatCurrencyVND($activity['remaining_balance']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo htmlspecialchars($activity['customer_name']); ?></div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo htmlspecialchars($activity['customer_phone']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="max-width:200px;word-wrap:break-word;"><?php echo htmlspecialchars($activity['description']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <?php echo getResultLabel($activity['result']); ?>
                            </td>
                            <td style="padding:16px;">
                                <?php if ($activity['next_action']): ?>
                                    <div style="font-size:0.875rem;"><?php echo htmlspecialchars($activity['next_action']); ?></div>
                                    <?php if ($activity['next_action_date']): ?>
                                        <div style="font-size:0.875rem;color:#666;"><?php echo formatDate($activity['next_action_date']); ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="font-size:0.875rem;color:#666;">-</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:16px;">
                                <div style="display:flex;gap:8px;">
                                    <button onclick="viewActivity(<?php echo $activity['id']; ?>)"
                                        style="padding:6px 12px;background:#2563eb;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editActivity(<?php echo $activity['id']; ?>)"
                                        style="padding:6px 12px;background:#10b981;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteActivity(<?php echo $activity['id']; ?>)"
                                        style="padding:6px 12px;background:#dc3545;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;margin-top:32px;">
        <nav>
            <ul style="display:flex;list-style:none;gap:8px;margin:0;padding:0;">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="?page=debt-collection&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&activity_type=<?php echo urlencode($activity_type); ?>&result=<?php echo urlencode($result); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#2563eb;">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li>
                        <a href="?page=debt-collection&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&activity_type=<?php echo urlencode($activity_type); ?>&result=<?php echo urlencode($result); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:<?php echo $i === $page ? '#fff' : '#2563eb'; ?>;background:<?php echo $i === $page ? '#2563eb' : 'transparent'; ?>;">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li>
                        <a href="?page=debt-collection&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&activity_type=<?php echo urlencode($activity_type); ?>&result=<?php echo urlencode($result); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#2563eb;">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>



<style>
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .bg-primary {
        background: #dbeafe;
        color: #1e40af;
    }

    .bg-success {
        background: #d1fae5;
        color: #065f46;
    }

    .bg-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .bg-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .bg-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .bg-secondary {
        background: #f3f4f6;
        color: #374151;
    }
</style>

<script>
    // View Activity Modal Functions
    function viewActivity(id) {
        showViewActivityModal(id);
    }

    function showViewActivityModal(id) {
        const modal = document.getElementById('viewActivityModal');
        const content = document.getElementById('viewActivityModalContent');

        // Show loading
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#2563eb;"></i><br><p>Đang tải thông tin...</p></div>';
        modal.style.display = 'flex';

        // Fetch activity details
        fetch(`pages/admin/api/debt-collection.php?action=get_detail&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showViewActivityForm(data.activity);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideViewActivityModal() {
        document.getElementById('viewActivityModal').style.display = 'none';
    }

    function showViewActivityForm(activity) {
        const content = document.getElementById('viewActivityModalContent');

        content.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <!-- Thông tin hoạt động -->
                <div style="background:#f0f9ff;padding:20px;border-radius:8px;border-left:4px solid #2563eb;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#2563eb;">
                        <i class="fas fa-info-circle"></i> Thông tin hoạt động
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:8px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Loại hoạt động:</strong>
                            <div style="margin-top:4px;">${getActivityTypeLabel(activity.activity_type)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ngày hoạt động:</strong>
                            <div style="color:#333;font-weight:500;">${formatDate(activity.activity_date)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Thời gian:</strong>
                            <div style="color:#333;font-weight:500;">${activity.activity_time || 'N/A'}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Kết quả:</strong>
                            <div style="margin-top:4px;">${getResultLabel(activity.result)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Mô tả:</strong>
                            <div style="color:#333;font-weight:500;line-height:1.4;">${activity.description || 'N/A'}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Hành động tiếp theo:</strong>
                            <div style="color:#333;font-weight:500;">${activity.next_action || 'N/A'}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ngày hành động tiếp theo:</strong>
                            <div style="color:#333;font-weight:500;">${activity.next_action_date ? formatDate(activity.next_action_date) : 'N/A'}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Thông tin hợp đồng -->
                <div style="background:#f0f9ff;padding:20px;border-radius:8px;border-left:4px solid #10b981;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#10b981;">
                        <i class="fas fa-file-contract"></i> Thông tin hợp đồng
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:8px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Mã hợp đồng:</strong>
                            <div style="color:#2563eb;font-weight:600;">${activity.contract_code}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Khách hàng:</strong>
                            <div style="color:#333;font-weight:500;">${activity.customer_name}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số điện thoại:</strong>
                            <div style="color:#333;font-weight:500;">${activity.customer_phone}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền hợp đồng:</strong>
                            <div style="color:#333;font-weight:500;">${formatCurrencyVND(activity.amount)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Dư nợ:</strong>
                            <div style="color:#dc3545;font-weight:600;">${formatCurrencyVND(activity.remaining_balance)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Người tạo:</strong>
                            <div style="color:#333;font-weight:500;">${activity.created_by_name || 'N/A'}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ngày tạo:</strong>
                            <div style="color:#333;font-weight:500;">${formatDateTime(activity.created_at)}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="display:flex;justify-content:flex-end;margin-top:24px;">
                <button onclick="hideViewActivityModal()" 
                    style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        `;
    }

    // Edit Activity Modal Functions
    function editActivity(id) {
        showEditActivityModal(id);
    }

    function showEditActivityModal(id) {
        const modal = document.getElementById('editActivityModal');
        const content = document.getElementById('editActivityModalContent');

        // Show loading
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#10b981;"></i><br><p>Đang tải thông tin...</p></div>';
        modal.style.display = 'flex';

        // Fetch activity details
        fetch(`pages/admin/api/debt-collection.php?action=get_detail&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showEditActivityForm(data.activity);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideEditActivityModal() {
        document.getElementById('editActivityModal').style.display = 'none';
    }

    function showEditActivityForm(activity) {
        const content = document.getElementById('editActivityModalContent');

        content.innerHTML = `
            <form id="editActivityForm" onsubmit="submitEditActivity(event, ${activity.id})">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <!-- Thông tin hoạt động -->
                    <div style="background:#f0f9ff;padding:20px;border-radius:8px;">
                        <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#2563eb;">
                            <i class="fas fa-edit"></i> Thông tin hoạt động
                        </h4>
                        
                        <div style="display:grid;grid-template-columns:1fr;gap:16px;">
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Loại hoạt động</label>
                                <select name="activity_type" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                    <option value="call" ${activity.activity_type === 'call' ? 'selected' : ''}>Gọi điện</option>
                                    <option value="visit" ${activity.activity_type === 'visit' ? 'selected' : ''}>Thăm viếng</option>
                                    <option value="sms" ${activity.activity_type === 'sms' ? 'selected' : ''}>SMS</option>
                                    <option value="email" ${activity.activity_type === 'email' ? 'selected' : ''}>Email</option>
                                    <option value="legal_notice" ${activity.activity_type === 'legal_notice' ? 'selected' : ''}>Thông báo pháp lý</option>
                                    <option value="other" ${activity.activity_type === 'other' ? 'selected' : ''}>Khác</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ngày hoạt động</label>
                                <input type="date" name="activity_date" value="${activity.activity_date}" required
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Thời gian</label>
                                <input type="time" name="activity_time" value="${activity.activity_time || ''}"
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Kết quả</label>
                                <select name="result" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                    <option value="successful" ${activity.result === 'successful' ? 'selected' : ''}>Thành công</option>
                                    <option value="unsuccessful" ${activity.result === 'unsuccessful' ? 'selected' : ''}>Không thành công</option>
                                    <option value="pending" ${activity.result === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                                    <option value="rescheduled" ${activity.result === 'rescheduled' ? 'selected' : ''}>Đổi lịch</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin bổ sung -->
                    <div style="background:#f0f9ff;padding:20px;border-radius:8px;">
                        <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#10b981;">
                            <i class="fas fa-plus"></i> Thông tin bổ sung
                        </h4>
                        
                        <div style="display:grid;grid-template-columns:1fr;gap:16px;">
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Mô tả</label>
                                <textarea name="description" rows="4" required
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;">${activity.description || ''}</textarea>
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Hành động tiếp theo</label>
                                <textarea name="next_action" rows="3"
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;">${activity.next_action || ''}</textarea>
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ngày hành động tiếp theo</label>
                                <input type="date" name="next_action_date" value="${activity.next_action_date || ''}"
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;">
                    <button type="button" onclick="hideEditActivityModal()"
                        style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" id="submitEditActivityBtn"
                        style="padding:12px 24px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        `;
    }

    function submitEditActivity(event, id) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('submitEditActivityBtn');
        const originalText = submitBtn.innerHTML;

        // Show loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('action', 'update');
        formData.append('id', id);

        fetch('pages/admin/api/debt-collection.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const content = document.getElementById('editActivityModalContent');
                    content.innerHTML = `
                    <div style="text-align:center;padding:40px;">
                        <div style="font-size:4rem;color:#10b981;margin-bottom:16px;">✅</div>
                        <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#10b981;">Cập nhật thành công!</h3>
                        <p style="color:#666;margin:16px 0;">Hoạt động thu hồi nợ đã được cập nhật thành công.</p>
                        <button onclick="hideEditActivityModal(); location.reload();" 
                            style="padding:12px 24px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                            <i class="fas fa-check"></i> Hoàn thành
                        </button>
                    </div>
                `;
                } else {
                    alert('❌ Lỗi: ' + data.error);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Có lỗi xảy ra: ' + error.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    // Add Activity Modal Functions
    function showAddActivityModal() {
        const modal = document.getElementById('addActivityModal');
        const content = document.getElementById('addActivityModalContent');

        // Show loading
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#2563eb;"></i><br><p>Đang tải danh sách hợp đồng...</p></div>';
        modal.style.display = 'flex';

        // Fetch loan applications for selection
        fetch('pages/admin/api/debt-collection.php?action=get_loan_applications')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAddActivityForm(data.applications);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideAddActivityModal() {
        document.getElementById('addActivityModal').style.display = 'none';
    }

    function showAddActivityForm(applications) {
        const content = document.getElementById('addActivityModalContent');

        const options = applications.map(app =>
            `<option value="${app.id}" data-customer="${app.customer_name}" data-phone="${app.customer_phone_main}" data-amount="${app.loan_amount}">
                ${app.application_code} - ${app.customer_name} (${formatCurrencyVND(app.loan_amount)})
            </option>`
        ).join('');

        content.innerHTML = `
            <form id="addActivityForm" onsubmit="submitAddActivity(event)">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <!-- Thông tin hợp đồng -->
                    <div style="background:#f0f9ff;padding:20px;border-radius:8px;">
                        <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#2563eb;">
                            <i class="fas fa-file-contract"></i> Chọn hợp đồng
                        </h4>
                        
                        <div style="margin-bottom:16px;">
                            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Hợp đồng <span style="color:#dc3545;">*</span></label>
                            <select name="contract_id" required onchange="showSelectedContractInfo(this)" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                <option value="">-- Chọn hợp đồng --</option>
                                ${options}
                            </select>
                        </div>
                        
                        <div id="selectedContractInfo" style="background:#e8f4fd;padding:16px;border-radius:8px;display:none;">
                            <!-- Contract info will be displayed here -->
                        </div>
                    </div>
                    
                    <!-- Thông tin hoạt động -->
                    <div style="background:#f0f9ff;padding:20px;border-radius:8px;">
                        <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#10b981;">
                            <i class="fas fa-plus"></i> Thông tin hoạt động
                        </h4>
                        
                        <div style="display:grid;grid-template-columns:1fr;gap:16px;">
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Loại hoạt động <span style="color:#dc3545;">*</span></label>
                                <select name="activity_type" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                    <option value="">-- Chọn loại hoạt động --</option>
                                    <option value="call">Gọi điện</option>
                                    <option value="visit">Thăm viếng</option>
                                    <option value="sms">SMS</option>
                                    <option value="email">Email</option>
                                    <option value="legal_notice">Thông báo pháp lý</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ngày hoạt động <span style="color:#dc3545;">*</span></label>
                                <input type="date" name="activity_date" required
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Thời gian</label>
                                <input type="time" name="activity_time"
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Kết quả <span style="color:#dc3545;">*</span></label>
                                <select name="result" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                    <option value="">-- Chọn kết quả --</option>
                                    <option value="successful">Thành công</option>
                                    <option value="unsuccessful">Không thành công</option>
                                    <option value="pending">Chờ xử lý</option>
                                    <option value="rescheduled">Đổi lịch</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top:24px;">
                    <div style="margin-bottom:16px;">
                        <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Mô tả <span style="color:#dc3545;">*</span></label>
                        <textarea name="description" rows="4" required placeholder="Nhập mô tả chi tiết về hoạt động thu hồi nợ..."
                            style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Hành động tiếp theo</label>
                            <textarea name="next_action" rows="3" placeholder="Nhập hành động tiếp theo (nếu có)..."
                                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
                        </div>
                        
                        <div>
                            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ngày hành động tiếp theo</label>
                            <input type="date" name="next_action_date"
                                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                        </div>
                    </div>
                </div>
                
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px;">
                    <button type="button" onclick="hideAddActivityModal()"
                        style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" id="submitAddActivityBtn"
                        style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                        <i class="fas fa-plus"></i> Thêm hoạt động
                    </button>
                </div>
            </form>
        `;
    }

    function showSelectedContractInfo(select) {
        const selectedOption = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('selectedContractInfo');

        if (selectedOption && selectedOption.value) {
            const customer = selectedOption.getAttribute('data-customer');
            const phone = selectedOption.getAttribute('data-phone');
            const amount = selectedOption.getAttribute('data-amount');

            infoDiv.innerHTML = `
                <div style="margin-bottom:8px;">
                    <strong style="color:#666;font-size:0.9rem;">Khách hàng:</strong>
                    <div style="color:#333;font-weight:500;">${customer}</div>
                </div>
                <div style="margin-bottom:8px;">
                    <strong style="color:#666;font-size:0.9rem;">Số điện thoại:</strong>
                    <div style="color:#333;font-weight:500;">${phone}</div>
                </div>
                <div>
                    <strong style="color:#666;font-size:0.9rem;">Số tiền hợp đồng:</strong>
                    <div style="color:#333;font-weight:500;">${formatCurrencyVND(amount)}</div>
                </div>
            `;
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }

    function submitAddActivity(event) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('submitAddActivityBtn');
        const originalText = submitBtn.innerHTML;

        // Show loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('action', 'create');

        fetch('pages/admin/api/debt-collection.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const content = document.getElementById('addActivityModalContent');
                    content.innerHTML = `
                    <div style="text-align:center;padding:40px;">
                        <div style="font-size:4rem;color:#2563eb;margin-bottom:16px;">✅</div>
                        <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#2563eb;">Thêm thành công!</h3>
                        <p style="color:#666;margin:16px 0;">Hoạt động thu hồi nợ đã được thêm thành công.</p>
                        <button onclick="hideAddActivityModal(); location.reload();" 
                            style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                            <i class="fas fa-check"></i> Hoàn thành
                        </button>
                    </div>
                `;
                } else {
                    alert('❌ Lỗi: ' + data.error);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Có lỗi xảy ra: ' + error.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    // Delete Activity Function
    function deleteActivity(id) {
        if (confirm('Bạn có chắc chắn muốn xóa hoạt động này?')) {
            fetch('pages/admin/api/debt-collection.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Xóa thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa hoạt động');
                });
        }
    }

    // Helper functions
    function getActivityTypeLabel(type) {
        const labels = {
            'call': '<span style="padding:4px 8px;background:#e3f2fd;color:#1976d2;border-radius:4px;font-size:0.875rem;"><i class="fas fa-phone"></i> Gọi điện</span>',
            'visit': '<span style="padding:4px 8px;background:#f3e5f5;color:#7b1fa2;border-radius:4px;font-size:0.875rem;"><i class="fas fa-home"></i> Thăm viếng</span>',
            'sms': '<span style="padding:4px 8px;background:#e8f5e8;color:#388e3c;border-radius:4px;font-size:0.875rem;"><i class="fas fa-sms"></i> SMS</span>',
            'email': '<span style="padding:4px 8px;background:#fff3e0;color:#f57c00;border-radius:4px;font-size:0.875rem;"><i class="fas fa-envelope"></i> Email</span>',
            'legal_notice': '<span style="padding:4px 8px;background:#ffebee;color:#d32f2f;border-radius:4px;font-size:0.875rem;"><i class="fas fa-gavel"></i> Thông báo pháp lý</span>',
            'other': '<span style="padding:4px 8px;background:#f5f5f5;color:#616161;border-radius:4px;font-size:0.875rem;"><i class="fas fa-ellipsis-h"></i> Khác</span>'
        };
        return labels[type] || type;
    }

    function getResultLabel(result) {
        const labels = {
            'successful': '<span style="padding:4px 8px;background:#e8f5e8;color:#388e3c;border-radius:4px;font-size:0.875rem;"><i class="fas fa-check"></i> Thành công</span>',
            'unsuccessful': '<span style="padding:4px 8px;background:#ffebee;color:#d32f2f;border-radius:4px;font-size:0.875rem;"><i class="fas fa-times"></i> Không thành công</span>',
            'pending': '<span style="padding:4px 8px;background:#fff3e0;color:#f57c00;border-radius:4px;font-size:0.875rem;"><i class="fas fa-clock"></i> Chờ xử lý</span>',
            'rescheduled': '<span style="padding:4px 8px;background:#e3f2fd;color:#1976d2;border-radius:4px;font-size:0.875rem;"><i class="fas fa-calendar-alt"></i> Đổi lịch</span>'
        };
        return labels[result] || result;
    }

    function formatCurrencyVND(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function formatDate(date) {
        return new Date(date).toLocaleDateString('vi-VN');
    }

    function formatDateTime(datetime) {
        return new Date(datetime).toLocaleString('vi-VN');
    }

    // Close modals when clicking outside
    document.getElementById('viewActivityModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideViewActivityModal();
        }
    });

    document.getElementById('editActivityModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideEditActivityModal();
        }
    });

    document.getElementById('addActivityModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideAddActivityModal();
        }
    });
</script>