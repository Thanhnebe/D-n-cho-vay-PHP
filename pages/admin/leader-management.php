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
            case 'create_lender':
                $lenderData = [
                    'name' => sanitize_input($_POST['name']),
                    'phone' => sanitize_input($_POST['phone']),
                    'email' => sanitize_input($_POST['email']),
                    'address' => sanitize_input($_POST['address']),
                    'id_number' => sanitize_input($_POST['id_number']),
                    'date_of_birth' => $_POST['date_of_birth'],
                    'gender' => $_POST['gender'],
                    'status' => $_POST['status'],
                    'verified' => isset($_POST['verified']) ? 1 : 0
                ];

                // Kiểm tra email đã tồn tại
                if ($lenderData['email']) {
                    $existing_email = $db->fetchOne("SELECT id FROM lenders WHERE email = ?", [$lenderData['email']]);
                    if ($existing_email) {
                        throw new Exception('Email đã tồn tại trong hệ thống');
                    }
                }

                // Kiểm tra số điện thoại đã tồn tại
                $existing_phone = $db->fetchOne("SELECT id FROM lenders WHERE phone = ?", [$lenderData['phone']]);
                if ($existing_phone) {
                    throw new Exception('Số điện thoại đã tồn tại trong hệ thống');
                }

                $lender_id = $db->insert('lenders', $lenderData);
                $message = 'Tạo người cho vay thành công';
                break;

            case 'update_lender':
                $lender_id = intval($_POST['lender_id']);
                $lenderData = [
                    'name' => sanitize_input($_POST['name']),
                    'phone' => sanitize_input($_POST['phone']),
                    'email' => sanitize_input($_POST['email']),
                    'address' => sanitize_input($_POST['address']),
                    'id_number' => sanitize_input($_POST['id_number']),
                    'date_of_birth' => $_POST['date_of_birth'],
                    'gender' => $_POST['gender'],
                    'status' => $_POST['status'],
                    'verified' => isset($_POST['verified']) ? 1 : 0
                ];

                // Kiểm tra email đã tồn tại (trừ lender hiện tại)
                if ($lenderData['email']) {
                    $existing_email = $db->fetchOne("SELECT id FROM lenders WHERE email = ? AND id != ?", [$lenderData['email'], $lender_id]);
                    if ($existing_email) {
                        throw new Exception('Email đã tồn tại trong hệ thống');
                    }
                }

                // Kiểm tra số điện thoại đã tồn tại (trừ lender hiện tại)
                $existing_phone = $db->fetchOne("SELECT id FROM lenders WHERE phone = ? AND id != ?", [$lenderData['phone'], $lender_id]);
                if ($existing_phone) {
                    throw new Exception('Số điện thoại đã tồn tại trong hệ thống');
                }

                $affected = $db->update('lenders', $lenderData, 'id = ?', [$lender_id]);

                if ($affected > 0) {
                    $message = 'Cập nhật người cho vay thành công';
                } else {
                    throw new Exception('Không tìm thấy người cho vay để cập nhật');
                }
                break;

            case 'delete_lender':
                $lender_id = intval($_POST['lender_id']);

                // Kiểm tra xem có hợp đồng nào liên quan không
                $contracts_count = $db->fetchOne("SELECT COUNT(*) as count FROM electronic_contracts WHERE lender_id = ?", [$lender_id]);
                if ($contracts_count['count'] > 0) {
                    throw new Exception('Không thể xóa người cho vay đang có hợp đồng liên quan');
                }

                $affected = $db->delete('lenders', 'id = ?', [$lender_id]);

                if ($affected > 0) {
                    $message = 'Xóa người cho vay thành công';
                } else {
                    throw new Exception('Không tìm thấy người cho vay để xóa');
                }
                break;

            case 'change_status':
                $lender_id = intval($_POST['lender_id']);
                $status = $_POST['status'];

                $affected = $db->update('lenders', ['status' => $status], 'id = ?', [$lender_id]);

                if ($affected > 0) {
                    $message = 'Cập nhật trạng thái thành công';
                } else {
                    throw new Exception('Không tìm thấy người cho vay để cập nhật');
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Lấy dữ liệu cho trang
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$gender_filter = $_GET['gender'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR l.id_number LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status_filter) {
    $where_conditions[] = "l.status = ?";
    $params[] = $status_filter;
}

if ($gender_filter) {
    $where_conditions[] = "l.gender = ?";
    $params[] = $gender_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Pagination
$page = max(1, intval($_GET['p'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM lenders l {$where_clause}";
$total_result = $db->fetchOne($count_query, $params);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $per_page);

// Get lenders
$query = "
    SELECT l.*, 
           COUNT(ec.id) as contract_count,
           SUM(ec.loan_amount) as total_loaned
    FROM lenders l 
    LEFT JOIN electronic_contracts ec ON l.id = ec.lender_id
    {$where_clause} 
    GROUP BY l.id
    ORDER BY l.created_at DESC 
    LIMIT {$per_page} OFFSET {$offset}
";

$lenders = $db->fetchAll($query, $params);

// Get statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_lenders,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_lenders,
        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_lenders,
        COUNT(CASE WHEN status = 'blacklisted' THEN 1 END) as blacklisted_lenders,
        COUNT(CASE WHEN verified = 1 THEN 1 END) as verified_lenders
    FROM lenders
");

// Helper functions
function getStatusLabel($status)
{
    $labels = [
        'active' => '<span class="badge bg-success">Hoạt động</span>',
        'inactive' => '<span class="badge bg-secondary">Không hoạt động</span>',
        'blacklisted' => '<span class="badge bg-danger">Đen</span>'
    ];
    return $labels[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

function getGenderLabel($gender)
{
    $labels = [
        'male' => '<span class="badge bg-primary">Nam</span>',
        'female' => '<span class="badge bg-pink">Nữ</span>',
        'other' => '<span class="badge bg-info">Khác</span>'
    ];
    return $labels[$gender] ?? '<span class="badge bg-secondary">' . $gender . '</span>';
}

function getVerifiedLabel($verified)
{
    return $verified ?
        '<span class="badge bg-success">Đã xác thực</span>' :
        '<span class="badge bg-warning">Chưa xác thực</span>';
}

function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

function formatDate($date)
{
    return $date ? date('d/m/Y', strtotime($date)) : 'N/A';
}

function calculateAge($birthDate)
{
    if (!$birthDate) return 'N/A';
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($birth);
    return $age->y . ' tuổi';
}

$page_title = 'Quản lý Người cho vay';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-handshake"></i> Quản lý Người cho vay
        </h1>
        <button class="btn btn-primary" onclick="showCreateLenderModal()">
            <i class="fas fa-plus"></i> Thêm Người cho vay
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
                                Tổng người cho vay
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_lenders'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-handshake fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['active_lenders'] ?></div>
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
                                Đã xác thực
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['verified_lenders'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Danh sách đen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['blacklisted_lenders'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
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
                <input type="hidden" name="page" value="leader-management">
                <div class="col-md-4">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm tên, SĐT, email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Không hoạt động</option>
                        <option value="blacklisted" <?= $status_filter === 'blacklisted' ? 'selected' : '' ?>>Danh sách đen</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giới tính</label>
                    <select class="form-select" name="gender">
                        <option value="">Tất cả</option>
                        <option value="male" <?= $gender_filter === 'male' ? 'selected' : '' ?>>Nam</option>
                        <option value="female" <?= $gender_filter === 'female' ? 'selected' : '' ?>>Nữ</option>
                        <option value="other" <?= $gender_filter === 'other' ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="index.php?page=leader-management" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lenders Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách Người cho vay</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Thông tin cá nhân</th>
                            <th>Liên hệ</th>
                            <th>Trạng thái</th>
                            <th>Thống kê</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lenders as $index => $lender): ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>
                                <td>
                                    <div><strong><?= htmlspecialchars($lender['name']) ?></strong></div>
                                    <div><small class="text-muted">CCCD: <?= htmlspecialchars($lender['id_number'] ?: 'N/A') ?></small></div>
                                    <div><small class="text-info"><?= getGenderLabel($lender['gender']) ?></small></div>
                                    <div><small class="text-muted">Tuổi: <?= calculateAge($lender['date_of_birth']) ?></small></div>
                                </td>
                                <td>
                                    <div><strong>ĐT: <?= htmlspecialchars($lender['phone']) ?></strong></div>
                                    <div><small class="text-muted">Email: <?= htmlspecialchars($lender['email'] ?: 'N/A') ?></small></div>
                                    <div><small class="text-muted">Địa chỉ: <?= htmlspecialchars($lender['address'] ?: 'N/A') ?></small></div>
                                </td>
                                <td>
                                    <div><?= getStatusLabel($lender['status']) ?></div>
                                    <div><?= getVerifiedLabel($lender['verified']) ?></div>
                                </td>
                                <td>
                                    <div><strong>Hợp đồng: <?= $lender['contract_count'] ?></strong></div>
                                    <div><small class="text-success">Tổng cho vay: <?= formatCurrency($lender['total_loaned'] ?: 0) ?></small></div>
                                    <div><small class="text-muted">Ngày tạo: <?= formatDate($lender['created_at']) ?></small></div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewLender(<?= $lender['id'] ?>)" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editLender(<?= $lender['id'] ?>)" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteLender(<?= $lender['id'] ?>)" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group mt-1" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php if ($lender['status'] === 'active'): ?>
                                                <li><a class="dropdown-item" href="#" onclick="changeStatus(<?= $lender['id'] ?>, 'inactive')">
                                                        <i class="fas fa-pause"></i> Tạm khóa
                                                    </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="changeStatus(<?= $lender['id'] ?>, 'blacklisted')">
                                                        <i class="fas fa-ban"></i> Đưa vào danh sách đen
                                                    </a></li>
                                            <?php else: ?>
                                                <li><a class="dropdown-item" href="#" onclick="changeStatus(<?= $lender['id'] ?>, 'active')">
                                                        <i class="fas fa-play"></i> Kích hoạt
                                                    </a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="#" onclick="toggleVerification(<?= $lender['id'] ?>)">
                                                    <i class="fas fa-user-check"></i> <?= $lender['verified'] ? 'Bỏ xác thực' : 'Xác thực' ?>
                                                </a></li>
                                        </ul>
                                    </div>
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
                                <a class="page-link" href="?page=leader-management&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&gender=<?= urlencode($gender_filter) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=leader-management&p=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&gender=<?= urlencode($gender_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=leader-management&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&gender=<?= urlencode($gender_filter) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="text-center text-muted mt-3">
                Hiển thị <?= $offset + 1 ?>-<?= min($offset + $per_page, $total_records) ?> trong tổng số <?= $total_records ?> người cho vay
            </div>
        </div>
    </div>
</div>

<!-- Modal tạo/sửa người cho vay -->
<div class="modal fade" id="lenderModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="lenderModalTitle">
                    <i class="fas fa-plus me-2"></i>Thêm Người cho vay
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="lenderForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_lender">
                    <input type="hidden" name="lender_id" value="">

                    <!-- Thông tin cá nhân -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Thông tin cá nhân
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">CCCD</label>
                                <input type="text" class="form-control" name="id_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Giới tính</label>
                                <select class="form-select" name="gender">
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea class="form-control" name="address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin bổ sung -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-cog me-2"></i>Thông tin bổ sung
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-select" name="status">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Không hoạt động</option>
                                    <option value="blacklisted">Danh sách đen</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="verified" value="1" id="verified">
                                    <label class="form-check-label" for="verified">
                                        Đã xác thực
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="lenderSubmitBtn">
                        <i class="fas fa-save me-2"></i>Tạo Người cho vay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết người cho vay -->
<div class="modal fade" id="lenderViewModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Chi tiết Người cho vay
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="lenderViewContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showCreateLenderModal() {
        const modal = document.getElementById('lenderModal');
        const form = document.getElementById('lenderForm');
        const title = document.getElementById('lenderModalTitle');
        const submitBtn = document.getElementById('lenderSubmitBtn');

        // Reset form
        form.reset();
        form.querySelector('input[name="action"]').value = 'create_lender';
        form.querySelector('input[name="lender_id"]').value = '';

        title.innerHTML = '<i class="fas fa-plus me-2"></i>Thêm Người cho vay';
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Tạo Người cho vay';

        new bootstrap.Modal(modal).show();
    }

    function editLender(lenderId) {
        // Load lender data via AJAX
        fetch(`pages/admin/api/get-lender.php?id=${lenderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const lender = data.data;
                    const modal = document.getElementById('lenderModal');
                    const form = document.getElementById('lenderForm');
                    const title = document.getElementById('lenderModalTitle');
                    const submitBtn = document.getElementById('lenderSubmitBtn');

                    // Check if all required elements exist
                    if (!modal || !form || !title || !submitBtn) {
                        console.error('Required modal elements not found');
                        alert('Có lỗi xảy ra khi tải modal');
                        return;
                    }

                    // Helper function to safely set form field value
                    function setFormField(fieldName, value) {
                        const element = form.querySelector(`[name="${fieldName}"]`);
                        if (element) {
                            if (element.type === 'checkbox') {
                                element.checked = value == 1;
                            } else {
                                element.value = value || '';
                            }
                        } else {
                            console.warn(`Form field '${fieldName}' not found`);
                        }
                    }

                    // Populate form with null checks
                    setFormField('action', 'update_lender');
                    setFormField('lender_id', lender.id);
                    setFormField('name', lender.name);
                    setFormField('phone', lender.phone);
                    setFormField('email', lender.email);
                    setFormField('address', lender.address);
                    setFormField('id_number', lender.id_number);
                    setFormField('date_of_birth', lender.date_of_birth);
                    setFormField('gender', lender.gender);
                    setFormField('status', lender.status);
                    setFormField('verified', lender.verified);

                    title.innerHTML = '<i class="fas fa-edit me-2"></i>Cập nhật Người cho vay';
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Cập nhật';

                    new bootstrap.Modal(modal).show();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải dữ liệu');
            });
    }

    function deleteLender(lenderId) {
        if (confirm('Bạn có chắc muốn xóa người cho vay này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_lender">
                <input type="hidden" name="lender_id" value="${lenderId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function changeStatus(lenderId, status) {
        const statusText = status === 'active' ? 'kích hoạt' : (status === 'inactive' ? 'tạm khóa' : 'đưa vào danh sách đen');
        if (confirm(`Bạn có chắc muốn ${statusText} người cho vay này?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="change_status">
                <input type="hidden" name="lender_id" value="${lenderId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleVerification(lenderId) {
        if (confirm('Bạn có chắc muốn thay đổi trạng thái xác thực?')) {
            fetch('pages/admin/api/toggle-verification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `lender_id=${lenderId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cập nhật trạng thái xác thực thành công');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                });
        }
    }

    function viewLender(lenderId) {
        fetch(`pages/admin/api/get-lender.php?id=${lenderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const lender = data.data;
                    const content = document.getElementById('lenderViewContent');

                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin cá nhân</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr><td><strong>Họ tên:</strong></td><td>${lender.name}</td></tr>
                                            <tr><td><strong>CCCD:</strong></td><td>${lender.id_number || 'N/A'}</td></tr>
                                            <tr><td><strong>Ngày sinh:</strong></td><td>${formatDate(lender.date_of_birth)}</td></tr>
                                            <tr><td><strong>Tuổi:</strong></td><td>${calculateAge(lender.date_of_birth)}</td></tr>
                                            <tr><td><strong>Giới tính:</strong></td><td>${getGenderLabel(lender.gender)}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-phone me-2"></i>Thông tin liên hệ</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr><td><strong>Số điện thoại:</strong></td><td>${lender.phone}</td></tr>
                                            <tr><td><strong>Email:</strong></td><td>${lender.email || 'N/A'}</td></tr>
                                            <tr><td><strong>Địa chỉ:</strong></td><td>${lender.address || 'N/A'}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr><td><strong>Số hợp đồng:</strong></td><td>${lender.contract_count || 0}</td></tr>
                                            <tr><td><strong>Tổng cho vay:</strong></td><td>${formatCurrency(lender.total_loaned || 0)}</td></tr>
                                            <tr><td><strong>Ngày tạo:</strong></td><td>${formatDate(lender.created_at)}</td></tr>
                                            <tr><td><strong>Cập nhật cuối:</strong></td><td>${formatDate(lender.updated_at)}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Trạng thái</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr><td><strong>Trạng thái:</strong></td><td>${getStatusLabel(lender.status)}</td></tr>
                                            <tr><td><strong>Xác thực:</strong></td><td>${getVerifiedLabel(lender.verified)}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    new bootstrap.Modal(document.getElementById('lenderViewModal')).show();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải dữ liệu');
            });
    }

    function getGenderLabel(gender) {
        const labels = {
            'male': '<span class="badge bg-primary">Nam</span>',
            'female': '<span class="badge bg-pink">Nữ</span>',
            'other': '<span class="badge bg-info">Khác</span>'
        };
        return labels[gender] || gender;
    }

    function getStatusLabel(status) {
        const labels = {
            'active': '<span class="badge bg-success">Hoạt động</span>',
            'inactive': '<span class="badge bg-secondary">Không hoạt động</span>',
            'blacklisted': '<span class="badge bg-danger">Danh sách đen</span>'
        };
        return labels[status] || status;
    }

    function getVerifiedLabel(verified) {
        return verified ?
            '<span class="badge bg-success">Đã xác thực</span>' :
            '<span class="badge bg-warning">Chưa xác thực</span>';
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function formatDate(date) {
        return date ? new Date(date).toLocaleDateString('vi-VN') : 'N/A';
    }

    function calculateAge(birthDate) {
        if (!birthDate) return 'N/A';
        const birth = new Date(birthDate);
        const today = new Date();
        const age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            return (age - 1) + ' tuổi';
        }
        return age + ' tuổi';
    }

    // Reset modal when closed
    document.getElementById('lenderModal').addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        form.reset();
        form.querySelector('input[name="action"]').value = 'create_lender';
        form.querySelector('input[name="lender_id"]').value = '';

        const title = this.querySelector('.modal-title');
        const submitBtn = this.querySelector('button[type="submit"]');
        title.innerHTML = '<i class="fas fa-plus me-2"></i>Thêm Người cho vay';
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Tạo Người cho vay';
    });
</script>