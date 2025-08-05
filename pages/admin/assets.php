<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$messageType = '';

// Xử lý các action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $assetData = [
            'customer_id' => $_POST['customer_id'] ?? 1, // Default to customer ID 1 for now
            'name' => sanitize_input($_POST['name']),
            'category_id' => $_POST['category_id'],
            'description' => sanitize_input($_POST['description']),
            'estimated_value' => $_POST['estimated_value'] ? str_replace(',', '', $_POST['estimated_value']) : null,
            'condition_status' => $_POST['condition'],
            'status' => 'available',
            'notes' => sanitize_input($_POST['notes'])
        ];

        if ($action === 'add') {
            $assetData['created_by'] = $_SESSION['user_id'] ?? 1;
            $assetId = $db->insert('assets', $assetData);
            if ($assetId) {
                $message = 'Thêm tài sản thành công!';
                $messageType = 'success';
            } else {
                $message = 'Có lỗi xảy ra khi thêm tài sản!';
                $messageType = 'error';
            }
        } else {
            $assetId = $_POST['asset_id'];
            $result = $db->update('assets', $assetData, 'id = ?', ['id' => $assetId]);
            if ($result) {
                $message = 'Cập nhật tài sản thành công!';
                $messageType = 'success';
            } else {
                $message = 'Có lỗi xảy ra khi cập nhật tài sản!';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $assetId = $_POST['asset_id'];
        $result = $db->delete('assets', 'id = ?', [$assetId]);
        if ($result) {
            $message = 'Xóa tài sản thành công!';
            $messageType = 'success';
        } else {
            $message = 'Có lỗi xảy ra khi xóa tài sản!';
            $messageType = 'error';
        }
    }
}

// Lấy danh sách tài sản
$assets = $db->fetchAll("
    SELECT a.*, ac.name as category_name, cu.name as customer_name 
    FROM assets a 
    LEFT JOIN asset_categories ac ON a.category_id = ac.id 
    LEFT JOIN customers cu ON a.customer_id = cu.id 
    ORDER BY a.created_at DESC
");

// Lấy danh sách danh mục tài sản
$categories = $db->fetchAll("SELECT * FROM asset_categories ORDER BY name");

// Lấy danh sách khách hàng
$customers = $db->fetchAll("SELECT id, name FROM customers ORDER BY name");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mt-5">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Quản lý tài sản cầm cố</h1>
                    <p class="mb-0">Quản lý thông tin tài sản cầm cố</p>
                </div>
                <button type="button" class="btn btn-primary" onclick="showAddAssetModal()">
                    <i class="fas fa-plus me-2"></i>Thêm tài sản
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

    <!-- Danh sách tài sản -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách tài sản</h6>
        </div>
        <div class="card-body">
            <?php if (empty($assets)): ?>
                <p class="text-muted text-center">Chưa có tài sản nào</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable" id="assetsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên tài sản</th>
                                <th>Danh mục</th>
                                <th>Giá trị ước tính</th>
                                <th>Tình trạng</th>
                                <th>Trạng thái</th>
                                <th>Khách hàng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td><?php echo $asset['id']; ?></td>
                                    <td><?php echo htmlspecialchars($asset['name']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['category_name'] ?? '-'); ?></td>
                                    <td><?php echo $asset['estimated_value'] ? format_currency($asset['estimated_value']) : '-'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php
                                                                    $condition = $asset['condition_status'] ?? '';
                                                                    echo $condition === 'excellent' ? 'success' : ($condition === 'good' ? 'info' : ($condition === 'fair' ? 'warning' : 'danger'));
                                                                    ?>">
                                            <?php
                                            $condition = $asset['condition_status'] ?? '';
                                            echo $condition === 'excellent' ? 'Tuyệt vời' : ($condition === 'good' ? 'Tốt' : ($condition === 'fair' ? 'Trung bình' : 'Kém'));
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php
                                                                    echo $asset['status'] === 'available' ? 'success' : ($asset['status'] === 'pledged' ? 'warning' : 'danger');
                                                                    ?>">
                                            <?php
                                            echo $asset['status'] === 'available' ? 'Có sẵn' : ($asset['status'] === 'pledged' ? 'Đã cầm cố' : 'Không khả dụng');
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($asset['customer_name'] ?? '-'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="showEditAssetModal(<?php echo $asset['id']; ?>)" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                            onclick="showViewAssetModal(<?php echo $asset['id']; ?>)" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteAsset(<?php echo $asset['id']; ?>)" title="Xóa">
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
</div>

<!-- Modal Thêm tài sản -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAssetModalLabel">Thêm tài sản mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAssetForm" method="POST" action="?page=assets&action=add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên tài sản <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Khách hàng</label>
                                <select class="form-control" id="customer_id" name="customer_id">
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Danh mục</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estimated_value" class="form-label">Giá trị ước tính</label>
                                <input type="text" class="form-control currency-input" id="estimated_value" name="estimated_value">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="condition" class="form-label">Tình trạng</label>
                                <select class="form-control" id="condition" name="condition">
                                    <option value="excellent">Tuyệt vời</option>
                                    <option value="good">Tốt</option>
                                    <option value="fair">Trung bình</option>
                                    <option value="poor">Kém</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="available">Có sẵn</option>
                                    <option value="pledged">Đã cầm cố</option>
                                    <option value="unavailable">Không khả dụng</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Thêm tài sản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa tài sản -->
<div class="modal fade" id="editAssetModal" tabindex="-1" aria-labelledby="editAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssetModalLabel">Sửa thông tin tài sản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAssetForm" method="POST" action="?page=assets&action=edit">
                <input type="hidden" id="edit_asset_id" name="asset_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Tên tài sản <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_customer_id" class="form-label">Khách hàng</label>
                                <select class="form-control" id="edit_customer_id" name="customer_id">
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_category_id" class="form-label">Danh mục</label>
                                <select class="form-control" id="edit_category_id" name="category_id">
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_estimated_value" class="form-label">Giá trị ước tính</label>
                                <input type="text" class="form-control currency-input" id="edit_estimated_value" name="estimated_value">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_condition" class="form-label">Tình trạng</label>
                                <select class="form-control" id="edit_condition" name="condition">
                                    <option value="excellent">Tuyệt vời</option>
                                    <option value="good">Tốt</option>
                                    <option value="fair">Trung bình</option>
                                    <option value="poor">Kém</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Trạng thái</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="available">Có sẵn</option>
                                    <option value="pledged">Đã cầm cố</option>
                                    <option value="unavailable">Không khả dụng</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xem chi tiết tài sản -->
<div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAssetModalLabel">Chi tiết tài sản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewAssetModalBody">
                <!-- Nội dung sẽ được load bằng AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Định nghĩa các hàm modal toàn cục
    window.showAddAssetModal = function() {
        try {
            console.log('showAddAssetModal called');
            const form = document.getElementById('addAssetForm');
            if (form) {
                form.reset();
            }
            
            const modal = document.getElementById('addAssetModal');
            if (modal) {
                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();
            } else {
                console.error('Modal not found');
                alert('Không tìm thấy modal');
            }
        } catch (error) {
            console.error('Error in showAddAssetModal:', error);
            alert('Có lỗi xảy ra khi mở modal');
        }
    };

    window.showEditAssetModal = function(assetId) {
        try {
            console.log('showEditAssetModal called with ID:', assetId);
            
            // Show modal first
            const modal = document.getElementById('editAssetModal');
            if (!modal) {
                console.error('Edit modal not found');
                alert('Không tìm thấy modal chỉnh sửa');
                return;
            }

            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();

            // Fetch asset data via AJAX
            fetch('pages/admin/api/get-asset.php?id=' + assetId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const asset = data.asset;
                        document.getElementById('edit_asset_id').value = asset.id;
                        document.getElementById('edit_name').value = asset.name;
                        document.getElementById('edit_customer_id').value = asset.customer_id || '';
                        document.getElementById('edit_category_id').value = asset.category_id || '';
                        document.getElementById('edit_estimated_value').value = asset.estimated_value ?
                            parseInt(asset.estimated_value).toLocaleString('vi-VN') : '';
                        document.getElementById('edit_condition').value = asset.condition_status || '';
                        document.getElementById('edit_status').value = asset.status || '';
                        document.getElementById('edit_description').value = asset.description || '';
                        document.getElementById('edit_notes').value = asset.notes || '';
                    } else {
                        alert('Có lỗi xảy ra khi tải thông tin tài sản!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải thông tin tài sản!');
                });
        } catch (error) {
            console.error('Error in showEditAssetModal:', error);
            alert('Có lỗi xảy ra khi mở modal');
        }
    };

    window.showViewAssetModal = function(assetId) {
        try {
            console.log('showViewAssetModal called with ID:', assetId);
            
            // Show modal first
            const modal = document.getElementById('viewAssetModal');
            if (!modal) {
                console.error('View modal not found');
                alert('Không tìm thấy modal xem chi tiết');
                return;
            }

            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();

            // Fetch asset details via AJAX
            fetch('pages/admin/api/get-asset.php?id=' + assetId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const asset = data.asset;
                        const modalBody = document.getElementById('viewAssetModalBody');

                        modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Thông tin tài sản</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tên tài sản:</strong></td>
                                        <td>${asset.name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Danh mục:</strong></td>
                                        <td>${asset.category_name || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Giá trị ước tính:</strong></td>
                                        <td>${asset.estimated_value ? formatCurrency(asset.estimated_value) : '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tình trạng:</strong></td>
                                        <td>
                                            <span class="badge badge-${getConditionBadgeClass(asset.condition_status)}">
                                                ${getConditionText(asset.condition_status)}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Trạng thái:</strong></td>
                                        <td>
                                            <span class="badge badge-${getStatusBadgeClass(asset.status)}">
                                                ${getStatusText(asset.status)}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Mô tả:</strong></td>
                                        <td>${asset.description || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ghi chú:</strong></td>
                                        <td>${asset.notes || '-'}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Thông tin hợp đồng</h6>
                                ${asset.customer_name ? `
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Khách hàng:</strong></td>
                                            <td>${asset.customer_name}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mã hợp đồng:</strong></td>
                                            <td>${asset.contract_code || '-'}</td>
                                        </tr>
                                    </table>
                                ` : '<p class="text-muted">Chưa có hợp đồng nào sử dụng tài sản này</p>'}
                            </div>
                        </div>
                    `;
                } else {
                    alert('Có lỗi xảy ra khi tải thông tin tài sản!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi tải thông tin tài sản!');
            });
        } catch (error) {
            console.error('Error in showViewAssetModal:', error);
            alert('Có lỗi xảy ra khi mở modal');
        }
    };

    window.deleteAsset = function(assetId) {
        try {
            console.log('deleteAsset called with ID:', assetId);
            if (confirm('Bạn có chắc chắn muốn xóa tài sản này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?page=assets&action=delete';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'asset_id';
                input.value = assetId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        } catch (error) {
            console.error('Error in deleteAsset:', error);
            alert('Có lỗi xảy ra khi xóa tài sản');
        }
    };

    // Helper functions
    function formatCurrency(amount) {
        return parseInt(amount).toLocaleString('vi-VN') + ' VNĐ';
    }

    function getConditionBadgeClass(condition) {
        switch (condition) {
            case 'excellent':
                return 'success';
            case 'good':
                return 'info';
            case 'fair':
                return 'warning';
            case 'poor':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    function getConditionText(condition) {
        switch (condition) {
            case 'excellent':
                return 'Tuyệt vời';
            case 'good':
                return 'Tốt';
            case 'fair':
                return 'Trung bình';
            case 'poor':
                return 'Kém';
            default:
                return 'Không xác định';
        }
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'available':
                return 'success';
            case 'pledged':
                return 'warning';
            default:
                return 'danger';
        }
    }

    function getStatusText(status) {
        switch (status) {
            case 'available':
                return 'Có sẵn';
            case 'pledged':
                return 'Đã cầm cố';
            default:
                return 'Không khả dụng';
        }
    }

    // Format currency input
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing currency formatters');
        
        const currencyInputs = document.querySelectorAll('.currency-input');
        currencyInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value) {
                    value = parseInt(value).toLocaleString('vi-VN');
                    e.target.value = value;
                }
            });
        });

        // Initialize DataTables if available
        if (typeof $.fn.DataTable !== 'undefined') {
            $('#assetsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
                },
                "pageLength": 25,
                "order": [[0, "desc"]]
            });
        }
    });
</script>