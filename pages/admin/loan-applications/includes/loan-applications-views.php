<?php

/**
 * Loan Applications View
 * Xử lý hiển thị giao diện
 */
class LoanApplicationsView
{
    public $applications = [];
    public $application = null;
    public $customers = [];
    public $assets = [];
    public $interestRates = [];
    public $users = [];
    public $departments = [];
    public $message = '';
    public $messageType = '';

    /**
     * Render giao diện chính
     */
    public function render()
    {
        $this->renderHeader();
        $this->renderMessage();
        $this->renderMainContent();
        $this->renderModals();
        $this->renderFooter();
    }

    /**
     * Render header với CSS và JS
     */
    private function renderHeader()
    {
?>
        <!-- CSS và JS cho loan applications -->
        <link rel="stylesheet" href="../../../assets/css/loan-applications.css">

        <script>
            // Debug: Kiểm tra xem JavaScript có được load không
            console.log('Loan Applications View loaded');

            // Đảm bảo jQuery và Bootstrap đã được load
            $(document).ready(function() {
                console.log('Document ready, jQuery version:', $.fn.jquery);
                console.log('Bootstrap modal available:', typeof bootstrap !== 'undefined');
                console.log('Bootstrap version:', bootstrap?.Modal?.VERSION || 'Unknown');

                // Test click button
                $('#addApplicationBtn').on('click', function() {
                    console.log('Add button clicked via inline script');
                    try {
                        $('#addApplicationModal').modal('show');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                });

                // Test click detail button
                $(document).on('click', '.detail-btn', function() {
                    console.log('Detail button clicked via inline script');
                    const id = $(this).data('id');
                    console.log('Application ID:', id);
                    try {
                        $('#applicationDetailModal').modal('show');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                });

                // Test click edit button
                $(document).on('click', '.edit-btn', function() {
                    console.log('Edit button clicked via inline script');
                    const id = $(this).data('id');
                    console.log('Application ID:', id);
                    try {
                        $('#editApplicationModal').modal('show');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                });

                // Test click approval button
                $(document).on('click', '.approval-btn', function() {
                    console.log('Approval button clicked via inline script');
                    const id = $(this).data('id');
                    console.log('Application ID:', id);
                    try {
                        $('#approvalModal').modal('show');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                });

                // Test click reject button
                $(document).on('click', '.reject-btn', function() {
                    console.log('Reject button clicked via inline script');
                    const id = $(this).data('id');
                    console.log('Application ID:', id);
                    try {
                        $('#rejectApplicationModal').modal('show');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                });

                // Test click delete button
                $(document).on('click', '.delete-btn', function() {
                    console.log('Delete button clicked via inline script');
                    const id = $(this).data('id');
                    console.log('Application ID:', id);
                    try {
                        $('#deleteConfirmModal').modal('show');
                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                });

                // Test xem có dữ liệu trong bảng không
                console.log('Table rows:', $('table tbody tr').length);
                console.log('Detail buttons:', $('.detail-btn').length);
                console.log('Edit buttons:', $('.edit-btn').length);
                console.log('Approval buttons:', $('.approval-btn').length);
                console.log('Reject buttons:', $('.reject-btn').length);
                console.log('Delete buttons:', $('.delete-btn').length);

                // Test xem có modal không
                console.log('Add modal exists:', $('#addApplicationModal').length);
                console.log('Detail modal exists:', $('#applicationDetailModal').length);
                console.log('Edit modal exists:', $('#editApplicationModal').length);
                console.log('Approval modal exists:', $('#approvalModal').length);
                console.log('Reject modal exists:', $('#rejectApplicationModal').length);
                console.log('Delete modal exists:', $('#deleteConfirmModal').length);
            });
        </script>

        <script src="assets/js/loan-applications-modular.js"></script>
        <script>
            // Debug: Kiểm tra xem file JavaScript có được load không
            console.log('Loan applications modular JS loaded');
        </script>

        <style>
            .modal-xl {
                max-width: 90%;
            }

            .modal-body {
                max-height: 70vh;
                overflow-y: auto;
            }

            .modal-body p {
                margin-bottom: 0.5rem;
            }

            .modal-body strong {
                color: #495057;
            }

            .modal-body span {
                color: #6c757d;
            }

            .text-primary {
                color: #007bff !important;
            }

            .badge {
                font-size: 0.875em;
            }

            .alert-heading {
                font-size: 1.1rem;
                font-weight: 600;
            }

            .form-control:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            .btn-primary {
                background-color: #007bff;
                border-color: #007bff;
            }

            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #0056b3;
            }

            .btn-danger {
                background-color: #dc3545;
                border-color: #dc3545;
            }

            .btn-danger:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }

            /* Validation styles */
            .form-control.is-invalid {
                border-color: #dc3545;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            }

            .invalid-feedback {
                display: block;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }

            .form-text {
                font-size: 0.875em;
                color: #6c757d;
            }
        </style>
        <?php
    }

    /**
     * Render thông báo
     */
    private function renderMessage()
    {
        if ($this->message): ?>
            <div class="alert alert-<?php echo $this->messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $this->message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif;
    }

    /**
     * Render nội dung chính
     */
    private function renderMainContent()
    {
        ?>
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">Quản lý đơn vay</h1>
                            <p class="mb-0">Quản lý thông tin đơn vay vay</p>
                        </div>
                        <button type="button" class="btn btn-primary" id="addApplicationBtn">
                            <i class="fas fa-plus me-2"></i>Tạo đơn vay mới
                        </button>
                    </div>
                </div>
            </div>

            <!-- Danh sách đơn vay -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn vay</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($this->applications)): ?>
                        <p class="text-muted text-center">Chưa có đơn vay nào</p>
                        <script>
                            console.log('No applications found in database');
                        </script>
                    <?php else: ?>
                        <script>
                            console.log('Found <?php echo count($this->applications); ?> applications');
                        </script>
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>Mã đơn vay</th>
                                        <th>Khách hàng</th>
                                        <th>Số tiền vay</th>
                                        <th>Lãi suất</th>
                                        <th>Thời hạn</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['application_code']); ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($app['customer_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($app['customer_phone_main']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo $this->formatCurrency($app['loan_amount']); ?></strong>
                                                    <?php if ($app['approved_amount']): ?>
                                                        <br>
                                                        <small class="text-success">Duyệt: <?php echo $this->formatCurrency($app['approved_amount']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo $app['monthly_rate']; ?>%/tháng</strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $app['daily_rate']; ?>%/ngày</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo $app['loan_term_months']; ?> tháng</strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($app['loan_purpose']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $this->getStatusBadgeClass($app['status']); ?>">
                                                    <?php echo $this->getStatusLabel($app['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($app['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info detail-btn"
                                                        data-id="<?php echo $app['id']; ?>" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                                        data-id="<?php echo $app['id']; ?>" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($app['status'] === 'pending'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success approval-btn"
                                                            data-id="<?php echo $app['id']; ?>" title="Duyệt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger reject-btn"
                                                            data-id="<?php echo $app['id']; ?>" title="Từ chối">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-dark delete-btn"
                                                        data-id="<?php echo $app['id']; ?>" title="Xóa">
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
        </div>
<?php
    }

    /**
     * Render modals
     */
    private function renderModals()
    {
        // Modal sẽ được render trong file riêng
        include __DIR__ . '/loan-applications-modals.php';
    }

    /**
     * Render footer
     */
    private function renderFooter()
    {
        // Footer có thể được thêm ở đây nếu cần
    }

    /**
     * Format currency
     */
    private function formatCurrency($amount)
    {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Nháp',
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
            'disbursed' => 'Đã giải ngân',
            'cancelled' => 'Đã hủy'
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get status badge class
     */
    private function getStatusBadgeClass($status)
    {
        $classes = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'disbursed' => 'info',
            'cancelled' => 'dark'
        ];

        return $classes[$status] ?? 'dark';
    }
}
?>