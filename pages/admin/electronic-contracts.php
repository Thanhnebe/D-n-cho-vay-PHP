<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $contractData = [
            'contract_code' => sanitize_input($_POST['contract_code']),
            'application_id' => intval($_POST['application_id']),
            'customer_id' => intval($_POST['customer_id']),
            'asset_id' => $_POST['asset_id'] ? intval($_POST['asset_id']) : null,
            'loan_amount' => floatval(str_replace(',', '', $_POST['loan_amount'])),
            'approved_amount' => floatval(str_replace(',', '', $_POST['approved_amount'])),
            'interest_rate_id' => intval($_POST['interest_rate_id']),
            'monthly_rate' => floatval($_POST['monthly_rate']),
            'daily_rate' => floatval($_POST['daily_rate']),
            'loan_term_months' => intval($_POST['loan_term_months']),
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'status' => $_POST['status'],
            'disbursement_status' => $_POST['disbursement_status'],
            'disbursed_amount' => isset($_POST['disbursed_amount']) && $_POST['disbursed_amount'] ? floatval(str_replace(',', '', $_POST['disbursed_amount'])) : null,
            'disbursed_date' => isset($_POST['disbursed_date']) && $_POST['disbursed_date'] ? $_POST['disbursed_date'] : null,
            'disbursed_by' => isset($_POST['disbursed_by']) && $_POST['disbursed_by'] ? intval($_POST['disbursed_by']) : null,
            'remaining_balance' => isset($_POST['remaining_balance']) && $_POST['remaining_balance'] ? floatval(str_replace(',', '', $_POST['remaining_balance'])) : null,
            'total_paid' => isset($_POST['total_paid']) && $_POST['total_paid'] ? floatval(str_replace(',', '', $_POST['total_paid'])) : 0.00,
            'next_payment_date' => isset($_POST['next_payment_date']) && $_POST['next_payment_date'] ? $_POST['next_payment_date'] : null,
            'monthly_payment' => isset($_POST['monthly_payment']) && $_POST['monthly_payment'] ? floatval(str_replace(',', '', $_POST['monthly_payment'])) : null,
            'customer_signature' => sanitize_input($_POST['customer_signature']),
            'company_signature' => sanitize_input($_POST['company_signature']),
            'signed_date' => isset($_POST['signed_date']) && $_POST['signed_date'] ? $_POST['signed_date'] : null,
            'created_by' => $_SESSION['user_id'] ?? 1,
            'approved_by' => isset($_POST['approved_by']) && $_POST['approved_by'] ? intval($_POST['approved_by']) : null
        ];

        if ($action === 'add') {
            $contractId = $db->insert('electronic_contracts', $contractData);
            if ($contractId) {
                $message = 'Tạo hợp đồng điện tử thành công!';
                $messageType = 'success';
            } else {
                $message = 'Có lỗi xảy ra khi tạo hợp đồng!';
                $messageType = 'error';
            }
        } else {
            $contractId = $_POST['contract_id'];
            $result = $db->update('electronic_contracts', $contractData, 'id = :id', ['id' => $contractId]);
            if ($result) {
                $message = 'Cập nhật hợp đồng thành công!';
                $messageType = 'success';
            } else {
                $message = 'Có lỗi xảy ra khi cập nhật hợp đồng!';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $contractId = $_POST['contract_id'];
        $result = $db->delete('electronic_contracts', 'id = :id', ['id' => $contractId]);
        if ($result) {
            $message = 'Xóa hợp đồng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Có lỗi xảy ra khi xóa hợp đồng!';
            $messageType = 'error';
        }
    } elseif ($action === 'disburse') {
        $contractId = $_POST['contract_id'];
        $disbursedAmount = floatval(str_replace(',', '', $_POST['disbursed_amount']));
        $disbursedDate = date('Y-m-d H:i:s');

        $updateData = [
            'disbursement_status' => 'disbursed',
            'disbursed_amount' => $disbursedAmount,
            'disbursed_date' => $disbursedDate,
            'disbursed_by' => $_SESSION['user_id'] ?? 1,
            'status' => 'active'
        ];

        $result = $db->update('electronic_contracts', $updateData, 'id = :id', ['id' => $contractId]);
        if ($result) {
            $message = 'Giải ngân hợp đồng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Có lỗi xảy ra khi giải ngân!';
            $messageType = 'error';
        }
    }
}

// Kiểm tra nếu được redirect từ trang phê duyệt
if (isset($_GET['from_approval']) && $_GET['from_approval'] == 1 && $action === 'edit') {
    $message = 'Phê duyệt đơn vay thành công! Hợp đồng điện tử đã được tạo. Bạn có thể tiếp tục chỉnh sửa và hoàn thiện hợp đồng.';
    $messageType = 'success';
}

// Xử lý tìm kiếm và lọc
$whereConditions = [];
$params = [];

// Tìm kiếm theo từ khóa
if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $whereConditions[] = "(ec.contract_code LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Lọc theo trạng thái
if (!empty($_GET['status'])) {
    $whereConditions[] = "ec.status = ?";
    $params[] = $_GET['status'];
}

// Lọc theo trạng thái giải ngân
if (!empty($_GET['disbursement_status'])) {
    $whereConditions[] = "ec.disbursement_status = ?";
    $params[] = $_GET['disbursement_status'];
}

// Lọc theo khoảng thời gian
if (!empty($_GET['date_range'])) {
    $dateRange = $_GET['date_range'];
    switch ($dateRange) {
        case 'today':
            $whereConditions[] = "DATE(ec.created_at) = CURDATE()";
            break;
        case 'week':
            $whereConditions[] = "ec.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $whereConditions[] = "ec.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'quarter':
            $whereConditions[] = "ec.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
            break;
    }
}

// Tạo câu WHERE
$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Lấy danh sách hợp đồng điện tử với điều kiện tìm kiếm
$contracts = $db->fetchAll("
    SELECT ec.*, 
           c.name as customer_name, 
           c.phone as customer_phone,
           a.name as asset_name,
           ir.description as rate_description,
           u1.name as created_by_name,
           u2.name as approved_by_name,
           u3.name as disbursed_by_name
    FROM electronic_contracts ec
    LEFT JOIN customers c ON ec.customer_id = c.id
    LEFT JOIN assets a ON ec.asset_id = a.id
    LEFT JOIN interest_rates ir ON ec.interest_rate_id = ir.id
    LEFT JOIN users u1 ON ec.created_by = u1.id
    LEFT JOIN users u2 ON ec.approved_by = u2.id
    LEFT JOIN users u3 ON ec.disbursed_by = u3.id
    $whereClause
    ORDER BY ec.created_at DESC
", $params);

// Lấy thông tin hợp đồng để edit
$contract = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $contract = $db->fetchOne("SELECT * FROM electronic_contracts WHERE id = ?", [$_GET['id']]);
}

// Lấy danh sách khách hàng
$customers = $db->fetchAll("SELECT id, name, phone FROM customers ORDER BY name");

// Lấy danh sách tài sản (bao gồm cả tài sản đang được sử dụng trong hợp đồng hiện tại)
if ($action === 'edit' && isset($_GET['id'])) {
    $assets = $db->fetchAll("SELECT id, name FROM assets WHERE status = 'available' OR id = (SELECT asset_id FROM electronic_contracts WHERE id = ?) ORDER BY name", [$_GET['id']]);
} else {
    $assets = $db->fetchAll("SELECT id, name FROM assets WHERE status = 'available' ORDER BY name");
}

// Lấy danh sách lãi suất
$interestRates = $db->fetchAll("SELECT id, description, monthly_rate, daily_rate FROM interest_rates WHERE status = 'active' ORDER BY description");

// Lấy danh sách người dùng
$users = $db->fetchAll("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");

// Lấy danh sách đơn vay
$applications = $db->fetchAll("SELECT id, application_code, customer_id FROM loan_applications ORDER BY created_at DESC");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Quản lý hợp đồng điện tử</h1>
                    <p class="mb-0">Quản lý thông tin hợp đồng vay điện tử</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="addContract()">
                    <i class="fas fa-plus me-2"></i>Tạo hợp đồng mới
                </button>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tổng hợp đồng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count($contracts); ?>
                                </div>
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
                                    Đã phê duyệt
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count(array_filter($contracts, function ($c) {
                                        return $c['status'] === 'active';
                                    })); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                    Chờ phê duyệt
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count(array_filter($contracts, function ($c) {
                                        return in_array($c['status'], ['draft', 'pending_approval']);
                                    })); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    Đã giải ngân
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo count(array_filter($contracts, function ($c) {
                                        return $c['disbursement_status'] === 'disbursed';
                                    })); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
            </div>
            <div class="card-body">
                <form id="searchForm" onsubmit="return performSearch(event)">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="search" class="form-label">Tìm kiếm</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="<?php echo $_GET['search'] ?? ''; ?>"
                                    placeholder="Mã hợp đồng, khách hàng...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">Tất cả</option>
                                    <option value="draft" <?php echo ($_GET['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Nháp</option>
                                    <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="disbursed" <?php echo ($_GET['status'] ?? '') === 'disbursed' ? 'selected' : ''; ?>>Đã giải ngân</option>
                                    <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                                    <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                    <option value="overdue" <?php echo ($_GET['status'] ?? '') === 'overdue' ? 'selected' : ''; ?>>Quá hạn</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="disbursement_status" class="form-label">Trạng thái giải ngân</label>
                                <select class="form-control" id="disbursement_status" name="disbursement_status">
                                    <option value="">Tất cả</option>
                                    <option value="pending" <?php echo ($_GET['disbursement_status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Chờ giải ngân</option>
                                    <option value="disbursed" <?php echo ($_GET['disbursement_status'] ?? '') === 'disbursed' ? 'selected' : ''; ?>>Đã giải ngân</option>
                                    <option value="cancelled" <?php echo ($_GET['disbursement_status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="date_range" class="form-label">Khoảng thời gian</label>
                                <select class="form-control" id="date_range" name="date_range">
                                    <option value="">Tất cả</option>
                                    <option value="today" <?php echo ($_GET['date_range'] ?? '') === 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                                    <option value="week" <?php echo ($_GET['date_range'] ?? '') === 'week' ? 'selected' : ''; ?>>Tuần này</option>
                                    <option value="month" <?php echo ($_GET['date_range'] ?? '') === 'month' ? 'selected' : ''; ?>>Tháng này</option>
                                    <option value="quarter" <?php echo ($_GET['date_range'] ?? '') === 'quarter' ? 'selected' : ''; ?>>Quý này</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Tìm kiếm
                        </button>
                        <a href="?page=electronic-contracts" class="btn btn-secondary">
                            <i class="fas fa-refresh me-1"></i>Làm mới
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danh sách hợp đồng -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách hợp đồng điện tử</h6>
            </div>
            <div class="card-body">
                <?php if (empty($contracts)): ?>
                    <p class="text-muted text-center">Chưa có hợp đồng nào</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Mã hợp đồng</th>
                                    <th>Khách hàng</th>
                                    <th>Số tiền vay</th>
                                    <th>Lãi suất</th>
                                    <th>Thời hạn</th>
                                    <th>Trạng thái phê duyệt</th>
                                    <th>Trạng thái giải ngân</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($contract['contract_code']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($contract['customer_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($contract['customer_phone']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo format_currency($contract['approved_amount']); ?></strong>
                                                <br>
                                                <small class="text-muted">Vay: <?php echo format_currency($contract['loan_amount']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo $contract['monthly_rate']; ?>%/tháng</strong>
                                                <br>
                                                <small class="text-muted"><?php echo $contract['daily_rate']; ?>%/ngày</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo $contract['loan_term_months']; ?> tháng</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($contract['start_date'])); ?> -
                                                    <?php echo date('d/m/Y', strtotime($contract['end_date'])); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php
                                                                        echo $contract['status'] === 'active' ? 'success' : ($contract['status'] === 'draft' ? 'secondary' : ($contract['status'] === 'pending_approval' ? 'warning' : ($contract['status'] === 'cancelled' ? 'danger' : 'info')));
                                                                        ?>">
                                                <?php
                                                echo $contract['status'] === 'active' ? 'Đã phê duyệt' : ($contract['status'] === 'draft' ? 'Nháp' : ($contract['status'] === 'pending_approval' ? 'Chờ phê duyệt' : ($contract['status'] === 'cancelled' ? 'Đã từ chối' : 'Hoàn thành')));
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php
                                                                        echo $contract['disbursement_status'] === 'disbursed' ? 'success' : ($contract['disbursement_status'] === 'pending' ? 'warning' : 'danger');
                                                                        ?>">
                                                <?php
                                                echo $contract['disbursement_status'] === 'disbursed' ? 'Đã giải ngân' : ($contract['disbursement_status'] === 'pending' ? 'Chờ giải ngân' : 'Đã hủy');
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($contract['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="viewContract(<?php echo $contract['id']; ?>)" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="editContract(<?php echo $contract['id']; ?>)" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if (in_array($contract['status'], ['draft', 'pending_approval'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="showApprovalModal(<?php echo $contract['id']; ?>)" title="Phê duyệt">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($contract['status'] === 'active' && $contract['disbursement_status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="showDisburseModal(<?php echo $contract['id']; ?>)" title="Giải ngân">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($contract['status'] === 'active'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="generateOTP(<?php echo $contract['id']; ?>)" title="Gửi OTP ký hợp đồng">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    onclick="showApprovalHistory(<?php echo $contract['id']; ?>)" title="Lịch sử phê duyệt">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteContract(<?php echo $contract['id']; ?>)" title="Xóa">
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
        </div>


    <?php endif; ?>
</div>

<!-- Modal Thêm/Sửa Hợp đồng -->
<div class="modal fade" id="contractModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contractModalTitle">Tạo hợp đồng điện tử</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="contractForm" method="POST" action="pages/admin/api/electronic-contracts.php">
                <div class="modal-body">
                    <input type="hidden" id="contract_action" name="contract_action" value="add">
                    <input type="hidden" id="contract_id" name="contract_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_contract_code" class="form-label">Mã hợp đồng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="modal_contract_code" name="contract_code" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_application_id" class="form-label">Đơn vay</label>
                                <select class="form-control" id="modal_application_id" name="application_id">
                                    <option value="">Chọn đơn vay</option>
                                    <?php foreach ($applications as $app): ?>
                                        <option value="<?php echo $app['id']; ?>">
                                            <?php echo htmlspecialchars($app['application_code']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_customer_id" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                                <select class="form-control" id="modal_customer_id" name="customer_id" required>
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['name'] . ' - ' . $customer['phone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_asset_id" class="form-label">Tài sản cầm cố</label>
                                <select class="form-control" id="modal_asset_id" name="asset_id">
                                    <option value="">Chọn tài sản</option>
                                    <?php foreach ($assets as $asset): ?>
                                        <option value="<?php echo $asset['id']; ?>">
                                            <?php echo htmlspecialchars($asset['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_loan_amount" class="form-label">Số tiền vay <span class="text-danger">*</span></label>
                                <input type="text" class="form-control currency-input" id="modal_loan_amount" name="loan_amount" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_approved_amount" class="form-label">Số tiền được duyệt <span class="text-danger">*</span></label>
                                <input type="text" class="form-control currency-input" id="modal_approved_amount" name="approved_amount" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_interest_rate_id" class="form-label">Lãi suất <span class="text-danger">*</span></label>
                                <select class="form-control" id="modal_interest_rate_id" name="interest_rate_id" required>
                                    <option value="">Chọn lãi suất</option>
                                    <?php foreach ($interestRates as $rate): ?>
                                        <option value="<?php echo $rate['id']; ?>"
                                            data-monthly="<?php echo $rate['monthly_rate']; ?>"
                                            data-daily="<?php echo $rate['daily_rate']; ?>">
                                            <?php echo htmlspecialchars($rate['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_monthly_rate" class="form-label">Lãi suất tháng (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="modal_monthly_rate" name="monthly_rate" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_daily_rate" class="form-label">Lãi suất ngày (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.0001" class="form-control" id="modal_daily_rate" name="daily_rate" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_loan_term_months" class="form-label">Thời hạn vay (tháng) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="modal_loan_term_months" name="loan_term_months" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_start_date" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="modal_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="modal_end_date" class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="modal_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-control" id="modal_status" name="status" required>
                                    <option value="draft">Nháp</option>
                                    <option value="active">Hoạt động</option>
                                    <option value="completed">Hoàn thành</option>
                                    <option value="cancelled">Đã hủy</option>
                                    <option value="overdue">Quá hạn</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="modal_disbursement_status" class="form-label">Trạng thái giải ngân</label>
                                <select class="form-control" id="modal_disbursement_status" name="disbursement_status">
                                    <option value="pending">Chờ giải ngân</option>
                                    <option value="disbursed">Đã giải ngân</option>
                                    <option value="cancelled">Đã hủy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="submitContractBtn">
                        <i class="fas fa-save me-2"></i>Lưu hợp đồng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xem Chi tiết Hợp đồng -->
<div class="modal fade" id="viewContractModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết hợp đồng điện tử</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewContractContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <div id="downloadButtonContainer" style="display: none;">
                    <button type="button" class="btn btn-success" id="downloadContractBtn" onclick="downloadContractFromModal()">
                        <i class="fas fa-download me-2"></i>Tải xuống hợp đồng
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal OTP Verification -->
<div class="modal fade" id="otpModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Xác thực OTP để tải hợp đồng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="otp_contract_id">

                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>Thông tin quan trọng
                    </h6>
                    <ul class="mb-0">
                        <li>Mã OTP đã được gửi đến email của khách hàng</li>
                        <li>Mã OTP có hiệu lực trong <strong>1 phút</strong></li>
                        <li>Khách hàng chỉ có <strong>3 lần</strong> nhập sai</li>
                        <li>Sau khi xác thực thành công, hợp đồng sẽ được tải xuống tự động</li>
                    </ul>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Nhập mã OTP</h5>
                                <p class="card-text text-muted">Vui lòng nhập mã OTP gồm 6 chữ số</p>

                                <div class="mb-3">
                                    <input type="text"
                                        class="form-control form-control-lg text-center"
                                        id="otp_input"
                                        maxlength="6"
                                        placeholder="000000"
                                        style="font-size: 24px; letter-spacing: 10px; font-weight: bold;"
                                        autocomplete="off">
                                </div>

                                <div class="alert alert-danger" id="otp_error" style="display: none;"></div>

                                <button type="button" class="btn btn-primary btn-lg" id="verify_otp_btn" onclick="verifyOTP()">
                                    <i class="fas fa-check me-2"></i>Xác thực
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6>Lưu ý bảo mật:</h6>
                    <div class="alert alert-warning">
                        <ul class="mb-0">
                            <li>Chỉ nhập OTP từ email chính thức của công ty</li>
                            <li>Không chia sẻ mã OTP với bất kỳ ai</li>
                            <li>Nếu không nhận được email, vui lòng kiểm tra thư mục spam</li>
                            <li>Liên hệ hotline nếu cần hỗ trợ: <strong>1900-xxxx</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-outline-primary" onclick="sendOTPForContract(document.getElementById('otp_contract_id').value)">
                    <i class="fas fa-redo me-2"></i>Gửi lại OTP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal phê duyệt -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Phê duyệt hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="approval_contract_id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="approval_action" class="form-label">Hành động <span class="text-danger">*</span></label>
                            <select class="form-control" id="approval_action" required>
                                <option value="">Chọn hành động</option>
                                <option value="approve">Phê duyệt</option>
                                <option value="reject">Từ chối</option>
                                <option value="request_info">Yêu cầu bổ sung thông tin</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="approval_level" class="form-label">Cấp độ phê duyệt <span class="text-danger">*</span></label>
                            <select class="form-control" id="approval_level" required>
                                <option value="">Chọn cấp độ</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="approved_amount" class="form-label">Số tiền phê duyệt</label>
                            <input type="text" class="form-control currency-input" id="approved_amount">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="approval_comments" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="approval_comments" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div id="approval_info" class="alert alert-info" style="display: none;">
                    <h6>Thông tin phê duyệt:</h6>
                    <div id="approval_details"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" onclick="submitApproval()">
                    <i class="fas fa-check me-2"></i>Xác nhận phê duyệt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal lịch sử phê duyệt -->
<div class="modal fade" id="approvalHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lịch sử phê duyệt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="approval_history_content">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal giải ngân -->
<div class="modal fade" id="disburseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Giải ngân hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?page=electronic-contracts&action=disburse">
                <div class="modal-body">
                    <input type="hidden" name="contract_id" id="disburse_contract_id">
                    <div class="mb-3">
                        <label for="disbursed_amount" class="form-label">Số tiền giải ngân <span class="text-danger">*</span></label>
                        <input type="text" class="form-control currency-input" id="disbursed_amount" name="disbursed_amount" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-money-bill-wave me-2"></i>Giải ngân
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Generate OTP -->
<div class="modal fade" id="otpGenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gửi mã OTP cho khách hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="otp_contract_id">
                <div class="mb-3">
                    <label class="form-label">Phương thức gửi OTP:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="send_method" id="method_email" value="email" checked>
                        <label class="form-check-label" for="method_email">Email</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="send_method" id="method_sms" value="sms">
                        <label class="form-check-label" for="method_sms">SMS</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="send_method" id="method_both" value="both">
                        <label class="form-check-label" for="method_both">Cả Email và SMS</label>
                    </div>
                </div>
                <div class="alert alert-info">
                    <small>Mã OTP sẽ có hiệu lực trong 1 phút và được gửi đến thông tin liên lạc của khách hàng.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="sendOTP()">
                    <i class="fas fa-paper-plane me-2"></i>Gửi OTP
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal OTP Verification for Customer -->
<div class="modal fade" id="otpVerifyModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt me-2"></i>Xác thực OTP - Ký hợp đồng điện tử
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h6>Mã OTP đã được gửi thành công!</h6>
                        <p class="mb-0">Vui lòng kiểm tra email/SMS và nhập mã OTP để xác nhận ký hợp đồng.</p>
                    </div>
                </div>

                <input type="hidden" id="verify_contract_id">
                <div class="mb-3">
                    <label for="otp_input_verify" class="form-label">Nhập mã OTP (6 số):</label>
                    <input type="text" class="form-control text-center" id="otp_input_verify" maxlength="6"
                        style="font-size: 1.5rem; letter-spacing: 0.5rem;" placeholder="000000"
                        oninput="debugOTPInput(this)">
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Mã OTP có hiệu lực:</small>
                        <span id="countdown" class="badge bg-warning">60s</span>
                    </div>
                </div>

                <div class="alert alert-warning" style="display: none;" id="otp_expired">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Mã OTP đã hết hạn. Vui lòng yêu cầu gửi lại mã mới.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="resend_otp_btn" onclick="resendOTP()" disabled>
                    <i class="fas fa-redo me-2"></i>Gửi lại OTP
                </button>
                <button type="button" class="btn btn-success" onclick="verifyOTP()">
                    <i class="fas fa-check me-2"></i>Xác thực và tải hợp đồng
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Contract Management Functions
    function addContract() {
        document.getElementById('contractModalTitle').textContent = 'Tạo hợp đồng điện tử';
        document.getElementById('submitContractBtn').innerHTML = '<i class="fas fa-save me-2"></i>Tạo hợp đồng';
        document.getElementById('contractForm').reset();

        // Set action and clear contract ID
        document.getElementById('contract_action').value = 'add';
        document.getElementById('contract_id').value = '';

        // Generate contract code for new contract
        const today = new Date();
        const contractCode = 'CT' + today.getFullYear() +
            String(today.getMonth() + 1).padStart(2, '0') +
            String(today.getDate()).padStart(2, '0') +
            Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        document.getElementById('modal_contract_code').value = contractCode;

        // Set default dates
        document.getElementById('modal_start_date').value = today.toISOString().split('T')[0];

        new bootstrap.Modal(document.getElementById('contractModal')).show();
    }

    function editContract(contractId) {
        document.getElementById('contractModalTitle').textContent = 'Chỉnh sửa hợp đồng điện tử';
        document.getElementById('contract_action').value = 'edit';
        document.getElementById('contract_id').value = contractId;
        document.getElementById('submitContractBtn').innerHTML = '<i class="fas fa-save me-2"></i>Cập nhật hợp đồng';

        // Load contract data
        fetch(`pages/admin/api/electronic-contracts.php?action=get_contract&id=${contractId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contract = data.data;

                    // Populate form fields
                    document.getElementById('modal_contract_code').value = contract.contract_code || '';
                    document.getElementById('modal_application_id').value = contract.application_id || '';
                    document.getElementById('modal_customer_id').value = contract.customer_id || '';
                    document.getElementById('modal_asset_id').value = contract.asset_id || '';
                    document.getElementById('modal_loan_amount').value = contract.loan_amount ? formatNumber(contract.loan_amount) : '';
                    document.getElementById('modal_approved_amount').value = contract.approved_amount ? formatNumber(contract.approved_amount) : '';
                    document.getElementById('modal_interest_rate_id').value = contract.interest_rate_id || '';
                    document.getElementById('modal_monthly_rate').value = contract.monthly_rate || '';
                    document.getElementById('modal_daily_rate').value = contract.daily_rate || '';
                    document.getElementById('modal_loan_term_months').value = contract.loan_term_months || '';
                    document.getElementById('modal_start_date').value = contract.start_date || '';
                    document.getElementById('modal_end_date').value = contract.end_date || '';
                    document.getElementById('modal_status').value = contract.status || 'draft';
                    document.getElementById('modal_disbursement_status').value = contract.disbursement_status || 'pending';

                    new bootstrap.Modal(document.getElementById('contractModal')).show();
                } else {
                    alert('Không thể tải thông tin hợp đồng: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải thông tin hợp đồng');
            });
    }

    function viewContract(contractId) {
        const modal = new bootstrap.Modal(document.getElementById('viewContractModal'));
        modal.show();

        // Ẩn nút download khi xem thông thường
        document.getElementById('downloadButtonContainer').style.display = 'none';

        document.getElementById('viewContractContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2">Đang tải thông tin hợp đồng...</p>
            </div>
        `;

        fetch(`pages/admin/api/electronic-contracts.php?action=get_contract_detail&id=${contractId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('viewContractContent').innerHTML = data.html;
                } else {
                    document.getElementById('viewContractContent').innerHTML =
                        '<p class="text-danger text-center">Không thể tải thông tin hợp đồng</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('viewContractContent').innerHTML =
                    '<p class="text-danger text-center">Có lỗi xảy ra khi tải thông tin hợp đồng</p>';
            });
    }

    function viewContractWithDownload(contractId) {
        const modal = new bootstrap.Modal(document.getElementById('viewContractModal'));
        modal.show();

        // Lưu contract ID để sử dụng trong download
        window.currentContractId = contractId;

        document.getElementById('viewContractContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2">Đang tải thông tin hợp đồng...</p>
            </div>
        `;

        fetch(`pages/admin/api/electronic-contracts.php?action=get_contract_detail&id=${contractId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Thêm thông báo xác thực OTP thành công
                    let html = data.html;
                    if (window.verifiedOTP && window.verifiedOTP.contractId == contractId) {
                        html += `
                            <div class="mt-4 p-3 bg-success text-white rounded">
                                <h6><i class="fas fa-check-circle me-2"></i>Xác thực OTP thành công!</h6>
                                <p class="mb-3">Hợp đồng đã được ký điện tử và cập nhật trạng thái thành <strong>Hoàn thành</strong>.</p>
                                <p class="mb-3">Hợp đồng sẵn sàng để tải xuống.</p>
                            </div>
                        `;

                        // Hiển thị nút download trong modal footer
                        document.getElementById('downloadButtonContainer').style.display = 'block';
                    } else {
                        // Ẩn nút download nếu chưa xác thực OTP
                        document.getElementById('downloadButtonContainer').style.display = 'none';
                    }
                    document.getElementById('viewContractContent').innerHTML = html;
                } else {
                    document.getElementById('viewContractContent').innerHTML =
                        '<p class="text-danger text-center">Không thể tải thông tin hợp đồng</p>';
                    document.getElementById('downloadButtonContainer').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('viewContractContent').innerHTML =
                    '<p class="text-danger text-center">Có lỗi xảy ra khi tải thông tin hợp đồng</p>';
                document.getElementById('downloadButtonContainer').style.display = 'none';
            });
    }

    function deleteContract(contractId) {
        if (confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=electronic-contracts&action=delete';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'contract_id';
            input.value = contractId;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Handle form submission
    document.getElementById('contractForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('submitContractBtn');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
        submitBtn.disabled = true;

        fetch(this.getAttribute('action'), {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', text);
                        throw new Error('Invalid JSON response');
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success) {
                    alert(data.message || 'Thao tác thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi yêu cầu: ' + error.message);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Utility functions
    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // OTP Functions
    let countdownTimer = null;

    function generateOTP(contractId) {
        document.getElementById('otp_contract_id').value = contractId;
        new bootstrap.Modal(document.getElementById('otpGenerateModal')).show();
    }

    function sendOTP() {
        const contractId = document.getElementById('otp_contract_id').value;
        const sendMethod = document.querySelector('input[name="send_method"]:checked').value;

        const formData = new FormData();
        formData.append('action', 'generate_otp');
        formData.append('contract_id', contractId);
        formData.append('send_method', sendMethod);

        fetch('pages/admin/api/otp-verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Đóng modal generate và mở modal verify
                    bootstrap.Modal.getInstance(document.getElementById('otpGenerateModal')).hide();

                    document.getElementById('verify_contract_id').value = contractId;
                    document.getElementById('otp_input').value = '';

                    const verifyModal = new bootstrap.Modal(document.getElementById('otpVerifyModal'));
                    verifyModal.show();

                    // Bắt đầu countdown
                    startCountdown(60);

                    alert(data.message);
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi OTP');
            });
    }

    function verifyOTP() {
        const contractId = document.getElementById('verify_contract_id').value;
        // Try both input fields
        const otpInput = document.getElementById('otp_input_verify') || document.getElementById('otp_input');
        const otpCode = otpInput.value.trim();

        console.log('OTP Debug:', {
            raw: otpInput.value,
            trimmed: otpCode,
            length: otpCode.length,
            bytes: Array.from(otpCode).map(c => c.charCodeAt(0))
        });

        // Kiểm tra độ dài
        if (otpCode.length !== 6) {
            console.log('Length validation failed:', otpCode.length);
            alert('Vui lòng nhập đầy đủ 6 số mã OTP');
            return;
        }

        // Kiểm tra chỉ chứa số
        if (!/^\d{6}$/.test(otpCode)) {
            console.log('Digit validation failed:', otpCode);
            alert('Mã OTP chỉ được chứa số');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'verify_otp');
        formData.append('contract_id', contractId);
        formData.append('otp_code', otpCode);

        fetch('pages/admin/api/otp-verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);

                    // Lưu thông tin OTP để sử dụng sau
                    window.verifiedOTP = {
                        contractId: contractId,
                        otpId: data.otp_id,
                        downloadUrl: data.download_url,
                        contractStatus: data.contract_status
                    };

                    // Đóng modal OTP và mở modal chi tiết hợp đồng
                    bootstrap.Modal.getInstance(document.getElementById('otpVerifyModal')).hide();

                    // Mở modal chi tiết hợp đồng với nút download
                    viewContractWithDownload(contractId);

                    // Clear countdown
                    if (countdownTimer) {
                        clearInterval(countdownTimer);
                    }

                    // Reload trang sau 2 giây để hiển thị trạng thái mới
                    setTimeout(() => {
                        location.reload();
                    }, 2000);

                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xác thực OTP');
            });
    }

    function resendOTP() {
        const contractId = document.getElementById('verify_contract_id').value;
        const sendMethod = document.querySelector('input[name="send_method"]:checked').value;

        const formData = new FormData();
        formData.append('action', 'generate_otp');
        formData.append('contract_id', contractId);
        formData.append('send_method', sendMethod);

        fetch('pages/admin/api/otp-verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Mã OTP mới đã được gửi!');
                    startCountdown(60);
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi lại OTP');
            });
    }

    function startCountdown(seconds) {
        const countdownElement = document.getElementById('countdown');
        const resendBtn = document.getElementById('resend_otp_btn');
        const otpExpiredAlert = document.getElementById('otp_expired');

        let timeLeft = seconds;

        // Clear existing timer
        if (countdownTimer) {
            clearInterval(countdownTimer);
        }

        // Reset UI
        resendBtn.disabled = true;
        otpExpiredAlert.style.display = 'none';

        countdownTimer = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft + 's';

            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                countdownElement.textContent = 'Hết hạn';
                countdownElement.className = 'badge bg-danger';
                resendBtn.disabled = false;
                otpExpiredAlert.style.display = 'block';
            }
        }, 1000);
    }

    // Debug OTP input
    function debugOTPInput(input) {
        console.log('OTP Input Debug:', {
            value: input.value,
            trimmed: input.value.trim(),
            length: input.value.length,
            trimmedLength: input.value.trim().length,
            bytes: Array.from(input.value).map(c => c.charCodeAt(0))
        });
    }

    // Auto format OTP input
    document.addEventListener('DOMContentLoaded', function() {
        // Handle both OTP input fields
        const otpInputs = [
            document.getElementById('otp_input'),
            document.getElementById('otp_input_verify')
        ];

        otpInputs.forEach(otpInput => {
            if (otpInput) {
                otpInput.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');

                    // Auto verify when 6 digits entered (disabled for security)
                    // if (this.value.length === 6) {
                    //     setTimeout(() => {
                    //         verifyOTP();
                    //     }, 500);
                    // }
                });
            }
        });
    });

    function showApprovalModal(contractId) {
        document.getElementById('approval_contract_id').value = contractId;

        // Load approval roles
        fetch('pages/admin/api/electronic-contracts.php?action=get_approval_roles')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const levelSelect = document.getElementById('approval_level');
                    levelSelect.innerHTML = '<option value="">Chọn cấp độ</option>';
                    data.data.forEach(role => {
                        const option = document.createElement('option');
                        option.value = role.id;
                        option.textContent = `${role.name} (${formatCurrency(role.min_amount)} - ${formatCurrency(role.max_amount)})`;
                        levelSelect.appendChild(option);
                    });
                }
            });

        new bootstrap.Modal(document.getElementById('approvalModal')).show();
    }

    function showApprovalHistory(contractId) {
        fetch(`pages/admin/api/electronic-contracts.php?action=get_approval_history&contract_id=${contractId}`)
            .then(response => response.json())
            .then(data => {
                const content = document.getElementById('approval_history_content');
                if (data.success && data.data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped">';
                    html += '<thead><tr><th>Thời gian</th><th>Người phê duyệt</th><th>Cấp độ</th><th>Hành động</th><th>Số tiền</th><th>Ghi chú</th></tr></thead><tbody>';

                    data.data.forEach(approval => {
                        const actionClass = approval.action === 'approve' ? 'success' :
                            (approval.action === 'reject' ? 'danger' : 'warning');
                        const actionText = approval.action === 'approve' ? 'Phê duyệt' :
                            (approval.action === 'reject' ? 'Từ chối' : 'Yêu cầu thông tin');

                        html += `<tr>
                        <td>${new Date(approval.approval_date).toLocaleString('vi-VN')}</td>
                        <td>${approval.approver_name || 'N/A'}</td>
                        <td>${approval.role_name || 'N/A'}</td>
                        <td><span class="badge badge-${actionClass}">${actionText}</span></td>
                        <td>${approval.approved_amount ? formatCurrency(approval.approved_amount) : '-'}</td>
                        <td>${approval.comments || '-'}</td>
                    </tr>`;
                    });

                    html += '</tbody></table></div>';
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<p class="text-muted text-center">Chưa có lịch sử phê duyệt</p>';
                }
            })
            .catch(error => {
                document.getElementById('approval_history_content').innerHTML =
                    '<p class="text-danger text-center">Có lỗi xảy ra khi tải lịch sử phê duyệt</p>';
            });

        new bootstrap.Modal(document.getElementById('approvalHistoryModal')).show();
    }

    function submitForApproval(contractId) {
        if (confirm('Bạn có chắc chắn muốn trình duyệt hợp đồng này?')) {
            const data = {
                contract_id: contractId,
                approval_level: 1, // Initial level
                action: 'submit',
                approved_amount: 0,
                comments: 'Hợp đồng đã được trình duyệt'
            };

            fetch('pages/admin/api/electronic-contracts.php?action=approve_contract', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Trình duyệt thành công!');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.error || 'Không xác định'));
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra khi gửi yêu cầu');
                });
        }
    }

    function submitApproval() {
        const contractId = document.getElementById('approval_contract_id').value;
        const action = document.getElementById('approval_action').value;
        const level = document.getElementById('approval_level').value;
        const amount = document.getElementById('approved_amount').value;
        const comments = document.getElementById('approval_comments').value;

        if (!action || !level) {
            alert('Vui lòng điền đầy đủ thông tin bắt buộc');
            return;
        }

        const data = {
            contract_id: contractId,
            approval_level: level,
            action: action,
            approved_amount: amount,
            comments: comments
        };

        fetch('pages/admin/api/electronic-contracts.php?action=approve_contract', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Phê duyệt thành công!');
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + (data.error || 'Không xác định'));
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra khi gửi yêu cầu');
            });
    }

    function showDisburseModal(contractId) {
        document.getElementById('disburse_contract_id').value = contractId;
        new bootstrap.Modal(document.getElementById('disburseModal')).show();
    }

    function performSearch(event) {
        event.preventDefault();
        
        const form = document.getElementById('searchForm');
        const formData = new FormData(form);
        
        // Tạo URL với các tham số tìm kiếm
        const params = new URLSearchParams();
        params.append('page', 'electronic-contracts');
        
        // Thêm các tham số từ form
        for (let [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        
        // Chuyển hướng đến URL mới
        window.location.href = '?' + params.toString();
        
        return false;
    }

    function downloadContract(contractId) {
        if (window.verifiedOTP && window.verifiedOTP.contractId == contractId) {
            const downloadUrl = `pages/admin/api/otp-verification.php?action=download_contract&contract_id=${contractId}&otp_id=${window.verifiedOTP.otpId}`;

            // Tạo link download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `HopDong_${contractId}.docx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Hiển thị thông báo thành công
            alert('Đang tải xuống hợp đồng...');
        } else {
            alert('Vui lòng xác thực OTP trước khi tải xuống hợp đồng!');
        }
    }

    function downloadContractFromModal() {
        const contractId = window.currentContractId;
        if (window.verifiedOTP && window.verifiedOTP.contractId == contractId) {
            const downloadUrl = `pages/admin/api/otp-verification.php?action=download_contract&contract_id=${contractId}&otp_id=${window.verifiedOTP.otpId}`;

            // Tạo link download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `HopDong_${contractId}.docx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Hiển thị thông báo thành công
            alert('Đang tải xuống hợp đồng...');
        } else {
            alert('Vui lòng xác thực OTP trước khi tải xuống hợp đồng!');
        }
    }

    // Tự động tính ngày kết thúc khi thay đổi ngày bắt đầu hoặc thời hạn
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const loanTermInput = document.getElementById('loan_term_months');

        function calculateEndDate() {
            if (startDateInput.value && loanTermInput.value) {
                const startDate = new Date(startDateInput.value);
                const months = parseInt(loanTermInput.value);
                const endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + months);
                endDateInput.value = endDate.toISOString().split('T')[0];
            }
        }

        if (startDateInput) startDateInput.addEventListener('change', calculateEndDate);
        if (loanTermInput) loanTermInput.addEventListener('change', calculateEndDate);

        // Tự động điền lãi suất khi chọn
        const interestRateSelect = document.getElementById('interest_rate_id');
        const monthlyRateInput = document.getElementById('monthly_rate');
        const dailyRateInput = document.getElementById('daily_rate');

        if (interestRateSelect) {
            interestRateSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.dataset.monthly) {
                    monthlyRateInput.value = selectedOption.dataset.monthly;
                    dailyRateInput.value = selectedOption.dataset.daily;
                }
            });
        }
    });
</script>