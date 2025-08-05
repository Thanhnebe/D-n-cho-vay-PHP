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
            case 'create_loan_role':
                $roleData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'approval_order' => intval($_POST['approval_order']),
                    'min_amount' => floatval($_POST['min_amount']),
                    'max_amount' => floatval($_POST['max_amount']),
                    'status' => $_POST['status']
                ];
                $db->insert('loan_approval_roles', $roleData);
                $message = 'Tạo vai trò phê duyệt khoản vay thành công';
                break;

            case 'update_loan_role':
                $role_id = intval($_POST['role_id']);
                $roleData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'approval_order' => intval($_POST['approval_order']),
                    'min_amount' => floatval($_POST['min_amount']),
                    'max_amount' => floatval($_POST['max_amount']),
                    'status' => $_POST['status']
                ];
                $db->update('loan_approval_roles', $roleData, 'id = ?', ['id' => $role_id]);
                $message = 'Cập nhật vai trò phê duyệt khoản vay thành công';
                break;

            case 'create_debt_role':
                $roleData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'approval_limit' => floatval($_POST['approval_limit']),
                    'status' => $_POST['status']
                ];
                $db->insert('debt_collection_roles', $roleData);
                $message = 'Tạo vai trò thu hồi nợ thành công';
                break;

            case 'update_debt_role':
                $role_id = intval($_POST['role_id']);
                $roleData = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'approval_limit' => floatval($_POST['approval_limit']),
                    'status' => $_POST['status']
                ];
                $db->update('debt_collection_roles', $roleData, 'id = ?', ['id' => $role_id]);
                $message = 'Cập nhật vai trò thu hồi nợ thành công';
                break;

            case 'assign_user_role':
                $user_id = intval($_POST['user_id']);
                $role_id = intval($_POST['role_id']);
                $role_type = $_POST['role_type'];

                if ($role_type === 'loan') {
                    $table = 'loan_approval_users';
                    $role_column = 'role_id';
                } else {
                    $table = 'debt_collection_users';
                    $role_column = 'role_id';
                }

                // Kiểm tra xem user đã có role này chưa
                $existing = $db->fetchOne("SELECT id FROM {$table} WHERE user_id = ? AND {$role_column} = ?", [$user_id, $role_id]);

                if (!$existing) {
                    $assignData = [
                        'user_id' => $user_id,
                        $role_column => $role_id,
                        'status' => 'active'
                    ];
                    $db->insert($table, $assignData);
                    $message = 'Phân quyền thành công';
                } else {
                    $error = 'Người dùng đã có vai trò này';
                }
                break;

            case 'update_waiver_limits':
                $year = intval($_POST['year']);
                $month = intval($_POST['month']);
                $level_1_limit = floatval($_POST['level_1_limit']);
                $level_2_limit = floatval($_POST['level_2_limit']);
                $level_3_limit = floatval($_POST['level_3_limit']);
                $limit_id = intval($_POST['limit_id'] ?? 0);

                $limitData = [
                    'year' => $year,
                    'month' => $month,
                    'level_1_limit' => $level_1_limit,
                    'level_2_limit' => $level_2_limit,
                    'level_3_limit' => $level_3_limit
                ];

                if ($limit_id) {
                    // Update existing limit
                    $db->update('waiver_approval_limits', $limitData, 'id = ?', ['id' => $limit_id]);
                    $message = 'Cập nhật giới hạn phê duyệt thành công';
                } else {
                    // Check if limit already exists for this month/year
                    $existing = $db->fetchOne("SELECT id FROM waiver_approval_limits WHERE year = ? AND month = ?", [$year, $month]);

                    if ($existing) {
                        $db->update('waiver_approval_limits', $limitData, 'id = ?', ['id' => $existing['id']]);
                        $message = 'Cập nhật giới hạn phê duyệt thành công';
                    } else {
                        $db->insert('waiver_approval_limits', $limitData);
                        $message = 'Tạo giới hạn phê duyệt thành công';
                    }
                }
                break;

            case 'delete_loan_role':
                $role_id = intval($_POST['role_id'] ?? 0);

                if (!$role_id) {
                    $error = 'ID vai trò không hợp lệ';
                    break;
                }

                // Kiểm tra xem có user nào đang sử dụng role này không
                $users_with_role = $db->fetchAll("SELECT COUNT(*) as count FROM loan_approval_users WHERE role_id = ?", [$role_id]);

                if ($users_with_role[0]['count'] > 0) {
                    $error = 'Không thể xóa vai trò đang được sử dụng';
                    break;
                }

                $affected = $db->delete('loan_approval_roles', 'id = ?', [$role_id]);

                if ($affected > 0) {
                    $message = 'Xóa vai trò phê duyệt khoản vay thành công';
                } else {
                    $error = 'Không tìm thấy vai trò để xóa';
                }
                break;

            case 'delete_debt_role':
                $role_id = intval($_POST['role_id'] ?? 0);

                if (!$role_id) {
                    $error = 'ID vai trò không hợp lệ';
                    break;
                }

                // Kiểm tra xem có user nào đang sử dụng role này không
                $users_with_role = $db->fetchAll("SELECT COUNT(*) as count FROM debt_collection_users WHERE role_id = ?", [$role_id]);

                if ($users_with_role[0]['count'] > 0) {
                    $error = 'Không thể xóa vai trò đang được sử dụng';
                    break;
                }

                $affected = $db->delete('debt_collection_roles', 'id = ?', [$role_id]);

                if ($affected > 0) {
                    $message = 'Xóa vai trò thu hồi nợ thành công';
                } else {
                    $error = 'Không tìm thấy vai trò để xóa';
                }
                break;

            case 'remove_user_role':
                $assignment_id = intval($_POST['assignment_id'] ?? 0);
                $type = $_POST['type'] ?? '';

                if (!$assignment_id || !$type) {
                    $error = 'Thiếu thông tin bắt buộc';
                    break;
                }

                if ($type === 'loan') {
                    $table = 'loan_approval_users';
                } else {
                    $table = 'debt_collection_users';
                }

                $affected = $db->delete($table, 'id = ?', [$assignment_id]);

                if ($affected > 0) {
                    $message = 'Xóa phân quyền thành công';
                } else {
                    $error = 'Không tìm thấy phân quyền để xóa';
                }
                break;

            case 'delete_waiver_limits':
                $limit_id = intval($_POST['limit_id'] ?? 0);

                if (!$limit_id) {
                    $error = 'ID giới hạn không hợp lệ';
                    break;
                }

                $affected = $db->delete('waiver_approval_limits', 'id = ?', [$limit_id]);

                if ($affected > 0) {
                    $message = 'Xóa giới hạn phê duyệt thành công';
                } else {
                    $error = 'Không tìm thấy giới hạn để xóa';
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Lấy dữ liệu cho các tab
$loan_roles = $db->fetchAll("SELECT * FROM loan_approval_roles ORDER BY approval_order");
$debt_roles = $db->fetchAll("SELECT * FROM debt_collection_roles ORDER BY approval_limit");
$users = $db->fetchAll("SELECT id, name, email, role FROM users WHERE status = 'active' ORDER BY name");
$departments = $db->fetchAll("SELECT * FROM departments WHERE status = 'active' ORDER BY name");

// Lấy thông tin phân quyền hiện tại
$loan_user_roles = $db->fetchAll("
    SELECT lur.*, u.name as user_name, u.email, lr.name as role_name 
    FROM loan_approval_users lur
    JOIN users u ON lur.user_id = u.id
    JOIN loan_approval_roles lr ON lur.role_id = lr.id
    WHERE lur.status = 'active'
    ORDER BY u.name, lr.approval_order
");

$debt_user_roles = $db->fetchAll("
    SELECT dcu.*, u.name as user_name, u.email, dcr.name as role_name 
    FROM debt_collection_users dcu
    JOIN users u ON dcu.user_id = u.id
    JOIN debt_collection_roles dcr ON dcu.role_id = dcr.id
    WHERE dcu.status = 'active'
    ORDER BY u.name, dcr.approval_limit
");

$waiver_limits = $db->fetchAll("SELECT * FROM waiver_approval_limits ORDER BY year DESC, month DESC");

// Helper functions
function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

function getStatusLabel($status)
{
    return $status === 'active' ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Không hoạt động</span>';
}

$page_title = 'Quản lý phân quyền';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-shield"></i> Quản lý phân quyền
        </h1>
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

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="permissionsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="loan-roles-tab" data-bs-toggle="tab" data-bs-target="#loan-roles" type="button" role="tab">
                <i class="fas fa-handshake"></i> Phê duyệt khoản vay
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="debt-roles-tab" data-bs-toggle="tab" data-bs-target="#debt-roles" type="button" role="tab">
                <i class="fas fa-hand-holding-usd"></i> Thu hồi nợ
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="user-assignments-tab" data-bs-toggle="tab" data-bs-target="#user-assignments" type="button" role="tab">
                <i class="fas fa-users"></i> Phân quyền người dùng
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="waiver-limits-tab" data-bs-toggle="tab" data-bs-target="#waiver-limits" type="button" role="tab">
                <i class="fas fa-chart-line"></i> Giới hạn miễn giảm
            </button>
        </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content" id="permissionsTabContent">
        <!-- Tab 1: Loan Approval Roles -->
        <div class="tab-pane fade show active" id="loan-roles" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-handshake"></i> Vai trò phê duyệt khoản vay
                    </h5>
                    <button class="btn btn-primary btn-sm" onclick="showCreateLoanRoleModal()">
                        <i class="fas fa-plus"></i> Thêm vai trò
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên vai trò</th>
                                    <th>Mô tả</th>
                                    <th>Thứ tự</th>
                                    <th>Giới hạn (VNĐ)</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loan_roles as $index => $role): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($role['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($role['description']) ?></td>
                                        <td><span class="badge bg-info"><?= $role['approval_order'] ?></span></td>
                                        <td>
                                            <small class="text-muted">Từ:</small> <?= formatCurrency($role['min_amount']) ?><br>
                                            <small class="text-muted">Đến:</small> <?= formatCurrency($role['max_amount']) ?>
                                        </td>
                                        <td><?= getStatusLabel($role['status']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editLoanRole(<?= $role['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteLoanRole(<?= $role['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Debt Collection Roles -->
        <div class="tab-pane fade" id="debt-roles" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-holding-usd"></i> Vai trò thu hồi nợ
                    </h5>
                    <button class="btn btn-primary btn-sm" onclick="showCreateDebtRoleModal()">
                        <i class="fas fa-plus"></i> Thêm vai trò
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên vai trò</th>
                                    <th>Mô tả</th>
                                    <th>Giới hạn phê duyệt</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($debt_roles as $index => $role): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($role['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($role['description']) ?></td>
                                        <td><span class="badge bg-warning"><?= formatCurrency($role['approval_limit']) ?></span></td>
                                        <td><?= getStatusLabel($role['status']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editDebtRole(<?= $role['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteDebtRole(<?= $role['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: User Assignments -->
        <div class="tab-pane fade" id="user-assignments" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Phân quyền người dùng
                    </h5>
                    <button class="btn btn-primary btn-sm" onclick="showAssignUserModal()">
                        <i class="fas fa-plus"></i> Phân quyền
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-handshake"></i> Phê duyệt khoản vay</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Người dùng</th>
                                            <th>Vai trò</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($loan_user_roles as $assignment): ?>
                                            <tr>
                                                <td>
                                                    <div><strong><?= htmlspecialchars($assignment['user_name']) ?></strong></div>
                                                    <small class="text-muted"><?= htmlspecialchars($assignment['email']) ?></small>
                                                </td>
                                                <td><span class="badge bg-primary"><?= htmlspecialchars($assignment['role_name']) ?></span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="removeUserRole(<?= $assignment['id'] ?>, 'loan')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-hand-holding-usd"></i> Thu hồi nợ</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Người dùng</th>
                                            <th>Vai trò</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($debt_user_roles as $assignment): ?>
                                            <tr>
                                                <td>
                                                    <div><strong><?= htmlspecialchars($assignment['user_name']) ?></strong></div>
                                                    <small class="text-muted"><?= htmlspecialchars($assignment['email']) ?></small>
                                                </td>
                                                <td><span class="badge bg-warning"><?= htmlspecialchars($assignment['role_name']) ?></span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="removeUserRole(<?= $assignment['id'] ?>, 'debt')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Waiver Limits -->
        <div class="tab-pane fade" id="waiver-limits" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Giới hạn phê duyệt miễn giảm
                    </h5>
                    <button class="btn btn-primary btn-sm" onclick="showWaiverLimitsModal()">
                        <i class="fas fa-plus"></i> Thêm giới hạn
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tháng/Năm</th>
                                    <th>Cấp 1 (VNĐ)</th>
                                    <th>Cấp 2 (VNĐ)</th>
                                    <th>Cấp 3 (VNĐ)</th>
                                    <th>Đã sử dụng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($waiver_limits as $limit): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $limit['month'] ?>/<?= $limit['year'] ?></strong>
                                        </td>
                                        <td>
                                            <div><?= formatCurrency($limit['level_1_limit']) ?></div>
                                            <small class="text-muted">Đã dùng: <?= formatCurrency($limit['level_1_used']) ?></small>
                                        </td>
                                        <td>
                                            <div><?= formatCurrency($limit['level_2_limit']) ?></div>
                                            <small class="text-muted">Đã dùng: <?= formatCurrency($limit['level_2_used']) ?></small>
                                        </td>
                                        <td>
                                            <div><?= formatCurrency($limit['level_3_limit']) ?></div>
                                            <small class="text-muted">Đã dùng: <?= formatCurrency($limit['level_3_used']) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $total_limit = $limit['level_1_limit'] + $limit['level_2_limit'] + $limit['level_3_limit'];
                                            $total_used = $limit['level_1_used'] + $limit['level_2_used'] + $limit['level_3_used'];
                                            $percentage = $total_limit > 0 ? ($total_used / $total_limit) * 100 : 0;
                                            ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?= $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success') ?>"
                                                    style="width: <?= $percentage ?>%">
                                                    <?= number_format($percentage, 1) ?>%
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= formatCurrency($total_used) ?> / <?= formatCurrency($total_limit) ?></small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editWaiverLimits(<?= $limit['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteWaiverLimits(<?= $limit['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal tạo vai trò phê duyệt khoản vay -->
<div class="modal fade" id="createLoanRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm vai trò phê duyệt khoản vay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_loan_role">
                    <input type="hidden" name="role_id" value="">
                    <div class="mb-3">
                        <label class="form-label">Tên vai trò *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Thứ tự phê duyệt *</label>
                                <input type="number" class="form-control" name="approval_order" min="1" required>
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
                                <label class="form-label">Giới hạn tối thiểu (VNĐ) *</label>
                                <input type="number" class="form-control" name="min_amount" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Giới hạn tối đa (VNĐ) *</label>
                                <input type="number" class="form-control" name="max_amount" min="0" step="1000" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo vai trò</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal tạo vai trò thu hồi nợ -->
<div class="modal fade" id="createDebtRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm vai trò thu hồi nợ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_debt_role">
                    <input type="hidden" name="role_id" value="">
                    <div class="mb-3">
                        <label class="form-label">Tên vai trò *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Giới hạn phê duyệt (VNĐ) *</label>
                                <input type="number" class="form-control" name="approval_limit" min="0" step="1000" required>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo vai trò</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal phân quyền người dùng -->
<div class="modal fade" id="assignUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Phân quyền người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="assign_user_role">
                    <div class="mb-3">
                        <label class="form-label">Loại vai trò *</label>
                        <select class="form-select" name="role_type" required onchange="loadRoles()">
                            <option value="">Chọn loại vai trò</option>
                            <option value="loan">Phê duyệt khoản vay</option>
                            <option value="debt">Thu hồi nợ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Người dùng *</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">Chọn người dùng</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vai trò *</label>
                        <select class="form-select" name="role_id" required id="roleSelect">
                            <option value="">Chọn vai trò</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Phân quyền</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal giới hạn miễn giảm -->
<div class="modal fade" id="waiverLimitsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm giới hạn phê duyệt miễn giảm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_waiver_limits">
                    <input type="hidden" name="limit_id" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tháng *</label>
                                <select class="form-select" name="month" required>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Năm *</label>
                                <select class="form-select" name="year" required>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 2; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Giới hạn cấp 1 (VNĐ) *</label>
                                <input type="number" class="form-control" name="level_1_limit" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Giới hạn cấp 2 (VNĐ) *</label>
                                <input type="number" class="form-control" name="level_2_limit" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Giới hạn cấp 3 (VNĐ) *</label>
                                <input type="number" class="form-control" name="level_3_limit" min="0" step="1000" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu giới hạn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Loan roles data
    const loanRoles = <?= json_encode($loan_roles) ?>;
    const debtRoles = <?= json_encode($debt_roles) ?>;

    function showCreateLoanRoleModal() {
        new bootstrap.Modal(document.getElementById('createLoanRoleModal')).show();
    }

    function showCreateDebtRoleModal() {
        new bootstrap.Modal(document.getElementById('createDebtRoleModal')).show();
    }

    function showAssignUserModal() {
        new bootstrap.Modal(document.getElementById('assignUserModal')).show();
    }

    function showWaiverLimitsModal() {
        new bootstrap.Modal(document.getElementById('waiverLimitsModal')).show();
    }

    function loadRoles() {
        const roleType = document.querySelector('select[name="role_type"]').value;
        const roleSelect = document.getElementById('roleSelect');
        roleSelect.innerHTML = '<option value="">Chọn vai trò</option>';

        if (roleType === 'loan') {
            loanRoles.forEach(role => {
                if (role.status === 'active') {
                    roleSelect.innerHTML += `<option value="${role.id}">${role.name}</option>`;
                }
            });
        } else if (roleType === 'debt') {
            debtRoles.forEach(role => {
                if (role.status === 'active') {
                    roleSelect.innerHTML += `<option value="${role.id}">${role.name}</option>`;
                }
            });
        }
    }

    function editLoanRole(roleId) {
        const role = loanRoles.find(r => r.id == roleId);
        if (role) {
            // Populate modal with role data
            const modal = document.getElementById('createLoanRoleModal');
            modal.querySelector('input[name="action"]').value = 'update_loan_role';
            modal.querySelector('input[name="role_id"]').value = roleId;
            modal.querySelector('input[name="name"]').value = role.name;
            modal.querySelector('textarea[name="description"]').value = role.description;
            modal.querySelector('input[name="approval_order"]').value = role.approval_order;
            modal.querySelector('input[name="min_amount"]').value = role.min_amount;
            modal.querySelector('input[name="max_amount"]').value = role.max_amount;
            modal.querySelector('select[name="status"]').value = role.status;

            modal.querySelector('.modal-title').textContent = 'Cập nhật vai trò phê duyệt khoản vay';
            modal.querySelector('button[type="submit"]').textContent = 'Cập nhật';

            new bootstrap.Modal(modal).show();
        }
    }

    function editDebtRole(roleId) {
        const role = debtRoles.find(r => r.id == roleId);
        if (role) {
            // Populate modal with role data
            const modal = document.getElementById('createDebtRoleModal');
            modal.querySelector('input[name="action"]').value = 'update_debt_role';
            modal.querySelector('input[name="role_id"]').value = roleId;
            modal.querySelector('input[name="name"]').value = role.name;
            modal.querySelector('textarea[name="description"]').value = role.description;
            modal.querySelector('input[name="approval_limit"]').value = role.approval_limit;
            modal.querySelector('select[name="status"]').value = role.status;

            modal.querySelector('.modal-title').textContent = 'Cập nhật vai trò thu hồi nợ';
            modal.querySelector('button[type="submit"]').textContent = 'Cập nhật';

            new bootstrap.Modal(modal).show();
        }
    }

    function deleteLoanRole(roleId) {
        if (confirm('Bạn có chắc muốn xóa vai trò này?')) {
            // Submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_loan_role">
            <input type="hidden" name="role_id" value="${roleId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteDebtRole(roleId) {
        if (confirm('Bạn có chắc muốn xóa vai trò này?')) {
            // Submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_debt_role">
            <input type="hidden" name="role_id" value="${roleId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function removeUserRole(assignmentId, type) {
        if (confirm('Bạn có chắc muốn xóa phân quyền này?')) {
            // Submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="remove_user_role">
            <input type="hidden" name="assignment_id" value="${assignmentId}">
            <input type="hidden" name="type" value="${type}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function editWaiverLimits(limitId) {
        // Load limit data and populate modal
        fetch(`api/permissions.php?action=get_waiver_limit&id=${limitId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const limit = data.data;
                    const modal = document.getElementById('waiverLimitsModal');
                    modal.querySelector('input[name="action"]').value = 'update_waiver_limits';
                    modal.querySelector('input[name="limit_id"]').value = limitId;
                    modal.querySelector('select[name="month"]').value = limit.month;
                    modal.querySelector('select[name="year"]').value = limit.year;
                    modal.querySelector('input[name="level_1_limit"]').value = limit.level_1_limit;
                    modal.querySelector('input[name="level_2_limit"]').value = limit.level_2_limit;
                    modal.querySelector('input[name="level_3_limit"]').value = limit.level_3_limit;

                    modal.querySelector('.modal-title').textContent = 'Cập nhật giới hạn phê duyệt miễn giảm';
                    modal.querySelector('button[type="submit"]').textContent = 'Cập nhật';

                    new bootstrap.Modal(modal).show();
                }
            });
    }

    function deleteWaiverLimits(limitId) {
        if (confirm('Bạn có chắc muốn xóa giới hạn này?')) {
            // Submit delete form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete_waiver_limits">
            <input type="hidden" name="limit_id" value="${limitId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Reset modal forms when closed
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            this.querySelector('form').reset();
            this.querySelector('.modal-title').textContent = this.querySelector('.modal-title').textContent.replace('Cập nhật', 'Thêm').replace('Cập nhật', 'Thêm');
            this.querySelector('button[type="submit"]').textContent = this.querySelector('button[type="submit"]').textContent.replace('Cập nhật', 'Tạo').replace('Cập nhật', 'Lưu');
        });
    });
</script>