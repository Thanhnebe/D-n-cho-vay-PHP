<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Trang quản lý hợp đồng vay - lấy dữ liệu từ database
$db = getDB();

// Xử lý tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;

$where = [];
$params = [];

if ($search) {
    $where[] = "(c.contract_code LIKE ? OR cu.name LIKE ? OR cu.phone LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($status) {
    $where[] = "c.status = ?";
    $params[] = $status;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Đếm tổng số hợp đồng sau khi lọc/tìm kiếm
$totalResult = $db->fetchOne("SELECT COUNT(*) as cnt FROM contracts c JOIN customers cu ON c.customer_id = cu.id $whereSql", $params);
$totalContracts = $totalResult['cnt'] ?? 0;
$totalPages = max(1, ceil($totalContracts / $perPage));
$offset = ($page - 1) * $perPage;

// Lấy danh sách hợp đồng phân trang
$sql = "
    SELECT
        c.id,
        c.contract_code,
        cu.name AS customer_name,
        cu.phone AS customer_phone,
        cu.cif AS customer_cif,
        a.name AS asset_name,
        ac.name AS asset_category,
        a.estimated_value AS asset_value,
        c.amount,
        c.monthly_rate,
        c.daily_rate,
        c.start_date,
        c.end_date,
        c.status,
        c.remaining_balance,
        c.total_paid,
        c.total_interest,
        c.notes,
        c.created_at,
        c.updated_at
    FROM contracts c
    JOIN customers cu ON c.customer_id = cu.id
    LEFT JOIN assets a ON c.asset_id = a.id
    LEFT JOIN asset_categories ac ON a.category_id = ac.id
    $whereSql
    ORDER BY c.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$contracts = $db->fetchAll($sql, $params);

// Thống kê tổng thể (không phân trang)
$allContracts = $db->fetchAll('SELECT status, remaining_balance FROM contracts');
$activeContracts = count(array_filter($allContracts, fn($c) => $c['status'] === 'active'));
$overdueContracts = count(array_filter($allContracts, fn($c) => $c['status'] === 'overdue'));
$totalDebt = array_sum(array_column($allContracts, 'remaining_balance'));

function statusLabel($status)
{
    switch ($status) {
        case 'active':
            return '<span class="status-badge status-normal">Hoạt động</span>';
        case 'overdue':
            return '<span class="status-badge status-overdue">Quá hạn</span>';
        case 'warning':
            return '<span class="status-badge status-warning">Cảnh báo</span>';
        case 'closed':
            return '<span class="status-badge">Đã đóng</span>';
        case 'defaulted':
            return '<span class="status-badge status-danger">Vỡ nợ</span>';
        default:
            return '<span class="status-badge">Khác</span>';
    }
}

function remainingIcon($status, $end_date)
{
    $now = new DateTime();
    $end = new DateTime($end_date);
    $diff = $now->diff($end);
    if ($status === 'overdue') return '<i class="fas fa-exclamation-triangle" style="color:#dc3545"></i>';
    if ($status === 'warning') return '<i class="fas fa-exclamation-circle" style="color:#ffc107"></i>';
    return '<i class="fas fa-calendar" style="color:#2563eb"></i>';
}

function formatCurrencyVND($amount)
{
    return number_format($amount, 0, ',', '.') . ' VND';
}

function getRemainingText($end_date, $status)
{
    $now = new DateTime();
    $end = new DateTime($end_date);
    $diff = $now->diff($end);
    if ($status === 'overdue') {
        return 'Quá ' . $diff->days . ' ngày';
    } else {
        return 'Còn ' . $diff->days . ' ngày';
    }
}
?>

<div class="contracts-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="font-size:2rem;font-weight:700;">Quản lý hợp đồng</h1>
        <div style="color:#666;">Danh sách và chi tiết các hợp đồng cho vay</div>
    </div>
    <button class="btn btn-primary" style="padding:10px 20px;font-size:1rem;" onclick="window.location.href='?page=contracts&action=create'">
        <i class="fas fa-plus"></i> Tạo hợp đồng mới
    </button>
</div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;">
    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng hợp đồng</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $totalContracts; ?></div>
        <div style="color:#2563eb;font-size:0.95rem;">+2 hợp đồng mới</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Đang hoạt động</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $activeContracts; ?></div>
        <div style="color:#10b981;font-size:0.95rem;">Hoạt động tốt</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Quá hạn</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $overdueContracts; ?></div>
        <div style="color:#dc3545;font-size:0.95rem;">Cần xử lý</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng nợ</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo formatCurrencyVND($totalDebt); ?></div>
        <div style="color:#f59e0b;font-size:0.95rem;">Cần thu hồi</div>
    </div>
</div>

<!-- Search and Filter -->
<div style="background:#fff;padding:24px;border-radius:12px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <form method="GET" action="" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:16px;align-items:end;">
        <input type="hidden" name="page" value="contracts">

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Mã hợp đồng, tên khách hàng, số điện thoại..."
                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Trạng thái</label>
            <select name="status" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>>Quá hạn</option>
                <option value="warning" <?php echo $status === 'warning' ? 'selected' : ''; ?>>Cảnh báo</option>
                <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Đã đóng</option>
                <option value="defaulted" <?php echo $status === 'defaulted' ? 'selected' : ''; ?>>Vỡ nợ</option>
            </select>
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Sắp xếp</label>
            <select name="sort" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="newest">Mới nhất</option>
                <option value="oldest">Cũ nhất</option>
                <option value="amount_high">Số tiền cao</option>
                <option value="amount_low">Số tiền thấp</option>
            </select>
        </div>

        <button type="submit" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </form>
</div>

<!-- Contracts Table -->
<div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <div style="padding:24px;border-bottom:1px solid #eee;">
        <h3 style="margin:0;font-size:1.25rem;font-weight:600;">Danh sách hợp đồng (<?php echo $totalContracts; ?> hợp đồng)</h3>
    </div>

    <?php if (empty($contracts)): ?>
        <div style="padding:48px;text-align:center;color:#666;">
            <i class="fas fa-file-contract" style="font-size:3rem;margin-bottom:16px;color:#ddd;"></i>
            <p>Không tìm thấy hợp đồng nào</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Mã HĐ</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Khách hàng</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Số tiền</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Lãi suất</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Ngày bắt đầu</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Ngày kết thúc</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Trạng thái</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Số dư</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $contract): ?>
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:16px;">
                                <div style="font-weight:600;color:#2563eb;"><?php echo htmlspecialchars($contract['contract_code']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">ID: <?php echo $contract['id']; ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo htmlspecialchars($contract['customer_name']); ?></div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo htmlspecialchars($contract['customer_phone']); ?></div>
                                <?php if ($contract['customer_cif']): ?>
                                    <div style="font-size:0.875rem;color:#666;">CIF: <?php echo htmlspecialchars($contract['customer_cif']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo formatCurrencyVND($contract['amount']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">Đã trả: <?php echo formatCurrencyVND($contract['total_paid']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo $contract['monthly_rate']; ?>%/tháng</div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo $contract['daily_rate']; ?>%/ngày</div>
                            </td>
                            <td style="padding:16px;">
                                <div><?php echo format_date($contract['start_date']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div><?php echo format_date($contract['end_date']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">
                                    <?php echo getRemainingText($contract['end_date'], $contract['status']); ?>
                                </div>
                            </td>
                            <td style="padding:16px;">
                                <?php echo statusLabel($contract['status']); ?>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;color:#dc3545;"><?php echo formatCurrencyVND($contract['remaining_balance']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">Lãi: <?php echo formatCurrencyVND($contract['total_interest']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="display:flex;gap:8px;">
                                    <button onclick="viewContract(<?php echo $contract['id']; ?>)"
                                        style="padding:6px 12px;background:#2563eb;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editContract(<?php echo $contract['id']; ?>)"
                                        style="padding:6px 12px;background:#10b981;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteContract(<?php echo $contract['id']; ?>)"
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
                        <a href="?page=contracts&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#2563eb;">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li>
                        <a href="?page=contracts&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:<?php echo $i === $page ? '#fff' : '#2563eb'; ?>;background:<?php echo $i === $page ? '#2563eb' : 'transparent'; ?>;">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li>
                        <a href="?page=contracts&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>"
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
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-normal {
        background: #d1fae5;
        color: #065f46;
    }

    .status-overdue {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-danger {
        background: #fecaca;
        color: #dc2626;
    }
</style>

<script>
    function viewContract(id) {
        window.location.href = '?page=contracts&action=view&id=' + id;
    }

    function editContract(id) {
        window.location.href = '?page=contracts&action=edit&id=' + id;
    }

    function deleteContract(id) {
        if (confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')) {
            window.location.href = '?page=contracts&action=delete&id=' + id;
        }
    }
</script>