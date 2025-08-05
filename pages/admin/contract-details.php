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
            case 'create_detail':
                $detailData = [
                    'contract_id' => intval($_POST['contract_id']),
                    'material_insurance' => $_POST['material_insurance'],
                    'hospital_insurance' => $_POST['hospital_insurance'],
                    'insurance_status' => $_POST['insurance_status'],
                    'digital_signature' => $_POST['digital_signature'],
                    'store_name' => $_POST['store_name'],
                    'store_code' => $_POST['store_code'],
                    'has_location_tracking' => $_POST['has_location_tracking'],
                    'location_tracking_type' => $_POST['location_tracking_type'],
                    'tima_customer_link' => $_POST['tima_customer_link'],
                    'tima_agent_code' => $_POST['tima_agent_code'],
                    'ndt_customer_link' => $_POST['ndt_customer_link'],
                    're_data_enabled' => $_POST['re_data_enabled']
                ];

                // Kiểm tra xem đã có chi tiết cho hợp đồng này chưa
                $existing = $db->fetchOne("SELECT id FROM contract_details WHERE contract_id = ?", [$detailData['contract_id']]);

                if ($existing) {
                    throw new Exception('Đã tồn tại chi tiết cho hợp đồng này');
                }

                $db->insert('contract_details', $detailData);
                $message = 'Tạo chi tiết hợp đồng thành công';
                break;

            case 'update_detail':
                $detail_id = intval($_POST['detail_id']);
                $detailData = [
                    'contract_id' => intval($_POST['contract_id']),
                    'material_insurance' => $_POST['material_insurance'],
                    'hospital_insurance' => $_POST['hospital_insurance'],
                    'insurance_status' => $_POST['insurance_status'],
                    'digital_signature' => $_POST['digital_signature'],
                    'store_name' => $_POST['store_name'],
                    'store_code' => $_POST['store_code'],
                    'has_location_tracking' => $_POST['has_location_tracking'],
                    'location_tracking_type' => $_POST['location_tracking_type'],
                    'tima_customer_link' => $_POST['tima_customer_link'],
                    'tima_agent_code' => $_POST['tima_agent_code'],
                    'ndt_customer_link' => $_POST['ndt_customer_link'],
                    're_data_enabled' => $_POST['re_data_enabled']
                ];

                // Kiểm tra xem có hợp đồng khác đã có chi tiết này chưa (trừ hợp đồng hiện tại)
                $existing = $db->fetchOne("SELECT id FROM contract_details WHERE contract_id = ? AND id != ?", [$detailData['contract_id'], $detail_id]);

                if ($existing) {
                    throw new Exception('Đã tồn tại chi tiết cho hợp đồng này');
                }

                $affected = $db->update('contract_details', $detailData, 'id = ?', ['id' => $detail_id]);

                if ($affected > 0) {
                    $message = 'Cập nhật chi tiết hợp đồng thành công';
                } else {
                    throw new Exception('Không tìm thấy chi tiết để cập nhật');
                }
                break;

            case 'delete_detail':
                $detail_id = intval($_POST['detail_id']);

                $affected = $db->delete('contract_details', 'id = ?', [$detail_id]);

                if ($affected > 0) {
                    $message = 'Xóa chi tiết hợp đồng thành công';
                } else {
                    throw new Exception('Không tìm thấy chi tiết để xóa');
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Lấy dữ liệu cho trang
$search = $_GET['search'] ?? '';
$contract_filter = $_GET['contract_id'] ?? '';
$insurance_filter = $_GET['insurance_status'] ?? '';
$tracking_filter = $_GET['has_location_tracking'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(cd.store_name LIKE ? OR cd.store_code LIKE ? OR c.contract_code LIKE ? OR cu.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($contract_filter) {
    $where_conditions[] = "cd.contract_id = ?";
    $params[] = $contract_filter;
}

if ($insurance_filter) {
    $where_conditions[] = "cd.insurance_status = ?";
    $params[] = $insurance_filter;
}

if ($tracking_filter) {
    $where_conditions[] = "cd.has_location_tracking = ?";
    $params[] = $tracking_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Pagination
$page = max(1, intval($_GET['p'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "
    SELECT COUNT(*) as total 
    FROM contract_details cd 
    JOIN contracts c ON cd.contract_id = c.id 
    JOIN customers cu ON c.customer_id = cu.id 
    {$where_clause}
";
$total_result = $db->fetchOne($count_query, $params);
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $per_page);

// Get contract details
$query = "
    SELECT 
        cd.*,
        c.contract_code,
        c.amount,
        c.status as contract_status,
        c.start_date,
        c.end_date,
        cu.name as customer_name,
        cu.phone as customer_phone,
        cu.cif as customer_cif
    FROM contract_details cd 
    JOIN contracts c ON cd.contract_id = c.id 
    JOIN customers cu ON c.customer_id = cu.id 
    {$where_clause} 
    ORDER BY cd.created_at DESC 
    LIMIT {$per_page} OFFSET {$offset}
";

$details = $db->fetchAll($query, $params);

// Get contracts for filter
$contracts = $db->fetchAll("
    SELECT c.id, c.contract_code, cu.name as customer_name 
    FROM contracts c 
    JOIN customers cu ON c.customer_id = cu.id 
    ORDER BY c.created_at DESC
");

// Get statistics
$stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total_details,
        COUNT(CASE WHEN insurance_status = 'active' THEN 1 END) as active_insurance,
        COUNT(CASE WHEN has_location_tracking = 'yes' THEN 1 END) as with_tracking,
        COUNT(CASE WHEN digital_signature = 'yes' THEN 1 END) as with_signature
    FROM contract_details
");

// Helper functions
function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

function getInsuranceStatusLabel($status)
{
    $labels = [
        'pending' => '<span class="badge bg-warning">Chờ xử lý</span>',
        'sent' => '<span class="badge bg-info">Đã gửi</span>',
        'active' => '<span class="badge bg-success">Hoạt động</span>',
        'expired' => '<span class="badge bg-danger">Hết hạn</span>'
    ];
    return $labels[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

function getContractStatusLabel($status)
{
    $labels = [
        'active' => '<span class="badge bg-success">Hoạt động</span>',
        'overdue' => '<span class="badge bg-danger">Quá hạn</span>',
        'warning' => '<span class="badge bg-warning">Cảnh báo</span>',
        'closed' => '<span class="badge bg-secondary">Đã đóng</span>',
        'defaulted' => '<span class="badge bg-danger">Vỡ nợ</span>'
    ];
    return $labels[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

function getYesNoLabel($value)
{
    return $value === 'yes' ?
        '<span class="badge bg-success">Có</span>' :
        '<span class="badge bg-secondary">Không</span>';
}

function getTrackingTypeLabel($type)
{
    $labels = [
        'real_time' => '<span class="badge bg-primary">Thời gian thực</span>',
        'periodic' => '<span class="badge bg-info">Định kỳ</span>',
        'on_demand' => '<span class="badge bg-warning">Theo yêu cầu</span>'
    ];
    return $labels[$type] ?? '<span class="badge bg-secondary">' . $type . '</span>';
}

function getLinkStatusLabel($status)
{
    return $status === 'linked' ?
        '<span class="badge bg-success">Đã liên kết</span>' :
        '<span class="badge bg-secondary">Chưa liên kết</span>';
}

$page_title = 'Chi tiết hợp đồng';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-contract"></i> Chi tiết hợp đồng
        </h1>
        <button class="btn btn-primary" onclick="showCreateDetailModal()">
            <i class="fas fa-plus"></i> Thêm chi tiết
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
                                Tổng chi tiết
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_details'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
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
                                Bảo hiểm hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['active_insurance'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
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
                                Có định vị
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['with_tracking'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
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
                                Chữ ký số
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['with_signature'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-signature fa-2x text-gray-300"></i>
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
                    <label class="form-label">Hợp đồng</label>
                    <select class="form-select" name="contract_id">
                        <option value="">Tất cả</option>
                        <?php foreach ($contracts as $contract): ?>
                            <option value="<?= $contract['id'] ?>" <?= $contract_filter == $contract['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($contract['contract_code']) ?> - <?= htmlspecialchars($contract['customer_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái BH</label>
                    <select class="form-select" name="insurance_status">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= $insurance_filter === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="sent" <?= $insurance_filter === 'sent' ? 'selected' : '' ?>>Đã gửi</option>
                        <option value="active" <?= $insurance_filter === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="expired" <?= $insurance_filter === 'expired' ? 'selected' : '' ?>>Hết hạn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Định vị</label>
                    <select class="form-select" name="has_location_tracking">
                        <option value="">Tất cả</option>
                        <option value="yes" <?= $tracking_filter === 'yes' ? 'selected' : '' ?>>Có</option>
                        <option value="no" <?= $tracking_filter === 'no' ? 'selected' : '' ?>>Không</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="index.php?page=contract-details" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contract Details Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Danh sách chi tiết hợp đồng</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Hợp đồng</th>
                            <th>Khách hàng</th>
                            <th>Cửa hàng</th>
                            <th>Bảo hiểm</th>
                            <th>Định vị</th>
                            <th>Liên kết</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $index => $detail): ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>
                                <td>
                                    <div><strong><?= htmlspecialchars($detail['contract_code']) ?></strong></div>
                                    <small class="text-muted"><?= formatCurrency($detail['amount']) ?></small>
                                    <div><?= getContractStatusLabel($detail['contract_status']) ?></div>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($detail['customer_name']) ?></strong></div>
                                    <small class="text-muted"><?= htmlspecialchars($detail['customer_phone']) ?></small>
                                    <?php if ($detail['customer_cif']): ?>
                                        <div><small class="text-info">CIF: <?= htmlspecialchars($detail['customer_cif']) ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($detail['store_name']) ?></strong></div>
                                    <small class="text-muted"><?= htmlspecialchars($detail['store_code']) ?></small>
                                </td>
                                <td>
                                    <div><?= getInsuranceStatusLabel($detail['insurance_status']) ?></div>
                                    <div>
                                        <small class="text-muted">
                                            Vật chất: <?= getYesNoLabel($detail['material_insurance']) ?>
                                        </small>
                                    </div>
                                    <div>
                                        <small class="text-muted">
                                            Y tế: <?= getYesNoLabel($detail['hospital_insurance']) ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div><?= getYesNoLabel($detail['has_location_tracking']) ?></div>
                                    <?php if ($detail['has_location_tracking'] === 'yes'): ?>
                                        <div><small class="text-muted"><?= getTrackingTypeLabel($detail['location_tracking_type']) ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>TIMA: <?= getLinkStatusLabel($detail['tima_customer_link']) ?></div>
                                    <div>NDT: <?= getLinkStatusLabel($detail['ndt_customer_link']) ?></div>
                                    <div>RE Data: <?= getYesNoLabel($detail['re_data_enabled']) ?></div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editDetail(<?= $detail['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteDetail(<?= $detail['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewDetail(<?= $detail['id'] ?>)">
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
                                <a class="page-link" href="?page=contract-details&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&contract_id=<?= urlencode($contract_filter) ?>&insurance_status=<?= urlencode($insurance_filter) ?>&has_location_tracking=<?= urlencode($tracking_filter) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=contract-details&p=<?= $i ?>&search=<?= urlencode($search) ?>&contract_id=<?= urlencode($contract_filter) ?>&insurance_status=<?= urlencode($insurance_filter) ?>&has_location_tracking=<?= urlencode($tracking_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=contract-details&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&contract_id=<?= urlencode($contract_filter) ?>&insurance_status=<?= urlencode($insurance_filter) ?>&has_location_tracking=<?= urlencode($tracking_filter) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="text-center text-muted mt-3">
                Hiển thị <?= $offset + 1 ?>-<?= min($offset + $per_page, $total_records) ?> trong tổng số <?= $total_records ?> chi tiết hợp đồng
            </div>
        </div>
    </div>
</div>

<!-- Modal tạo/sửa chi tiết hợp đồng -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalTitle">Thêm chi tiết hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="detailForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_detail">
                    <input type="hidden" name="detail_id" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hợp đồng *</label>
                                <select class="form-select" name="contract_id" required>
                                    <option value="">Chọn hợp đồng</option>
                                    <?php foreach ($contracts as $contract): ?>
                                        <option value="<?= $contract['id'] ?>">
                                            <?= htmlspecialchars($contract['contract_code']) ?> - <?= htmlspecialchars($contract['customer_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái bảo hiểm</label>
                                <select class="form-select" name="insurance_status">
                                    <option value="pending">Chờ xử lý</option>
                                    <option value="sent">Đã gửi</option>
                                    <option value="active">Hoạt động</option>
                                    <option value="expired">Hết hạn</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên cửa hàng</label>
                                <input type="text" class="form-control" name="store_name" placeholder="Nhập tên cửa hàng">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mã cửa hàng</label>
                                <input type="text" class="form-control" name="store_code" placeholder="Nhập mã cửa hàng">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bảo hiểm vật chất</label>
                                <select class="form-select" name="material_insurance">
                                    <option value="no">Không</option>
                                    <option value="yes">Có</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bảo hiểm y tế</label>
                                <select class="form-select" name="hospital_insurance">
                                    <option value="no">Không</option>
                                    <option value="yes">Có</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Chữ ký số</label>
                                <select class="form-select" name="digital_signature">
                                    <option value="no">Không</option>
                                    <option value="yes">Có</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Định vị</label>
                                <select class="form-select" name="has_location_tracking">
                                    <option value="no">Không</option>
                                    <option value="yes">Có</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Loại định vị</label>
                                <select class="form-select" name="location_tracking_type">
                                    <option value="real_time">Thời gian thực</option>
                                    <option value="periodic">Định kỳ</option>
                                    <option value="on_demand">Theo yêu cầu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mã đại lý TIMA</label>
                                <input type="text" class="form-control" name="tima_agent_code" placeholder="Nhập mã đại lý">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Liên kết TIMA</label>
                                <select class="form-select" name="tima_customer_link">
                                    <option value="unlinked">Chưa liên kết</option>
                                    <option value="linked">Đã liên kết</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Liên kết NDT</label>
                                <select class="form-select" name="ndt_customer_link">
                                    <option value="unlinked">Chưa liên kết</option>
                                    <option value="linked">Đã liên kết</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">RE Data</label>
                                <select class="form-select" name="re_data_enabled">
                                    <option value="no">Không</option>
                                    <option value="yes">Có</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="detailSubmitBtn">Tạo chi tiết</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết -->
<div class="modal fade" id="detailViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailViewContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showCreateDetailModal() {
        const modal = document.getElementById('detailModal');
        const form = document.getElementById('detailForm');
        const title = document.getElementById('detailModalTitle');
        const submitBtn = document.getElementById('detailSubmitBtn');

        // Reset form
        form.reset();
        form.querySelector('input[name="action"]').value = 'create_detail';
        form.querySelector('input[name="detail_id"]').value = '';

        title.textContent = 'Thêm chi tiết hợp đồng';
        submitBtn.textContent = 'Tạo chi tiết';

        new bootstrap.Modal(modal).show();
    }

    function editDetail(detailId) {
        // Load detail data via AJAX
        fetch(`api/contract-details.php?action=get_detail&id=${detailId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const detail = data.data;
                    const modal = document.getElementById('detailModal');
                    const form = document.getElementById('detailForm');
                    const title = document.getElementById('detailModalTitle');
                    const submitBtn = document.getElementById('detailSubmitBtn');

                    // Populate form
                    form.querySelector('input[name="action"]').value = 'update_detail';
                    form.querySelector('input[name="detail_id"]').value = detail.id;
                    form.querySelector('select[name="contract_id"]').value = detail.contract_id;
                    form.querySelector('select[name="insurance_status"]').value = detail.insurance_status;
                    form.querySelector('input[name="store_name"]').value = detail.store_name || '';
                    form.querySelector('input[name="store_code"]').value = detail.store_code || '';
                    form.querySelector('select[name="material_insurance"]').value = detail.material_insurance;
                    form.querySelector('select[name="hospital_insurance"]').value = detail.hospital_insurance;
                    form.querySelector('select[name="digital_signature"]').value = detail.digital_signature;
                    form.querySelector('select[name="has_location_tracking"]').value = detail.has_location_tracking;
                    form.querySelector('select[name="location_tracking_type"]').value = detail.location_tracking_type;
                    form.querySelector('input[name="tima_agent_code"]').value = detail.tima_agent_code || '';
                    form.querySelector('select[name="tima_customer_link"]').value = detail.tima_customer_link;
                    form.querySelector('select[name="ndt_customer_link"]').value = detail.ndt_customer_link;
                    form.querySelector('select[name="re_data_enabled"]').value = detail.re_data_enabled;

                    title.textContent = 'Cập nhật chi tiết hợp đồng';
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

    function deleteDetail(detailId) {
        if (confirm('Bạn có chắc muốn xóa chi tiết hợp đồng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_detail">
            <input type="hidden" name="detail_id" value="${detailId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewDetail(detailId) {
        fetch(`api/contract-details.php?action=get_detail&id=${detailId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const detail = data.data;
                    const content = document.getElementById('detailViewContent');

                    content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thông tin hợp đồng</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Mã hợp đồng:</strong></td><td>${detail.contract_code}</td></tr>
                                <tr><td><strong>Khách hàng:</strong></td><td>${detail.customer_name}</td></tr>
                                <tr><td><strong>Số tiền:</strong></td><td>${formatCurrency(detail.amount)}</td></tr>
                                <tr><td><strong>Trạng thái:</strong></td><td>${getContractStatusLabel(detail.contract_status)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin cửa hàng</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Tên cửa hàng:</strong></td><td>${detail.store_name || 'N/A'}</td></tr>
                                <tr><td><strong>Mã cửa hàng:</strong></td><td>${detail.store_code || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Bảo hiểm</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Trạng thái:</strong></td><td>${getInsuranceStatusLabel(detail.insurance_status)}</td></tr>
                                <tr><td><strong>Vật chất:</strong></td><td>${getYesNoLabel(detail.material_insurance)}</td></tr>
                                <tr><td><strong>Y tế:</strong></td><td>${getYesNoLabel(detail.hospital_insurance)}</td></tr>
                                <tr><td><strong>Chữ ký số:</strong></td><td>${getYesNoLabel(detail.digital_signature)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Định vị & Liên kết</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Định vị:</strong></td><td>${getYesNoLabel(detail.has_location_tracking)}</td></tr>
                                <tr><td><strong>Loại định vị:</strong></td><td>${getTrackingTypeLabel(detail.location_tracking_type)}</td></tr>
                                <tr><td><strong>TIMA:</strong></td><td>${getLinkStatusLabel(detail.tima_customer_link)}</td></tr>
                                <tr><td><strong>NDT:</strong></td><td>${getLinkStatusLabel(detail.ndt_customer_link)}</td></tr>
                                <tr><td><strong>RE Data:</strong></td><td>${getYesNoLabel(detail.re_data_enabled)}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Thông tin bổ sung</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Mã đại lý TIMA:</strong></td><td>${detail.tima_agent_code || 'N/A'}</td></tr>
                                <tr><td><strong>Ngày tạo:</strong></td><td>${new Date(detail.created_at).toLocaleString('vi-VN')}</td></tr>
                                <tr><td><strong>Cập nhật:</strong></td><td>${new Date(detail.updated_at).toLocaleString('vi-VN')}</td></tr>
                            </table>
                        </div>
                    </div>
                `;

                    new bootstrap.Modal(document.getElementById('detailViewModal')).show();
                } else {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải dữ liệu');
            });
    }

    function getContractStatusLabel(status) {
        const labels = {
            'active': '<span class="badge bg-success">Hoạt động</span>',
            'overdue': '<span class="badge bg-danger">Quá hạn</span>',
            'warning': '<span class="badge bg-warning">Cảnh báo</span>',
            'closed': '<span class="badge bg-secondary">Đã đóng</span>',
            'defaulted': '<span class="badge bg-danger">Vỡ nợ</span>'
        };
        return labels[status] || status;
    }

    function getInsuranceStatusLabel(status) {
        const labels = {
            'pending': '<span class="badge bg-warning">Chờ xử lý</span>',
            'sent': '<span class="badge bg-info">Đã gửi</span>',
            'active': '<span class="badge bg-success">Hoạt động</span>',
            'expired': '<span class="badge bg-danger">Hết hạn</span>'
        };
        return labels[status] || status;
    }

    function getYesNoLabel(value) {
        return value === 'yes' ?
            '<span class="badge bg-success">Có</span>' :
            '<span class="badge bg-secondary">Không</span>';
    }

    function getTrackingTypeLabel(type) {
        const labels = {
            'real_time': '<span class="badge bg-primary">Thời gian thực</span>',
            'periodic': '<span class="badge bg-info">Định kỳ</span>',
            'on_demand': '<span class="badge bg-warning">Theo yêu cầu</span>'
        };
        return labels[type] || type;
    }

    function getLinkStatusLabel(status) {
        return status === 'linked' ?
            '<span class="badge bg-success">Đã liên kết</span>' :
            '<span class="badge bg-secondary">Chưa liên kết</span>';
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // Reset modal when closed
    document.getElementById('detailModal').addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        form.reset();
        form.querySelector('input[name="action"]').value = 'create_detail';
        form.querySelector('input[name="detail_id"]').value = '';

        const title = this.querySelector('.modal-title');
        const submitBtn = this.querySelector('button[type="submit"]');
        title.textContent = 'Thêm chi tiết hợp đồng';
        submitBtn.textContent = 'Tạo chi tiết';
    });
</script>

<?php include '../includes/footer.php'; ?>