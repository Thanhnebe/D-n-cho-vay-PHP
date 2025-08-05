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
        $customerData = [
            'name' => sanitize_input($_POST['name']),
            'phone' => sanitize_input($_POST['phone']),
            'email' => sanitize_input($_POST['email']),
            'address' => sanitize_input($_POST['address']),
            'id_number' => sanitize_input($_POST['id_number']),
            'id_type' => $_POST['id_type'],
            'date_of_birth' => $_POST['date_of_birth'],
            'gender' => $_POST['gender'],
            'occupation' => sanitize_input($_POST['occupation']),
            'monthly_income' => $_POST['monthly_income'] ? str_replace(',', '', $_POST['monthly_income']) : null,
            'status' => 'active',
            'notes' => sanitize_input($_POST['notes']),
            'tax_code' => sanitize_input($_POST['tax_code']),
            'verified' => isset($_POST['verified']) ? 1 : 0,
            'cif' => sanitize_input($_POST['cif']),
            'loan_date' => date('Y-m-d')
        ];

        if ($action === 'add') {
            $customerData['created_by'] = $_SESSION['user_id'] ?? 1;
            $customerId = $db->insert('customers', $customerData);
            if ($customerId) {
                $message = 'Thêm khách hàng thành công!';
                $messageType = 'success';
            } else {
                $message = 'Có lỗi xảy ra khi thêm khách hàng!';
                $messageType = 'error';
            }
        } else {
            $customerId = $_POST['customer_id'];
            $result = $db->update('customers', $customerData, 'id = ?', ['id' => $customerId]);
            if ($result) {
                $message = 'Cập nhật khách hàng thành công!';
                $messageType = 'success';
            } else {
                $message = 'Có lỗi xảy ra khi cập nhật khách hàng!';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $customerId = $_POST['customer_id'];
        $result = $db->delete('customers', 'id = ?', [$customerId]);
        if ($result) {
            $message = 'Xóa khách hàng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Có lỗi xảy ra khi xóa khách hàng!';
            $messageType = 'error';
        }
    }
}

// Lấy danh sách khách hàng
$customers = $db->fetchAll("SELECT * FROM customers ORDER BY created_at DESC");

// Lấy thông tin khách hàng để edit
$customer = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$_GET['id']]);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mt-5">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Quản lý khách hàng</h1>
                    <p class="mb-0">Quản lý thông tin khách hàng</p>
                </div>
                <a href="?page=customers&action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm khách hàng
                </a>
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
        <!-- Danh sách khách hàng -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách khách hàng</h6>
            </div>
            <div class="card-body">
                <?php if (empty($customers)): ?>
                    <p class="text-muted text-center">Chưa có khách hàng nào</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Họ tên</th>
                                    <th>Số điện thoại</th>
                                    <th>Email</th>
                                    <th>CMND/CCCD</th>
                                    <th>Ngày sinh</th>
                                    <th>Thu nhập</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['id_number']); ?></td>
                                        <td><?php echo $customer['date_of_birth'] ? format_date($customer['date_of_birth']) : '-'; ?></td>
                                        <td><?php echo $customer['monthly_income'] ? format_currency($customer['monthly_income']) : '-'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php
                                                                        echo $customer['status'] === 'active' ? 'success' : ($customer['status'] === 'inactive' ? 'warning' : 'danger');
                                                                        ?>">
                                                <?php
                                                echo $customer['status'] === 'active' ? 'Hoạt động' : ($customer['status'] === 'inactive' ? 'Không hoạt động' : 'Đen');
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?page=customers&action=edit&id=<?php echo $customer['id']; ?>"
                                                class="btn btn-sm btn-outline-primary" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=customers&action=view&id=<?php echo $customer['id']; ?>"
                                                class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteCustomer(<?php echo $customer['id']; ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Form thêm/sửa khách hàng -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?php echo $action === 'add' ? 'Thêm khách hàng mới' : 'Sửa thông tin khách hàng'; ?>
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="?page=customers&action=<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo $customer['name'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo $customer['phone'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo $customer['email'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_number" class="form-label">CMND/CCCD</label>
                                <input type="text" class="form-control" id="id_number" name="id_number"
                                    value="<?php echo $customer['id_number'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_type" class="form-label">Loại giấy tờ</label>
                                <select class="form-control" id="id_type" name="id_type">
                                    <option value="cccd" <?php echo ($customer['id_type'] ?? 'cccd') === 'cccd' ? 'selected' : ''; ?>>CCCD</option>
                                    <option value="cmnd" <?php echo ($customer['id_type'] ?? '') === 'cmnd' ? 'selected' : ''; ?>>CMND</option>
                                    <option value="passport" <?php echo ($customer['id_type'] ?? '') === 'passport' ? 'selected' : ''; ?>>Passport</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                    value="<?php echo $customer['date_of_birth'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gender" class="form-label">Giới tính</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Chọn giới tính</option>
                                    <option value="male" <?php echo ($customer['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="female" <?php echo ($customer['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="other" <?php echo ($customer['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="occupation" class="form-label">Nghề nghiệp</label>
                                <input type="text" class="form-control" id="occupation" name="occupation"
                                    value="<?php echo $customer['occupation'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monthly_income" class="form-label">Thu nhập hàng tháng</label>
                                <input type="text" class="form-control currency-input" id="monthly_income" name="monthly_income"
                                    value="<?php echo $customer['monthly_income'] ? number_format($customer['monthly_income'], 0, ',', '.') : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cif" class="form-label">Mã CIF</label>
                                <input type="text" class="form-control" id="cif" name="cif"
                                    value="<?php echo $customer['cif'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tax_code" class="form-label">Mã số thuế</label>
                                <input type="text" class="form-control" id="tax_code" name="tax_code"
                                    value="<?php echo $customer['tax_code'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="verified" name="verified"
                                        <?php echo ($customer['verified'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="verified">
                                        Đã xác minh
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo $customer['address'] ?? ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo $customer['notes'] ?? ''; ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="?page=customers" class="btn btn-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $action === 'add' ? 'Thêm khách hàng' : 'Cập nhật'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($action === 'view' && isset($_GET['id'])): ?>
        <!-- Chi tiết khách hàng -->
        <?php
        $customerId = $_GET['id'];
        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
        $contracts = $db->fetchAll("SELECT * FROM contracts WHERE customer_id = ? ORDER BY created_at DESC", [$customerId]);
        ?>

        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Chi tiết khách hàng</h6>
            </div>
            <div class="card-body">
                <?php if ($customer): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin cá nhân</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Họ tên:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Số điện thoại:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>CMND/CCCD:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['id_number']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Loại giấy tờ:</strong></td>
                                    <td><?php echo strtoupper($customer['id_type']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày sinh:</strong></td>
                                    <td><?php echo $customer['date_of_birth'] ? format_date($customer['date_of_birth']) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Giới tính:</strong></td>
                                    <td>
                                        <?php
                                        echo $customer['gender'] === 'male' ? 'Nam' : ($customer['gender'] === 'female' ? 'Nữ' : ($customer['gender'] === 'other' ? 'Khác' : '-'));
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nghề nghiệp:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['occupation'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Thu nhập:</strong></td>
                                    <td><?php echo $customer['monthly_income'] ? format_currency($customer['monthly_income']) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Mã CIF:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['cif'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Mã số thuế:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['tax_code'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Địa chỉ:</strong></td>
                                    <td><?php echo htmlspecialchars($customer['address'] ?? '-'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        <span class="badge badge-<?php
                                                                    echo $customer['status'] === 'active' ? 'success' : ($customer['status'] === 'inactive' ? 'warning' : 'danger');
                                                                    ?>">
                                            <?php
                                            echo $customer['status'] === 'active' ? 'Hoạt động' : ($customer['status'] === 'inactive' ? 'Không hoạt động' : 'Đen');
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Lịch sử hợp đồng</h5>
                            <?php if (empty($contracts)): ?>
                                <p class="text-muted">Chưa có hợp đồng nào</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($contracts as $contract): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($contract['contract_code']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo format_date($contract['created_at']); ?>
                                                    </small>
                                                </div>
                                                <span class="badge badge-<?php echo $contract['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo format_currency($contract['amount']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Không tìm thấy khách hàng</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function deleteCustomer(customerId) {
        if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=customers&action=delete';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'customer_id';
            input.value = customerId;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>