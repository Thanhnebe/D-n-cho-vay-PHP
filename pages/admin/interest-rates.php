<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Xử lý form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'create_rate':
                $rateData = [
                    'loan_type' => $_POST['loan_type'],
                    'min_amount' => floatval($_POST['min_amount']),
                    'max_amount' => floatval($_POST['max_amount']),
                    'monthly_rate' => floatval($_POST['monthly_rate']),
                    'daily_rate' => floatval($_POST['daily_rate']),
                    'service_fee_rate' => floatval($_POST['service_fee_rate']),
                    'grace_period_days' => intval($_POST['grace_period_days']),
                    'late_fee_rate' => floatval($_POST['late_fee_rate']),
                    'max_late_fee' => floatval($_POST['max_late_fee']),
                    'effective_from' => $_POST['effective_from'],
                    'effective_to' => $_POST['effective_to'] ?: null,
                    'status' => $_POST['status'],
                    'description' => $_POST['description'],
                    'created_by' => $_SESSION['user_id'] ?? 1
                ];

                // Validate overlapping rates
                $overlapping = $db->fetchOne("
                    SELECT id FROM interest_rates 
                    WHERE loan_type = ? 
                    AND status = 'active'
                    AND (
                        (min_amount <= ? AND max_amount >= ?) OR
                        (min_amount <= ? AND max_amount >= ?) OR
                        (min_amount >= ? AND max_amount <= ?)
                    )
                    AND effective_from <= ? 
                    AND (effective_to IS NULL OR effective_to >= ?)
                ", [
                    $rateData['loan_type'],
                    $rateData['min_amount'],
                    $rateData['min_amount'],
                    $rateData['max_amount'],
                    $rateData['max_amount'],
                    $rateData['min_amount'],
                    $rateData['max_amount'],
                    $rateData['effective_from'],
                    $rateData['effective_from']
                ]);

                if ($overlapping) {
                    throw new Exception('Đã tồn tại lãi suất cho khoảng tiền và thời gian này');
                }

                $db->insert('interest_rates', $rateData);
                $message = 'Tạo lãi suất thành công';
                break;

            case 'update_rate':
                $rate_id = intval($_POST['rate_id']);
                $rateData = [
                    'loan_type' => $_POST['loan_type'],
                    'min_amount' => floatval($_POST['min_amount']),
                    'max_amount' => floatval($_POST['max_amount']),
                    'monthly_rate' => floatval($_POST['monthly_rate']),
                    'daily_rate' => floatval($_POST['daily_rate']),
                    'service_fee_rate' => floatval($_POST['service_fee_rate']),
                    'grace_period_days' => intval($_POST['grace_period_days']),
                    'late_fee_rate' => floatval($_POST['late_fee_rate']),
                    'max_late_fee' => floatval($_POST['max_late_fee']),
                    'effective_from' => $_POST['effective_from'],
                    'effective_to' => $_POST['effective_to'] ?: null,
                    'status' => $_POST['status'],
                    'description' => $_POST['description']
                ];

                // Validate overlapping rates (excluding current rate)
                $overlapping = $db->fetchOne("
                    SELECT id FROM interest_rates 
                    WHERE loan_type = ? 
                    AND status = 'active'
                    AND id != ?
                    AND (
                        (min_amount <= ? AND max_amount >= ?) OR
                        (min_amount <= ? AND max_amount >= ?) OR
                        (min_amount >= ? AND max_amount <= ?)
                    )
                    AND effective_from <= ? 
                    AND (effective_to IS NULL OR effective_to >= ?)
                ", [
                    $rateData['loan_type'],
                    $rate_id,
                    $rateData['min_amount'],
                    $rateData['min_amount'],
                    $rateData['max_amount'],
                    $rateData['max_amount'],
                    $rateData['min_amount'],
                    $rateData['max_amount'],
                    $rateData['effective_from'],
                    $rateData['effective_from']
                ]);

                if ($overlapping) {
                    throw new Exception('Đã tồn tại lãi suất cho khoảng tiền và thời gian này');
                }

                $affected = $db->update('interest_rates', $rateData, 'id = ?', ['id' => $rate_id]);

                if ($affected > 0) {
                    $message = 'Cập nhật lãi suất thành công';
                } else {
                    throw new Exception('Không tìm thấy lãi suất để cập nhật');
                }
                break;

            case 'delete_rate':
                $rate_id = intval($_POST['rate_id']);

                // Kiểm tra xem có hợp đồng nào đang sử dụng lãi suất này không
                $contracts_using_rate = $db->fetchAll("SELECT COUNT(*) as count FROM contracts WHERE interest_rate_id = ?", [$rate_id]);

                if ($contracts_using_rate[0]['count'] > 0) {
                    throw new Exception('Không thể xóa lãi suất đang được sử dụng trong hợp đồng');
                }

                $affected = $db->delete('interest_rates', 'id = ?', [$rate_id]);

                if ($affected > 0) {
                    $message = 'Xóa lãi suất thành công';
                } else {
                    throw new Exception('Không tìm thấy lãi suất để xóa');
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Lấy dữ liệu cho trang
$search = $_GET['search'] ?? '';
$loan_type_filter = $_GET['loan_type'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(description LIKE ? OR loan_type LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($loan_type_filter) {
    $where_conditions[] = "loan_type = ?";
    $params[] = $loan_type_filter;
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Pagination
$page = max(1, intval($_GET['p'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM interest_rates {$where_clause}";
$total_result = $db->fetchOne($count_query, $params);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $per_page);

// Get rates
$query = "
    SELECT ir.*, u.name as created_by_name 
    FROM interest_rates ir 
    LEFT JOIN users u ON ir.created_by = u.id 
    {$where_clause} 
    ORDER BY ir.effective_from DESC, ir.created_at DESC 
    LIMIT {$per_page} OFFSET {$offset}
";

$rates = $db->fetchAll($query, $params);

// Get statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_rates,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_rates,
        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_rates,
        COUNT(CASE WHEN effective_to IS NULL OR effective_to >= CURDATE() THEN 1 END) as current_rates
    FROM interest_rates
");

// Helper functions
function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

function formatPercentage($rate)
{
    return number_format($rate, 2) . '%';
}

function getLoanTypeLabel($type)
{
    $labels = [
        'standard' => '<span class="badge bg-primary">Lãi suất chuẩn</span>',
        'short_term' => '<span class="badge bg-info">Ngắn hạn</span>',
        'medium_term' => '<span class="badge bg-success">Trung hạn</span>',
        'long_term' => '<span class="badge bg-warning">Dài hạn</span>',
        'penalty_standard' => '<span class="badge bg-danger">Phạt chuẩn</span>',
        'penalty_heavy' => '<span class="badge bg-dark">Phạt nặng</span>'
    ];
    return $labels[$type] ?? '<span class="badge bg-secondary">' . $type . '</span>';
}

function getStatusLabel($status)
{
    return $status === 'active' ?
        '<span class="badge bg-success">Hoạt động</span>' :
        '<span class="badge bg-secondary">Không hoạt động</span>';
}

function isCurrentRate($effective_from, $effective_to)
{
    $today = date('Y-m-d');
    return $effective_from <= $today && ($effective_to === null || $effective_to >= $today);
}

$page_title = 'Quản lý lãi suất';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> Quản lý lãi suất
        </h1>
        <button class="btn btn-primary" onclick="showCreateRateModal()">
            <i class="fas fa-plus"></i> Thêm lãi suất
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng lãi suất
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_rates'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đang hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['active_rates'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Hiện tại
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['current_rates'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Không hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['inactive_rates'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-search"></i> Tìm kiếm & Lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Loại khoản vay</label>
                    <select class="form-select" name="loan_type">
                        <option value="">Tất cả</option>
                        <option value="standard" <?= $loan_type_filter === 'standard' ? 'selected' : '' ?>>Lãi suất chuẩn</option>
                        <option value="short_term" <?= $loan_type_filter === 'short_term' ? 'selected' : '' ?>>Ngắn hạn</option>
                        <option value="medium_term" <?= $loan_type_filter === 'medium_term' ? 'selected' : '' ?>>Trung hạn</option>
                        <option value="long_term" <?= $loan_type_filter === 'long_term' ? 'selected' : '' ?>>Dài hạn</option>
                        <option value="penalty_standard" <?= $loan_type_filter === 'penalty_standard' ? 'selected' : '' ?>>Phạt chuẩn</option>
                        <option value="penalty_heavy" <?= $loan_type_filter === 'penalty_heavy' ? 'selected' : '' ?>>Phạt nặng</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="index.php?page=interest-rates" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rates Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách lãi suất</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Loại khoản vay</th>
                            <th>Khoảng tiền</th>
                            <th>Lãi suất</th>
                            <th>Thời gian hiệu lực</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rates as $index => $rate): ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>
                                <td>
                                    <?= getLoanTypeLabel($rate['loan_type']) ?>
                                    <?php if (isCurrentRate($rate['effective_from'], $rate['effective_to'])): ?>
                                        <span class="badge bg-success ms-1">Hiện tại</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><strong>Từ:</strong> <?= formatCurrency($rate['min_amount']) ?></div>
                                    <div><strong>Đến:</strong> <?= formatCurrency($rate['max_amount']) ?></div>
                                </td>
                                <td>
                                    <div><strong>Lãi suất:</strong> <?= formatPercentage($rate['monthly_rate']) ?>/tháng</div>
                                    <div><strong>Phí dịch vụ:</strong> <?= formatPercentage($rate['service_fee_rate']) ?>/tháng</div>
                                    <div><strong>Phạt quá hạn:</strong> <?= formatCurrency($rate['late_fee_rate']) ?>/ngày</div>
                                    <?php if ($rate['max_late_fee'] > 0): ?>
                                        <div><small class="text-muted">Tối đa: <?= formatCurrency($rate['max_late_fee']) ?>/kỳ</small></div>
                                    <?php endif; ?>
                                    <?php if ($rate['grace_period_days'] > 0): ?>
                                        <div><small class="text-muted">Gia hạn: <?= $rate['grace_period_days'] ?> ngày</small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><strong>Từ:</strong> <?= date('d/m/Y', strtotime($rate['effective_from'])) ?></div>
                                    <?php if ($rate['effective_to']): ?>
                                        <div><strong>Đến:</strong> <?= date('d/m/Y', strtotime($rate['effective_to'])) ?></div>
                                    <?php else: ?>
                                        <div><small class="text-muted">Không giới hạn</small></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= getStatusLabel($rate['status']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editRate(<?= $rate['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRate(<?= $rate['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewRateDetails(<?= $rate['id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=interest-rates&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&loan_type=<?= urlencode($loan_type_filter) ?>&status=<?= urlencode($status_filter) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=interest-rates&p=<?= $i ?>&search=<?= urlencode($search) ?>&loan_type=<?= urlencode($loan_type_filter) ?>&status=<?= urlencode($status_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=interest-rates&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&loan_type=<?= urlencode($loan_type_filter) ?>&status=<?= urlencode($status_filter) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="text-center text-muted mt-3">
                Hiển thị <?= $offset + 1 ?>-<?= min($offset + $per_page, $total_records) ?> trong tổng số <?= $total_records ?> lãi suất
            </div>
        </div>
    </div>
</div>

<!-- Modal tạo/sửa lãi suất -->
<div class="modal fade" id="rateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rateModalTitle">Thêm lãi suất mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="rateForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_rate">
                    <input type="hidden" name="rate_id" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Loại khoản vay *</label>
                                <select class="form-select" name="loan_type" required>
                                    <option value="">Chọn loại khoản vay</option>
                                    <option value="standard">Lãi suất chuẩn</option>
                                    <option value="short_term">Ngắn hạn</option>
                                    <option value="medium_term">Trung hạn</option>
                                    <option value="long_term">Dài hạn</option>
                                    <option value="penalty_standard">Phạt chuẩn</option>
                                    <option value="penalty_heavy">Phạt nặng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số tiền tối thiểu (VNĐ) *</label>
                                <input type="number" class="form-control" name="min_amount" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số tiền tối đa (VNĐ) *</label>
                                <input type="number" class="form-control" name="max_amount" min="0" step="1000" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lãi suất tháng (%) *</label>
                                <input type="number" class="form-control" name="monthly_rate" min="0" max="100" step="0.01" required>
                                <small class="text-muted">Mặc định: 1.58% (19%/năm)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lãi suất ngày (%) *</label>
                                <input type="number" class="form-control" name="daily_rate" min="0" max="100" step="0.0001" required>
                                <small class="text-muted">Mặc định: 0.052% (19%/365 ngày)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phí dịch vụ (%) *</label>
                                <input type="number" class="form-control" name="service_fee_rate" min="0" max="100" step="0.1" required>
                                <small class="text-muted">Mặc định: 4.5%/tháng</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phí phạt quá hạn (VNĐ/ngày)</label>
                                <input type="number" class="form-control" name="late_fee_rate" min="0" step="1000" value="100000">
                                <small class="text-muted">Mặc định: 100,000 VNĐ/ngày</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phí phạt tối đa (VNĐ/kỳ)</label>
                                <input type="number" class="form-control" name="max_late_fee" min="0" step="100000" value="1000000">
                                <small class="text-muted">Mặc định: 1,000,000 VNĐ/kỳ</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Thời gian gia hạn (ngày)</label>
                                <input type="number" class="form-control" name="grace_period_days" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Thời gian gia hạn (ngày)</label>
                                <input type="number" class="form-control" name="grace_period_days" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phí phạt quá hạn (%)</label>
                                <input type="number" class="form-control" name="late_fee_rate" min="0" max="100" step="0.01" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày hiệu lực từ *</label>
                                <input type="date" class="form-control" name="effective_from" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày hiệu lực đến</label>
                                <input type="date" class="form-control" name="effective_to">
                                <small class="text-muted">Để trống nếu không giới hạn thời gian</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Mô tả chi tiết về lãi suất này..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="rateSubmitBtn">Tạo lãi suất</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal chi tiết lãi suất -->
<div class="modal fade" id="rateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết lãi suất</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="rateDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showCreateRateModal() {
        const modal = document.getElementById('rateModal');
        const form = document.getElementById('rateForm');
        const title = document.getElementById('rateModalTitle');
        const submitBtn = document.getElementById('rateSubmitBtn');

        // Reset form
        form.reset();
        form.querySelector('input[name="action"]').value = 'create_rate';
        form.querySelector('input[name="rate_id"]').value = '';

        // Set default date
        form.querySelector('input[name="effective_from"]').value = new Date().toISOString().split('T')[0];

        // Set default values based on user requirements
        form.querySelector('select[name="loan_type"]').value = 'standard';
        form.querySelector('input[name="min_amount"]').value = '1000000';
        form.querySelector('input[name="max_amount"]').value = '1000000000';
        form.querySelector('input[name="monthly_rate"]').value = '1.58'; // 19% / 12 months
        form.querySelector('input[name="daily_rate"]').value = '0.052'; // 19% / 365 days
        form.querySelector('input[name="service_fee_rate"]').value = '4.5';
        form.querySelector('input[name="late_fee_rate"]').value = '0.1'; // 100k per day
        form.querySelector('input[name="max_late_fee"]').value = '1000000'; // 1 million max
        form.querySelector('input[name="grace_period_days"]').value = '0';
        form.querySelector('select[name="status"]').value = 'active';

        title.textContent = 'Thêm lãi suất mới';
        submitBtn.textContent = 'Tạo lãi suất';

        new bootstrap.Modal(modal).show();
    }

    function editRate(rateId) {
        // Load rate data via AJAX
        fetch(`api/interest-rates.php?action=get_rate&id=${rateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rate = data.data;
                    const modal = document.getElementById('rateModal');
                    const form = document.getElementById('rateForm');
                    const title = document.getElementById('rateModalTitle');
                    const submitBtn = document.getElementById('rateSubmitBtn');

                    // Populate form
                    form.querySelector('input[name="action"]').value = 'update_rate';
                    form.querySelector('input[name="rate_id"]').value = rate.id;
                    form.querySelector('select[name="loan_type"]').value = rate.loan_type;
                    form.querySelector('input[name="min_amount"]').value = rate.min_amount;
                    form.querySelector('input[name="max_amount"]').value = rate.max_amount;
                    form.querySelector('input[name="monthly_rate"]').value = rate.monthly_rate;
                    form.querySelector('input[name="daily_rate"]').value = rate.daily_rate;
                    form.querySelector('input[name="service_fee_rate"]').value = rate.service_fee_rate || 4.5;
                    form.querySelector('input[name="grace_period_days"]').value = rate.grace_period_days;
                    form.querySelector('input[name="late_fee_rate"]').value = rate.late_fee_rate;
                    form.querySelector('input[name="max_late_fee"]').value = rate.max_late_fee || 1000000;
                    form.querySelector('input[name="effective_from"]').value = rate.effective_from;
                    form.querySelector('input[name="effective_to"]').value = rate.effective_to || '';
                    form.querySelector('select[name="status"]').value = rate.status;
                    form.querySelector('textarea[name="description"]').value = rate.description;

                    title.textContent = 'Cập nhật lãi suất';
                    submitBtn.textContent = 'Cập nhật';

                    new bootstrap.Modal(modal).show();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải dữ liệu');
            });
    }

    function deleteRate(rateId) {
        if (confirm('Bạn có chắc muốn xóa lãi suất này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_rate">
            <input type="hidden" name="rate_id" value="${rateId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewRateDetails(rateId) {
        fetch(`api/interest-rates.php?action=get_rate&id=${rateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rate = data.data;
                    const content = document.getElementById('rateDetailsContent');

                    content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thông tin cơ bản</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Loại khoản vay:</strong></td><td>${getLoanTypeLabel(rate.loan_type)}</td></tr>
                                <tr><td><strong>Trạng thái:</strong></td><td>${getStatusLabel(rate.status)}</td></tr>
                                <tr><td><strong>Người tạo:</strong></td><td>${rate.created_by_name || 'N/A'}</td></tr>
                                <tr><td><strong>Ngày tạo:</strong></td><td>${new Date(rate.created_at).toLocaleString('vi-VN')}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Khoảng tiền</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Từ:</strong></td><td>${formatCurrency(rate.min_amount)}</td></tr>
                                <tr><td><strong>Đến:</strong></td><td>${formatCurrency(rate.max_amount)}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Lãi suất & Phí</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Lãi suất:</strong></td><td>${formatPercentage(rate.monthly_rate)}/tháng</td></tr>
                                <tr><td><strong>Phí dịch vụ:</strong></td><td>${formatPercentage(rate.service_fee_rate || 4.5)}/tháng</td></tr>
                                <tr><td><strong>Phạt quá hạn:</strong></td><td>${formatCurrency(rate.late_fee_rate)}/ngày</td></tr>
                                <tr><td><strong>Phạt tối đa:</strong></td><td>${formatCurrency(rate.max_late_fee || 1000000)}/kỳ</td></tr>
                                <tr><td><strong>Gia hạn:</strong></td><td>${rate.grace_period_days} ngày</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Thời gian hiệu lực</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Từ:</strong></td><td>${new Date(rate.effective_from).toLocaleDateString('vi-VN')}</td></tr>
                                <tr><td><strong>Đến:</strong></td><td>${rate.effective_to ? new Date(rate.effective_to).toLocaleDateString('vi-VN') : 'Không giới hạn'}</td></tr>
                            </table>
                        </div>
                    </div>
                    ${rate.description ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Mô tả</h6>
                            <p class="text-muted">${rate.description}</p>
                        </div>
                    </div>
                    ` : ''}
                `;

                    new bootstrap.Modal(document.getElementById('rateDetailsModal')).show();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải dữ liệu');
            });
    }

    function getLoanTypeLabel(type) {
        const labels = {
            'standard': '<span class="badge bg-primary">Lãi suất chuẩn</span>',
            'short_term': '<span class="badge bg-info">Ngắn hạn</span>',
            'medium_term': '<span class="badge bg-success">Trung hạn</span>',
            'long_term': '<span class="badge bg-warning">Dài hạn</span>',
            'penalty_standard': '<span class="badge bg-danger">Phạt chuẩn</span>',
            'penalty_heavy': '<span class="badge bg-dark">Phạt nặng</span>'
        };
        return labels[type] || type;
    }

    function getStatusLabel(status) {
        return status === 'active' ?
            '<span class="badge bg-success">Hoạt động</span>' :
            '<span class="badge bg-secondary">Không hoạt động</span>';
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function formatPercentage(rate) {
        return new Intl.NumberFormat('vi-VN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(rate) + '%';
    }

    // Auto-calculate daily rate from monthly rate
    document.querySelector('input[name="monthly_rate"]').addEventListener('input', function() {
        const monthlyRate = parseFloat(this.value) || 0;
        const dailyRate = (monthlyRate / 30).toFixed(4);
        document.querySelector('input[name="daily_rate"]').value = dailyRate;
    });

    // Auto-calculate service fee rate (default 4.5%)
    document.querySelector('input[name="service_fee_rate"]').addEventListener('input', function() {
        const serviceFeeRate = parseFloat(this.value) || 4.5;
        if (serviceFeeRate < 0 || serviceFeeRate > 100) {
            this.setCustomValidity('Phí dịch vụ phải từ 0% đến 100%');
        } else {
            this.setCustomValidity('');
        }
    });

    // Reset modal when closed
    document.getElementById('rateModal').addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        form.reset();
        form.querySelector('input[name="action"]').value = 'create_rate';
        form.querySelector('input[name="rate_id"]').value = '';

        const title = this.querySelector('.modal-title');
        const submitBtn = this.querySelector('button[type="submit"]');
        title.textContent = 'Thêm lãi suất mới';
        submitBtn.textContent = 'Tạo lãi suất';
    });
</script>