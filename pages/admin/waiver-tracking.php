<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Trang quản lý miễn giảm lãi
$db = getDB();

// Xử lý tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$waiver_type = $_GET['waiver_type'] ?? '';
$department_id = $_GET['department_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;

$where = [];
$params = [];

if ($search) {
    $where[] = "(iwa.application_code LIKE ? OR la.application_code LIKE ? OR la.customer_name LIKE ? OR la.customer_phone_main LIKE ? OR iwa.reason LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($status) {
    $where[] = "iwa.status = ?";
    $params[] = $status;
}

if ($waiver_type) {
    $where[] = "iwa.waiver_type = ?";
    $params[] = $waiver_type;
}

if ($department_id) {
    $where[] = "iwa.department_id = ?";
    $params[] = $department_id;
}

if ($date_from) {
    $where[] = "iwa.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if ($date_to) {
    $where[] = "iwa.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Đếm tổng số đơn miễn giảm
$totalResult = $db->fetchOne("
    SELECT COUNT(*) as cnt 
    FROM interest_waiver_applications iwa
    JOIN loan_applications la ON iwa.application_id = la.id
    LEFT JOIN departments d ON iwa.department_id = d.id
    $whereSql
", $params);
$totalApplications = $totalResult['cnt'] ?? 0;
$totalPages = max(1, ceil($totalApplications / $perPage));
$offset = ($page - 1) * $perPage;

// Lấy danh sách đơn miễn giảm
$sql = "
    SELECT
        iwa.id,
        iwa.application_code,
        iwa.waiver_amount,
        iwa.waiver_percentage,
        iwa.waiver_type,
        iwa.original_amount,
        iwa.remaining_amount_after_waiver,
        iwa.reason,
        iwa.status,
        iwa.current_approval_level,
        iwa.total_approval_levels,
        iwa.approved_amount,
        iwa.approved_percentage,
        iwa.final_decision,
        iwa.decision_date,
        iwa.decision_notes,
        iwa.created_at,
        iwa.updated_at,
        iwa.effective_expiry_date,
        iwa.detailed_reason,
        la.application_code AS loan_application_code,
        la.loan_amount,
        la.approved_amount AS loan_approved_amount,
        la.customer_name,
        la.customer_phone_main AS customer_phone,
        d.name AS department_name,
        u.name AS created_by_name
    FROM interest_waiver_applications iwa
    JOIN loan_applications la ON iwa.application_id = la.id
    LEFT JOIN departments d ON iwa.department_id = d.id
    LEFT JOIN users u ON iwa.created_by = u.id
    $whereSql
    ORDER BY iwa.created_at DESC
    LIMIT $perPage OFFSET $offset
";
$applications = $db->fetchAll($sql, $params);

// Thống kê tổng thể
$statsResult = $db->fetchAll("
    SELECT 
        status,
        waiver_type,
        COUNT(*) as count,
        SUM(waiver_amount) as total_amount,
        AVG(waiver_percentage) as avg_percentage
    FROM interest_waiver_applications 
    GROUP BY status, waiver_type
");

$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'cancelled' => 0,
    'total_amount' => 0,
    'approved_amount' => 0,
    'interest' => 0,
    'principal' => 0,
    'both' => 0,
    'fee' => 0
];

foreach ($statsResult as $stat) {
    $stats['total'] += $stat['count'];

    // Check if status is not empty before using as array key
    if (!empty($stat['status'])) {
        $stats[$stat['status']] += $stat['count'];
    }

    $stats['total_amount'] += $stat['total_amount'];

    // Check if waiver_type is not empty before using as array key
    if (!empty($stat['waiver_type'])) {
        $stats[$stat['waiver_type']] += $stat['count'];
    }

    if ($stat['status'] === 'approved') {
        $stats['approved_amount'] += $stat['total_amount'];
    }
}

// Lấy danh sách phòng ban
$departments = $db->fetchAll("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name");

// Lấy danh sách đơn vay để tạo đơn miễn giảm
$loanApplications = $db->fetchAll("
    SELECT 
        la.id,
        la.application_code,
        la.customer_id,
        la.asset_id,
        la.loan_amount,
        la.loan_purpose,
        la.loan_term_months,
        la.interest_rate_id,
        la.monthly_rate,
        la.daily_rate,
        la.customer_name,
        la.customer_cmnd,
        la.customer_address,
        la.customer_phone_main,
        la.customer_birth_date,
        la.customer_id_issued_place,
        la.customer_id_issued_date,
        la.customer_email,
        la.customer_job,
        la.customer_income,
        la.customer_company,
        la.asset_name,
        la.asset_quantity,
        la.asset_license_plate,
        la.asset_frame_number,
        la.asset_engine_number,
        la.asset_registration_number,
        la.asset_registration_date,
        la.asset_value,
        la.asset_condition,
        la.asset_brand,
        la.asset_model,
        la.asset_year,
        la.asset_color,
        la.asset_cc,
        la.asset_fuel_type,
        la.asset_description,
        la.emergency_contact_name,
        la.emergency_contact_phone,
        la.emergency_contact_relationship,
        la.emergency_contact_address,
        la.emergency_contact_note,
        la.has_life_insurance,
        la.has_health_insurance,
        la.has_vehicle_insurance,
        la.otp_code,
        la.otp_expires_at,
        la.otp_verified_at,
        la.status,
        la.current_approval_level,
        la.highest_approval_level,
        la.total_approval_levels,
        la.created_by,
        la.department_id,
        la.created_at,
        la.updated_at,
        la.final_decision,
        la.decision_date,
        la.approved_amount,
        la.decision_notes
    FROM loan_applications la
    WHERE la.status IN ('approved', 'disbursed')
    ORDER BY la.created_at DESC
    LIMIT 20
");

function getStatusLabel($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Chờ phê duyệt</span>';
        case 'approved':
            return '<span class="badge bg-success">Đã phê duyệt</span>';
        case 'rejected':
            return '<span class="badge bg-danger">Từ chối</span>';
        case 'cancelled':
            return '<span class="badge bg-secondary">Đã hủy</span>';
        default:
            return '<span class="badge bg-secondary">Khác</span>';
    }
}

function getWaiverTypeLabel($type)
{
    switch ($type) {
        case 'interest':
            return '<span class="badge bg-primary">Miễn lãi</span>';
        case 'principal':
            return '<span class="badge bg-info">Miễn gốc</span>';
        case 'both':
            return '<span class="badge bg-success">Miễn cả gốc và lãi</span>';
        case 'fee':
            return '<span class="badge bg-warning">Miễn phí</span>';
        default:
            return '<span class="badge bg-secondary">Khác</span>';
    }
}

function getApprovalLevelLabel($current, $total)
{
    $percentage = ($current / $total) * 100;
    $color = $percentage >= 100 ? 'success' : ($percentage >= 50 ? 'warning' : 'info');
    return "<span class='badge bg-{$color}'>{$current}/{$total}</span>";
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

function getProgressBar($current, $total)
{
    $percentage = ($current / $total) * 100;
    return "
        <div class='progress' style='height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;'>
            <div class='progress-bar' style='width:{$percentage}%;background:#2563eb;height:100%;'></div>
        </div>
        <small style='color:#666;font-size:0.75rem;'>Cấp {$current}/{$total}</small>
    ";
}
?>

<div class="waiver-tracking-header mt-5" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="font-size:2rem;font-weight:700;">Miễn giảm lãi</h1>
        <div style="color:#666;">Quản lý đơn xin miễn giảm lãi và phê duyệt</div>
    </div>
    <button class="btn btn-primary" style="padding:10px 20px;font-size:1rem;" onclick="showCreateApplicationModal()">
        <i class="fas fa-plus"></i> Tạo đơn miễn giảm
    </button>
    <a href="?page=waiver-tracking-comprehensive" class="btn btn-primary" style="padding:10px 20px;font-size:1rem;">
        <i class="fas fa-chart-bar"></i> Xem báo cáo
    </a>
</div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:32px;">
    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng đơn</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $stats['total']; ?></div>
        <div style="color:#2563eb;font-size:0.95rem;">Đơn xin miễn giảm</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Chờ phê duyệt</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $stats['pending']; ?></div>
        <div style="color:#f59e0b;font-size:0.95rem;">Cần xử lý</div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Đã phê duyệt</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo $stats['approved']; ?></div>
        <div style="color:#10b981;font-size:0.95rem;"><?php echo formatCurrencyVND($stats['approved_amount']); ?></div>
    </div>

    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng giá trị</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;"><?php echo formatCurrencyVND($stats['total_amount']); ?></div>
        <div style="color:#dc3545;font-size:0.95rem;">Giá trị miễn giảm</div>
    </div>
</div>

<!-- Search and Filter -->
<div style="background:#fff;padding:24px;border-radius:12px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <form method="GET" action="" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr auto;gap:16px;align-items:end;">
        <input type="hidden" name="page" value="waiver-tracking">

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Mã đơn, mã đơn vay, tên KH, SĐT..."
                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Trạng thái</label>
            <select name="status" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ phê duyệt</option>
                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Đã phê duyệt</option>
                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
            </select>
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Loại miễn giảm</label>
            <select name="waiver_type" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <option value="interest" <?php echo $waiver_type === 'interest' ? 'selected' : ''; ?>>Miễn lãi</option>
                <option value="principal" <?php echo $waiver_type === 'principal' ? 'selected' : ''; ?>>Miễn gốc</option>
                <option value="both" <?php echo $waiver_type === 'both' ? 'selected' : ''; ?>>Miễn cả gốc và lãi</option>
                <option value="fee" <?php echo $waiver_type === 'fee' ? 'selected' : ''; ?>>Miễn phí</option>
            </select>
        </div>

        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Phòng ban</label>
            <select name="department_id" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['id']; ?>" <?php echo $department_id == $dept['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
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

<!-- Applications Table -->
<div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <div style="padding:24px;border-bottom:1px solid #eee;">
        <h3 style="margin:0;font-size:1.25rem;font-weight:600;">Danh sách đơn miễn giảm (<?php echo $totalApplications; ?> đơn)</h3>
    </div>

    <?php if (empty($applications)): ?>
        <div style="padding:48px;text-align:center;color:#666;">
            <i class="fas fa-file-alt" style="font-size:3rem;margin-bottom:16px;color:#ddd;"></i>
            <p>Không tìm thấy đơn miễn giảm nào</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Mã đơn</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Đơn vay</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Khách hàng</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Loại</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Số tiền</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Trạng thái</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Tiến độ</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Ngày tạo</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:16px;">
                                <div style="font-weight:600;color:#2563eb;"><?php echo htmlspecialchars($app['application_code']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">ID: <?php echo $app['id']; ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo htmlspecialchars($app['loan_application_code']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">Số tiền: <?php echo formatCurrencyVND($app['loan_approved_amount'] ?: $app['loan_amount']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;"><?php echo htmlspecialchars($app['customer_name']); ?></div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo htmlspecialchars($app['customer_phone']); ?></div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo htmlspecialchars($app['department_name']); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <?php echo getWaiverTypeLabel($app['waiver_type']); ?>
                                <div style="font-size:0.875rem;color:#666;"><?php echo $app['waiver_percentage']; ?>%</div>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;color:#dc3545;"><?php echo formatCurrencyVND($app['waiver_amount']); ?></div>
                                <?php if ($app['approved_amount'] > 0): ?>
                                    <div style="font-size:0.875rem;color:#10b981;">Đã duyệt: <?php echo formatCurrencyVND($app['approved_amount']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:16px;">
                                <?php echo getStatusLabel($app['status']); ?>
                                <?php if ($app['final_decision']): ?>
                                    <div style="font-size:0.875rem;color:#666;"><?php echo ucfirst($app['final_decision']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:16px;">
                                <?php echo getApprovalLevelLabel($app['current_approval_level'], $app['total_approval_levels']); ?>
                                <?php echo getProgressBar($app['current_approval_level'], $app['total_approval_levels']); ?>
                            </td>
                            <td style="padding:16px;">
                                <div><?php echo formatDate($app['created_at']); ?></div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo date('H:i', strtotime($app['created_at'])); ?></div>
                            </td>
                            <td style="padding:16px;">
                                <div style="display:flex;gap:8px;">
                                    <button onclick="viewApplicationDetail(<?php echo $app['id']; ?>)"
                                        style="padding:6px 12px;background:#2563eb;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <?php if ($app['status'] === 'pending'): ?>
                                        <button onclick="approveApplication(<?php echo $app['id']; ?>)"
                                            style="padding:6px 12px;background:#10b981;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="rejectApplication(<?php echo $app['id']; ?>)"
                                            style="padding:6px 12px;background:#dc3545;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="editApplication(<?php echo $app['id']; ?>)"
                                        style="padding:6px 12px;background:#f59e0b;color:#fff;border:none;border-radius:4px;font-size:0.875rem;cursor:pointer;">
                                        <i class="fas fa-edit"></i>
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
                        <a href="?page=waiver-tracking&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&waiver_type=<?php echo urlencode($waiver_type); ?>&department_id=<?php echo urlencode($department_id); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#2563eb;">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li>
                        <a href="?page=waiver-tracking&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&waiver_type=<?php echo urlencode($waiver_type); ?>&department_id=<?php echo urlencode($department_id); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:<?php echo $i === $page ? '#fff' : '#2563eb'; ?>;background:<?php echo $i === $page ? '#2563eb' : 'transparent'; ?>;">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li>
                        <a href="?page=waiver-tracking&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&waiver_type=<?php echo urlencode($waiver_type); ?>&department_id=<?php echo urlencode($department_id); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>"
                            style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:#2563eb;">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- Create Application Modal -->
<div id="createApplicationModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:600px;max-width:90vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;">Tạo đơn miễn giảm lãi</h3>
            <button onclick="hideCreateApplicationModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <form id="createApplicationForm" method="POST" action="pages/admin/api/waiver-tracking.php" onsubmit="submitWaiverForm(event)">
            <input type="hidden" name="action" value="create">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Chọn đơn vay</label>
                    <select name="application_id" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;" onchange="showSelectedApplicationInfo()">
                        <option value="">-- Chọn đơn vay đã duyệt --</option>
                        <?php foreach ($loanApplications as $app): ?>
                            <option value="<?php echo $app['id']; ?>"
                                data-amount="<?php echo $app['approved_amount'] ?: $app['loan_amount']; ?>"
                                data-customer="<?php echo htmlspecialchars($app['customer_name']); ?>"
                                data-phone="<?php echo htmlspecialchars($app['customer_phone_main']); ?>">
                                <?php echo htmlspecialchars($app['application_code']); ?> -
                                <?php echo htmlspecialchars($app['customer_name']); ?>
                                (<?php echo formatCurrencyVND($app['approved_amount'] ?: $app['loan_amount']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="selectedApplicationInfo" style="margin-top:8px;padding:8px;background:#f8f9fa;border-radius:4px;display:none;">
                        <small style="color:#666;">
                            <strong>Thông tin đơn vay:</strong><br>
                            <span id="selectedCustomerInfo"></span><br>
                            <span id="selectedAmountInfo"></span>
                        </small>
                    </div>
                </div>

                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Phòng ban</label>
                    <select name="department_id" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                        <option value="">-- Chọn phòng ban --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Loại miễn giảm</label>
                    <select name="waiver_type" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                        <option value="">-- Chọn loại miễn giảm --</option>
                        <option value="interest">Miễn lãi</option>
                        <option value="principal">Miễn gốc</option>
                        <option value="both">Miễn cả gốc và lãi</option>
                        <option value="fee">Miễn phí</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Phần trăm miễn giảm (%)</label>
                    <input type="number" name="waiver_percentage" min="0" max="100" step="0.01" required
                        style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;"
                        oninput="calculateWaiverAmount()">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Số tiền miễn giảm (VND)</label>
                    <input type="text" name="waiver_amount" required placeholder="Nhập số tiền..."
                        style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;"
                        oninput="formatCurrency(this)" onblur="formatCurrency(this)">
                </div>

                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ngày hiệu lực</label>
                    <input type="date" name="effective_expiry_date" required
                        style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Lý do miễn giảm</label>
                <textarea name="reason" required rows="4" placeholder="Mô tả chi tiết lý do xin miễn giảm..."
                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Lý do chi tiết</label>
                <textarea name="detailed_reason" rows="3" placeholder="Lý do chi tiết bổ sung..."
                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ghi chú ngoại lệ</label>
                <textarea name="exception_notes" rows="2" placeholder="Ghi chú về ngoại lệ (nếu có)..."
                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" onclick="hideCreateApplicationModal()"
                    style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    Hủy
                </button>
                <button type="submit" id="submitWaiverBtn"
                    style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-save"></i> Tạo đơn
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Application Detail Modal -->
<div id="applicationDetailModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:800px;max-width:90vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;">Chi tiết đơn miễn giảm</h3>
            <button onclick="hideApplicationDetailModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="applicationDetailContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:600px;max-width:90vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#10b981;">
                <i class="fas fa-check-circle"></i> Phê duyệt đơn miễn giảm
            </h3>
            <button onclick="hideApprovalModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="approvalModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:600px;max-width:90vw;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#dc3545;">
                <i class="fas fa-times-circle"></i> Từ chối đơn miễn giảm
            </h3>
            <button onclick="hideRejectionModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="rejectionModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;min-width:800px;max-width:95vw;max-height:95vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
            <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#f59e0b;">
                <i class="fas fa-edit"></i> Chỉnh sửa đơn miễn giảm
            </h3>
            <button onclick="hideEditModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#666;">&times;</button>
        </div>

        <div id="editModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

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
    function showCreateApplicationModal() {
        document.getElementById('createApplicationModal').style.display = 'flex';
    }

    function hideCreateApplicationModal() {
        document.getElementById('createApplicationModal').style.display = 'none';
    }

    function viewApplicationDetail(id) {
        // Show loading
        const modal = document.getElementById('applicationDetailModal');
        const content = document.getElementById('applicationDetailContent');
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#2563eb;"></i><br><p>Đang tải dữ liệu...</p></div>';
        modal.style.display = 'flex';

        // Fetch application details
        fetch(`pages/admin/api/waiver-tracking.php?action=get_detail&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showApplicationDetail(data.application);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideApplicationDetailModal() {
        document.getElementById('applicationDetailModal').style.display = 'none';
    }

    function showApplicationDetail(app) {
        const content = document.getElementById('applicationDetailContent');

        const statusLabel = getStatusLabel(app.status);
        const waiverTypeLabel = getWaiverTypeLabel(app.waiver_type);
        const approvalLevelLabel = getApprovalLevelLabel(app.current_approval_level, app.total_approval_levels);

        content.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <!-- Thông tin cơ bản -->
                <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;border-bottom:2px solid #2563eb;padding-bottom:8px;">
                        <i class="fas fa-info-circle"></i> Thông tin cơ bản
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Mã đơn miễn giảm:</strong>
                            <div style="color:#2563eb;font-weight:600;font-size:1.1rem;">${app.application_code}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Mã đơn vay:</strong>
                            <div style="color:#333;font-weight:500;">${app.loan_application_code || 'N/A'}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Khách hàng:</strong>
                            <div style="color:#333;font-weight:500;">${app.customer_name}</div>
                            <div style="color:#666;font-size:0.875rem;">${app.customer_phone}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Phòng ban:</strong>
                            <div style="color:#333;font-weight:500;">${app.department_name || 'N/A'}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ngày tạo:</strong>
                            <div style="color:#333;font-weight:500;">${formatDateTime(app.created_at)}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Thông tin miễn giảm -->
                <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;border-bottom:2px solid #10b981;padding-bottom:8px;">
                        <i class="fas fa-percentage"></i> Thông tin miễn giảm
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Loại miễn giảm:</strong>
                            <div style="margin-top:4px;">${waiverTypeLabel}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Phần trăm miễn giảm:</strong>
                            <div style="color:#333;font-weight:500;">${app.waiver_percentage}%</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền gốc:</strong>
                            <div style="color:#333;font-weight:500;">${formatCurrencyVND(app.original_amount)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền miễn giảm:</strong>
                            <div style="color:#dc3545;font-weight:600;font-size:1.1rem;">${formatCurrencyVND(app.waiver_amount)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền còn lại:</strong>
                            <div style="color:#10b981;font-weight:500;">${formatCurrencyVND(app.remaining_amount_after_waiver)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ngày hiệu lực:</strong>
                            <div style="color:#333;font-weight:500;">${formatDate(app.effective_expiry_date)}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Trạng thái và phê duyệt -->
                <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;border-bottom:2px solid #f59e0b;padding-bottom:8px;">
                        <i class="fas fa-clipboard-check"></i> Trạng thái & Phê duyệt
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Trạng thái:</strong>
                            <div style="margin-top:4px;">${statusLabel}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Tiến độ phê duyệt:</strong>
                            <div style="margin-top:4px;">${approvalLevelLabel}</div>
                            ${getProgressBar(app.current_approval_level, app.total_approval_levels)}
                        </div>
                        
                        ${app.approved_amount ? `
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền đã duyệt:</strong>
                            <div style="color:#10b981;font-weight:600;">${formatCurrencyVND(app.approved_amount)}</div>
                        </div>
                        ` : ''}
                        
                        ${app.final_decision ? `
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Quyết định cuối:</strong>
                            <div style="color:#333;font-weight:500;text-transform:capitalize;">${app.final_decision}</div>
                        </div>
                        ` : ''}
                        
                        ${app.decision_date ? `
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ngày quyết định:</strong>
                            <div style="color:#333;font-weight:500;">${formatDate(app.decision_date)}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <!-- Lý do và ghi chú -->
                <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;border-bottom:2px solid #8b5cf6;padding-bottom:8px;">
                        <i class="fas fa-file-alt"></i> Lý do & Ghi chú
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:12px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Lý do miễn giảm:</strong>
                            <div style="color:#333;font-weight:500;margin-top:4px;line-height:1.5;">${app.reason}</div>
                        </div>
                        
                        ${app.detailed_reason ? `
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Lý do chi tiết:</strong>
                            <div style="color:#333;font-weight:500;margin-top:4px;line-height:1.5;">${app.detailed_reason}</div>
                        </div>
                        ` : ''}
                        
                        ${app.exception_notes ? `
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ghi chú ngoại lệ:</strong>
                            <div style="color:#333;font-weight:500;margin-top:4px;line-height:1.5;">${app.exception_notes}</div>
                        </div>
                        ` : ''}
                        
                        ${app.decision_notes ? `
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Ghi chú quyết định:</strong>
                            <div style="color:#333;font-weight:500;margin-top:4px;line-height:1.5;">${app.decision_notes}</div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <!-- Action buttons -->
            <div style="display:flex;gap:12px;justify-content:center;margin-top:24px;padding-top:24px;border-top:1px solid #eee;">
                <button onclick="hideApplicationDetailModal()" 
                    style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-times"></i> Đóng
                </button>
                
                ${app.status === 'pending' ? `
                <button onclick="approveApplication(${app.id})" 
                    style="padding:12px 24px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-check"></i> Phê duyệt
                </button>
                
                <button onclick="rejectApplication(${app.id})" 
                    style="padding:12px 24px;background:#dc3545;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-times"></i> Từ chối
                </button>
                ` : ''}
                
                <button onclick="editApplication(${app.id})" 
                    style="padding:12px 24px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </button>
            </div>
        `;
    }

    function editApplication(id) {
        // Show edit modal
        showEditModal(id);
    }

    function approveApplication(id) {
        // Show approval modal
        showApprovalModal(id);
    }

    function rejectApplication(id) {
        // Show rejection modal
        showRejectionModal(id);
    }

    // Close modal when clicking outside
    document.getElementById('createApplicationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideCreateApplicationModal();
        }
    });

    // Close detail modal when clicking outside
    document.getElementById('applicationDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideApplicationDetailModal();
        }
    });

    // Close approval modal when clicking outside
    document.getElementById('approvalModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideApprovalModal();
        }
    });

    // Close rejection modal when clicking outside
    document.getElementById('rejectionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideRejectionModal();
        }
    });

    // Close edit modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideEditModal();
        }
    });

    // Format currency function
    function formatCurrency(input) {
        // Remove all non-numeric characters except decimal point
        let value = input.value.replace(/[^\d]/g, '');

        // Convert to number
        let numValue = parseInt(value) || 0;

        // Format with commas
        let formattedValue = numValue.toLocaleString('vi-VN');

        // Update input value
        input.value = formattedValue;

        // Store numeric value in data attribute for form submission
        input.setAttribute('data-numeric-value', numValue);
    }

    // Calculate waiver amount based on percentage
    function calculateWaiverAmount() {
        const percentageInput = document.querySelector('input[name="waiver_percentage"]');
        const amountInput = document.querySelector('input[name="waiver_amount"]');
        const contractSelect = document.querySelector('select[name="application_id"]');

        if (percentageInput && amountInput && contractSelect) {
            const percentage = parseFloat(percentageInput.value) || 0;
            const selectedOption = contractSelect.options[contractSelect.selectedIndex];

            if (selectedOption && selectedOption.value) {
                // Extract amount from option text (format: "CODE - NAME (AMOUNT)")
                const optionText = selectedOption.text;
                const amountMatch = optionText.match(/\(([^)]+)\)/);

                if (amountMatch) {
                    const originalAmount = parseFloat(amountMatch[1].replace(/[^\d]/g, '')) || 0;
                    const waiverAmount = (originalAmount * percentage) / 100;

                    // Format the calculated amount
                    amountInput.value = waiverAmount.toLocaleString('vi-VN');
                    amountInput.setAttribute('data-numeric-value', waiverAmount);
                }
            }
        }
    }

    // Handle form submission to convert formatted values back to numbers
    document.getElementById('createApplicationForm').addEventListener('submit', function(e) {
        const amountInput = document.querySelector('input[name="waiver_amount"]');
        if (amountInput) {
            // Use the numeric value stored in data attribute
            const numericValue = amountInput.getAttribute('data-numeric-value') ||
                amountInput.value.replace(/[^\d]/g, '');
            amountInput.value = numericValue;
        }
    });

    // Show selected application info
    function showSelectedApplicationInfo() {
        const select = document.querySelector('select[name="application_id"]');
        const infoDiv = document.getElementById('selectedApplicationInfo');
        const customerInfo = document.getElementById('selectedCustomerInfo');
        const amountInfo = document.getElementById('selectedAmountInfo');

        if (select.value) {
            const selectedOption = select.options[select.selectedIndex];
            const customerName = selectedOption.getAttribute('data-customer');
            const customerPhone = selectedOption.getAttribute('data-phone');
            const amount = parseFloat(selectedOption.getAttribute('data-amount')) || 0;

            customerInfo.textContent = `Khách hàng: ${customerName} - ${customerPhone}`;
            amountInfo.textContent = `Số tiền gốc: ${amount.toLocaleString('vi-VN')} VND`;

            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }

    // Submit waiver form with AJAX
    function submitWaiverForm(event) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('submitWaiverBtn');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        const formData = new FormData(form);

        fetch('pages/admin/api/waiver-tracking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    showSuccessModal(data);
                } else {
                    // Show error alert
                    alert('❌ Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Có lỗi xảy ra: ' + error.message);
            })
            .finally(() => {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }

    // Show success modal with details
    function showSuccessModal(data) {
        const modalHtml = `
            <div id="successModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center;">
                <div style="background:#fff;padding:32px;border-radius:12px;min-width:500px;max-width:90vw;max-height:90vh;overflow-y:auto;">
                    <div style="text-align:center;margin-bottom:24px;">
                        <div style="font-size:4rem;color:#10b981;margin-bottom:16px;">✅</div>
                        <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#10b981;">${data.message}</h3>
                    </div>
                    
                    <div style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:24px;">
                        <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;">Chi tiết đơn miễn giảm:</h4>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            ${Object.entries(data.details).map(([key, value]) => `
                                <div style="padding:8px 0;border-bottom:1px solid #eee;">
                                    <strong style="color:#666;font-size:0.9rem;">${key}:</strong>
                                    <div style="color:#333;font-weight:500;margin-top:2px;">${value}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div style="display:flex;gap:12px;justify-content:center;">
                        <button onclick="closeSuccessModal()" 
                            style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                            Đóng
                        </button>
                        <button onclick="closeSuccessModalAndReload()" 
                            style="padding:12px 24px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                            <i class="fas fa-refresh"></i> Làm mới trang
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('successModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = document.getElementById('successModal');
        modal.style.display = 'flex';

        // Hide create application modal
        hideCreateApplicationModal();
    }

    // Close success modal
    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.remove();
        }
    }

    // Close success modal and reload page
    function closeSuccessModalAndReload() {
        closeSuccessModal();
        location.reload();
    }

    // Helper functions for modal
    function getStatusLabel(status) {
        switch (status) {
            case 'pending':
                return '<span class="badge bg-warning">Chờ phê duyệt</span>';
            case 'approved':
                return '<span class="badge bg-success">Đã phê duyệt</span>';
            case 'rejected':
                return '<span class="badge bg-danger">Từ chối</span>';
            case 'cancelled':
                return '<span class="badge bg-secondary">Đã hủy</span>';
            default:
                return '<span class="badge bg-secondary">Khác</span>';
        }
    }

    function getWaiverTypeLabel(type) {
        switch (type) {
            case 'interest':
                return '<span class="badge bg-primary">Miễn lãi</span>';
            case 'principal':
                return '<span class="badge bg-info">Miễn gốc</span>';
            case 'both':
                return '<span class="badge bg-success">Miễn cả gốc và lãi</span>';
            case 'fee':
                return '<span class="badge bg-warning">Miễn phí</span>';
            default:
                return '<span class="badge bg-secondary">Khác</span>';
        }
    }

    function getApprovalLevelLabel(current, total) {
        const percentage = (current / total) * 100;
        const color = percentage >= 100 ? 'success' : (percentage >= 50 ? 'warning' : 'info');
        return `<span class='badge bg-${color}'>${current}/${total}</span>`;
    }

    function formatCurrencyVND(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' VND';
    }

    function formatDate(date) {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString('vi-VN');
    }

    function formatDateTime(datetime) {
        if (!datetime) return 'N/A';
        return new Date(datetime).toLocaleString('vi-VN');
    }

    function getProgressBar(current, total) {
        const percentage = (current / total) * 100;
        return `
            <div class='progress' style='height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;margin-top:4px;'>
                <div class='progress-bar' style='width:${percentage}%;background:#2563eb;height:100%;'></div>
            </div>
            <small style='color:#666;font-size:0.75rem;'>Cấp ${current}/${total}</small>
        `;
    }

    // Approval modal functions
    function showApprovalModal(id) {
        const modal = document.getElementById('approvalModal');
        const content = document.getElementById('approvalModalContent');

        // Show loading
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#10b981;"></i><br><p>Đang tải thông tin đơn...</p></div>';
        modal.style.display = 'flex';

        // Fetch application details for approval
        fetch(`pages/admin/api/waiver-tracking.php?action=get_detail&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showApprovalForm(data.application);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideApprovalModal() {
        document.getElementById('approvalModal').style.display = 'none';
    }

    function showApprovalForm(app) {
        const content = document.getElementById('approvalModalContent');

        content.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
                <!-- Thông tin đơn miễn giảm -->
                <div style="background:#f0f9ff;padding:20px;border-radius:8px;border-left:4px solid #10b981;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#10b981;">
                        <i class="fas fa-info-circle"></i> Thông tin đơn miễn giảm
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr;gap:8px;">
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Mã đơn:</strong>
                            <div style="color:#10b981;font-weight:600;">${app.application_code}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Khách hàng:</strong>
                            <div style="color:#333;font-weight:500;">${app.customer_name}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Loại miễn giảm:</strong>
                            <div style="margin-top:4px;">${getWaiverTypeLabel(app.waiver_type)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền gốc:</strong>
                            <div style="color:#333;font-weight:500;">${formatCurrencyVND(app.original_amount)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Số tiền miễn giảm:</strong>
                            <div style="color:#dc3545;font-weight:600;">${formatCurrencyVND(app.waiver_amount)}</div>
                        </div>
                        
                        <div>
                            <strong style="color:#666;font-size:0.9rem;">Lý do:</strong>
                            <div style="color:#333;font-weight:500;line-height:1.4;">${app.reason}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form phê duyệt -->
                <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                    <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;">
                        <i class="fas fa-edit"></i> Thông tin phê duyệt
                    </h4>
                    
                    <form id="approvalForm" onsubmit="submitApproval(event, ${app.id})">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Số tiền phê duyệt (VND)</label>
                                <input type="text" name="approved_amount" value="${app.waiver_amount}" required
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;"
                                    oninput="formatCurrency(this)" onblur="formatCurrency(this)">
                            </div>
                            
                            <div>
                                <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Phần trăm phê duyệt (%)</label>
                                <input type="number" name="approved_percentage" value="${app.waiver_percentage}" min="0" max="100" step="0.01" required
                                    style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                            </div>
                        </div>
                        
                        <div style="margin-bottom:16px;">
                            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Cấp phê duyệt</label>
                            <select name="approval_level" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                <option value="1" ${app.current_approval_level == 1 ? 'selected' : ''}>Cấp 1 - Trưởng phòng</option>
                                <option value="2" ${app.current_approval_level == 2 ? 'selected' : ''}>Cấp 2 - Giám đốc</option>
                                <option value="3" ${app.current_approval_level == 3 ? 'selected' : ''}>Cấp 3 - Hội đồng</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom:24px;">
                            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ghi chú phê duyệt</label>
                            <textarea name="comments" rows="4" placeholder="Nhập ghi chú phê duyệt..."
                                style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
                        </div>
                        
                        <div style="display:flex;gap:12px;justify-content:flex-end;">
                            <button type="button" onclick="hideApprovalModal()"
                                style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                            <button type="submit" id="submitApprovalBtn"
                                style="padding:12px 24px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                                <i class="fas fa-check"></i> Phê duyệt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    function submitApproval(event, id) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('submitApprovalBtn');
        const originalText = submitBtn.innerHTML;

        // Show loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('action', 'approve');
        formData.append('id', id);

        fetch('pages/admin/api/waiver-tracking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const content = document.getElementById('approvalModalContent');
                    content.innerHTML = `
                    <div style="text-align:center;padding:40px;">
                        <div style="font-size:4rem;color:#10b981;margin-bottom:16px;">✅</div>
                        <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#10b981;">Phê duyệt thành công!</h3>
                        <p style="color:#666;margin:16px 0;">Đơn miễn giảm đã được phê duyệt thành công.</p>
                        <button onclick="hideApprovalModal(); location.reload();" 
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

    // Rejection modal functions
    function showRejectionModal(id) {
        const modal = document.getElementById('rejectionModal');
        const content = document.getElementById('rejectionModalContent');

        // Show loading
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#dc3545;"></i><br><p>Đang tải thông tin đơn...</p></div>';
        modal.style.display = 'flex';

        // Fetch application details for rejection
        fetch(`pages/admin/api/waiver-tracking.php?action=get_detail&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showRejectionForm(data.application);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideRejectionModal() {
        document.getElementById('rejectionModal').style.display = 'none';
    }

    function showRejectionForm(app) {
        const content = document.getElementById('rejectionModalContent');

        content.innerHTML = `
             <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
                 <!-- Thông tin đơn miễn giảm -->
                 <div style="background:#fef2f2;padding:20px;border-radius:8px;border-left:4px solid #dc3545;">
                     <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#dc3545;">
                         <i class="fas fa-info-circle"></i> Thông tin đơn miễn giảm
                     </h4>
                     
                     <div style="display:grid;grid-template-columns:1fr;gap:8px;">
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Mã đơn:</strong>
                             <div style="color:#dc3545;font-weight:600;">${app.application_code}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Khách hàng:</strong>
                             <div style="color:#333;font-weight:500;">${app.customer_name}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Loại miễn giảm:</strong>
                             <div style="margin-top:4px;">${getWaiverTypeLabel(app.waiver_type)}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Số tiền gốc:</strong>
                             <div style="color:#333;font-weight:500;">${formatCurrencyVND(app.original_amount)}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Số tiền miễn giảm:</strong>
                             <div style="color:#dc3545;font-weight:600;">${formatCurrencyVND(app.waiver_amount)}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Lý do:</strong>
                             <div style="color:#333;font-weight:500;line-height:1.4;">${app.reason}</div>
                         </div>
                     </div>
                 </div>
                 
                 <!-- Form từ chối -->
                 <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                     <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;">
                         <i class="fas fa-edit"></i> Thông tin từ chối
                     </h4>
                     
                     <form id="rejectionForm" onsubmit="submitRejection(event, ${app.id})">
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Lý do từ chối <span style="color:#dc3545;">*</span></label>
                             <select name="reason" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                 <option value="">-- Chọn lý do từ chối --</option>
                                 <option value="Không đủ điều kiện">Không đủ điều kiện</option>
                                 <option value="Thiếu hồ sơ">Thiếu hồ sơ</option>
                                 <option value="Thông tin không chính xác">Thông tin không chính xác</option>
                                 <option value="Vượt quá hạn mức">Vượt quá hạn mức</option>
                                 <option value="Không đúng quy định">Không đúng quy định</option>
                                 <option value="Lý do khác">Lý do khác</option>
                             </select>
                         </div>
                         
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Cấp từ chối</label>
                             <select name="rejection_level" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                 <option value="1" ${app.current_approval_level == 1 ? 'selected' : ''}>Cấp 1 - Trưởng phòng</option>
                                 <option value="2" ${app.current_approval_level == 2 ? 'selected' : ''}>Cấp 2 - Giám đốc</option>
                                 <option value="3" ${app.current_approval_level == 3 ? 'selected' : ''}>Cấp 3 - Hội đồng</option>
                             </select>
                         </div>
                         
                         <div style="margin-bottom:24px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ghi chú chi tiết</label>
                             <textarea name="comments" rows="4" placeholder="Nhập ghi chú chi tiết về lý do từ chối..."
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;"></textarea>
                         </div>
                         
                         <div style="display:flex;gap:12px;justify-content:flex-end;">
                             <button type="button" onclick="hideRejectionModal()"
                                 style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                                 <i class="fas fa-times"></i> Hủy
                             </button>
                             <button type="submit" id="submitRejectionBtn"
                                 style="padding:12px 24px;background:#dc3545;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                                 <i class="fas fa-times"></i> Từ chối
                             </button>
                         </div>
                     </form>
                 </div>
             </div>
         `;
    }

    function submitRejection(event, id) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('submitRejectionBtn');
        const originalText = submitBtn.innerHTML;

        // Show loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('action', 'reject');
        formData.append('id', id);

        fetch('pages/admin/api/waiver-tracking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const content = document.getElementById('rejectionModalContent');
                    content.innerHTML = `
                     <div style="text-align:center;padding:40px;">
                         <div style="font-size:4rem;color:#dc3545;margin-bottom:16px;">❌</div>
                         <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#dc3545;">Từ chối thành công!</h3>
                         <p style="color:#666;margin:16px 0;">Đơn miễn giảm đã được từ chối thành công.</p>
                         <button onclick="hideRejectionModal(); location.reload();" 
                             style="padding:12px 24px;background:#dc3545;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                             <i class="fas fa-times"></i> Hoàn thành
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

    // Edit modal functions
    function showEditModal(id) {
        const modal = document.getElementById('editModal');
        const content = document.getElementById('editModalContent');

        // Show loading
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#f59e0b;"></i><br><p>Đang tải thông tin đơn...</p></div>';
        modal.style.display = 'flex';

        // Fetch application details for editing
        fetch(`pages/admin/api/waiver-tracking.php?action=get_detail&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showEditForm(data.application);
                } else {
                    content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Lỗi: ${data.error}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `<div style="text-align:center;padding:40px;color:#dc3545;"><i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i><br><p>Có lỗi xảy ra khi tải dữ liệu</p></div>`;
            });
    }

    function hideEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function showEditForm(app) {
        const content = document.getElementById('editModalContent');

        content.innerHTML = `
             <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                 <!-- Thông tin hiện tại -->
                 <div style="background:#fef7ed;padding:20px;border-radius:8px;border-left:4px solid #f59e0b;">
                     <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#f59e0b;">
                         <i class="fas fa-info-circle"></i> Thông tin hiện tại
                     </h4>
                     
                     <div style="display:grid;grid-template-columns:1fr;gap:8px;">
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Mã đơn:</strong>
                             <div style="color:#f59e0b;font-weight:600;">${app.application_code}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Khách hàng:</strong>
                             <div style="color:#333;font-weight:500;">${app.customer_name}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Trạng thái:</strong>
                             <div style="margin-top:4px;">${getStatusLabel(app.status)}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Số tiền gốc:</strong>
                             <div style="color:#333;font-weight:500;">${formatCurrencyVND(app.original_amount)}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Số tiền miễn giảm hiện tại:</strong>
                             <div style="color:#f59e0b;font-weight:600;">${formatCurrencyVND(app.waiver_amount)}</div>
                         </div>
                         
                         <div>
                             <strong style="color:#666;font-size:0.9rem;">Lý do hiện tại:</strong>
                             <div style="color:#333;font-weight:500;line-height:1.4;">${app.reason}</div>
                         </div>
                     </div>
                 </div>
                 
                 <!-- Form chỉnh sửa -->
                 <div style="background:#f8f9fa;padding:20px;border-radius:8px;">
                     <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;">
                         <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                     </h4>
                     
                     <form id="editForm" onsubmit="submitEdit(event, ${app.id})">
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Loại miễn giảm</label>
                             <select name="waiver_type" required style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                                 <option value="interest_waiver" ${app.waiver_type === 'interest_waiver' ? 'selected' : ''}>Miễn giảm lãi suất</option>
                                 <option value="principal_waiver" ${app.waiver_type === 'principal_waiver' ? 'selected' : ''}>Miễn giảm gốc</option>
                                 <option value="fee_waiver" ${app.waiver_type === 'fee_waiver' ? 'selected' : ''}>Miễn giảm phí</option>
                                 <option value="penalty_waiver" ${app.waiver_type === 'penalty_waiver' ? 'selected' : ''}>Miễn giảm phạt</option>
                             </select>
                         </div>
                         
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Phần trăm miễn giảm (%)</label>
                             <input type="number" name="waiver_percentage" value="${app.waiver_percentage}" min="0" max="100" step="0.01" 
                                 oninput="calculateEditWaiverAmount()" required
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                         </div>
                         
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Số tiền miễn giảm (VND)</label>
                             <input type="text" name="waiver_amount" value="${formatCurrencyVND(app.waiver_amount)}" 
                                 oninput="formatCurrency(this)" onblur="formatCurrency(this)" required
                                 data-original-amount="${app.original_amount}"
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                         </div>
                         
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ngày hiệu lực</label>
                             <input type="date" name="effective_expiry_date" value="${app.effective_expiry_date}" required
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                         </div>
                         
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Lý do miễn giảm</label>
                             <textarea name="reason" rows="3" required
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;">${app.reason}</textarea>
                         </div>
                         
                         <div style="margin-bottom:16px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Lý do chi tiết</label>
                             <textarea name="detailed_reason" rows="3"
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;">${app.detailed_reason || ''}</textarea>
                         </div>
                         
                         <div style="margin-bottom:24px;">
                             <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Ghi chú ngoại lệ</label>
                             <textarea name="exception_notes" rows="2"
                                 style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;resize:vertical;">${app.exception_notes || ''}</textarea>
                         </div>
                         
                         <div style="display:flex;gap:12px;justify-content:flex-end;">
                             <button type="button" onclick="hideEditModal()"
                                 style="padding:12px 24px;background:#6c757d;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                                 <i class="fas fa-times"></i> Hủy
                             </button>
                             <button type="submit" id="submitEditBtn"
                                 style="padding:12px 24px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
                                 <i class="fas fa-save"></i> Lưu thay đổi
                             </button>
                         </div>
                     </form>
                 </div>
             </div>
         `;
    }

    function calculateEditWaiverAmount() {
        const percentageInput = document.querySelector('#editForm input[name="waiver_percentage"]');
        const amountInput = document.querySelector('#editForm input[name="waiver_amount"]');

        if (percentageInput && amountInput) {
            const percentage = parseFloat(percentageInput.value) || 0;
            // Get original amount from the form or use a default value
            const originalAmount = parseFloat(amountInput.getAttribute('data-original-amount')) || 0;
            const waiverAmount = (originalAmount * percentage) / 100;

            // Format the calculated amount
            amountInput.value = waiverAmount.toLocaleString('vi-VN');
            amountInput.setAttribute('data-numeric-value', waiverAmount);
        }
    }

    function submitEdit(event, id) {
        event.preventDefault();

        const form = event.target;
        const submitBtn = document.getElementById('submitEditBtn');
        const originalText = submitBtn.innerHTML;

        // Show loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        submitBtn.disabled = true;

        const formData = new FormData(form);
        formData.append('action', 'update');
        formData.append('id', id);

        fetch('pages/admin/api/waiver-tracking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const content = document.getElementById('editModalContent');
                    content.innerHTML = `
                     <div style="text-align:center;padding:40px;">
                         <div style="font-size:4rem;color:#f59e0b;margin-bottom:16px;">✅</div>
                         <h3 style="margin:0;font-size:1.5rem;font-weight:600;color:#f59e0b;">Cập nhật thành công!</h3>
                         <p style="color:#666;margin:16px 0;">Thông tin đơn miễn giảm đã được cập nhật thành công.</p>
                         <button onclick="hideEditModal(); location.reload();" 
                             style="padding:12px 24px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
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
</script>