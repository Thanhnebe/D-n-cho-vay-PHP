/**
 * Loan Applications JavaScript - Modular Version
 * Xử lý tất cả tương tác JavaScript cho loan applications
 */

class LoanApplicationsJS {
    constructor() {
        console.log('LoanApplicationsJS constructor called');
        this.init();
    }

    init() {
        console.log('Initializing LoanApplicationsJS...');

        // Test xem các element có tồn tại không
        console.log('Add button exists:', $('#addApplicationBtn').length);
        console.log('Detail buttons exist:', $('.detail-btn').length);
        console.log('Edit buttons exist:', $('.edit-btn').length);
        console.log('Approval buttons exist:', $('.approval-btn').length);
        console.log('Reject buttons exist:', $('.reject-btn').length);
        console.log('Delete buttons exist:', $('.delete-btn').length);

        this.bindEvents();
        this.initCurrencyFormatters();
        this.initDataTables();
        console.log('LoanApplicationsJS initialized');
    }

    bindEvents() {
        console.log('Binding events...');

        // Add application button
        $('#addApplicationBtn').on('click', () => {
            console.log('Add button clicked');
            this.showAddModal();
        });

        // Detail buttons
        $(document).on('click', '.detail-btn', (e) => {
            console.log('Detail button clicked', $(e.currentTarget).data('id')); // Debug
            console.log('Detail button element:', e.currentTarget); // Debug
            console.log('Detail button data-id:', $(e.currentTarget).data('id')); // Debug
            this.showDetailModal(e);
        });

        // Edit buttons
        $(document).on('click', '.edit-btn', (e) => {
            console.log('Edit button clicked', $(e.currentTarget).data('id'));
            this.showEditModal(e);
        });

        // Approval buttons
        $(document).on('click', '.approval-btn', (e) => {
            console.log('Approval button clicked', $(e.currentTarget).data('id'));
            this.showApprovalModal(e);
        });

        // Reject buttons
        $(document).on('click', '.reject-btn', (e) => {
            console.log('Reject button clicked', $(e.currentTarget).data('id'));
            this.showRejectModal(e);
        });

        // Delete buttons
        $(document).on('click', '.delete-btn', (e) => {
            console.log('Delete button clicked', $(e.currentTarget).data('id'));
            this.showDeleteModal(e);
        });

        // Form submissions
        $('#addApplicationForm').on('submit', (e) => this.handleAddSubmit(e));
        $('#approvalForm').on('submit', (e) => this.handleApprovalSubmit(e));
        $('#rejectApplicationForm').on('submit', (e) => this.handleRejectSubmit(e));
        $('#editApplicationForm').on('submit', (e) => {
            console.log('Edit form submitted'); // Debug
            this.handleEditSubmit(e);
        });

        // Edit form submit button
        $('#saveEditBtn').on('click', (e) => {
            console.log('Save edit button clicked'); // Debug
            e.preventDefault();
            this.handleEditSubmit();
        });

        // Interest rate selection
        $('#interest_rate_id').on('change', (e) => this.handleInterestRateChange(e));

        // Insurance checkboxes
        $('.insurance-checkbox').on('change', () => this.calculateInsuranceFees());

        // Currency inputs
        $(document).on('input', '.currency-input', (e) => this.formatCurrency(e));

        // Number only inputs
        $(document).on('input', 'input[pattern*="[0-9]"]', (e) => {
            const input = $(e.target);
            const value = input.val();
            const numbersOnly = value.replace(/[^0-9]/g, '');
            input.val(numbersOnly);

            // Auto-format CCCD (12 digits)
            if (input.attr('id') === 'customer_cmnd' || input.attr('id') === 'edit-customer-cmnd') {
                if (numbersOnly.length > 12) {
                    input.val(numbersOnly.substring(0, 12));
                }
            }

            // Auto-format phone (10-11 digits)
            if (input.attr('id') === 'customer_phone_main' || input.attr('id') === 'edit-customer-phone' ||
                input.attr('id') === 'edit-emergency-phone') {
                if (numbersOnly.length > 11) {
                    input.val(numbersOnly.substring(0, 11));
                }
            }
        });

        // Real-time validation
        $(document).on('blur', 'input[pattern*="[0-9]"]', (e) => {
            const input = $(e.target);
            const value = input.val();

            // Validate CCCD
            if (input.attr('id') === 'customer_cmnd' || input.attr('id') === 'edit-customer-cmnd') {
                if (value && value.length !== 12) {
                    this.showFieldError(input.attr('id'), 'CCCD phải có đúng 12 số');
                } else {
                    this.clearFieldError(input.attr('id'));
                }
            }

            // Validate phone
            if (input.attr('id') === 'customer_phone_main' || input.attr('id') === 'edit-customer-phone' ||
                input.attr('id') === 'edit-emergency-phone') {
                if (value && (value.length < 10 || value.length > 11)) {
                    this.showFieldError(input.attr('id'), 'Số điện thoại phải có 10-11 số');
                } else {
                    this.clearFieldError(input.attr('id'));
                }
            }
        });

        // Real-time email validation
        $(document).on('blur', 'input[type="email"]', (e) => {
            const input = $(e.target);
            const value = input.val();

            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                this.showFieldError(input.attr('id'), 'Email không hợp lệ');
            } else {
                this.clearFieldError(input.attr('id'));
            }
        });

        // Modal events
        $('#addApplicationModal').on('shown.bs.modal', () => this.onAddModalShown());
        $('#approvalModal').on('shown.bs.modal', () => this.onApprovalModalShown());
        $('#rejectApplicationModal').on('shown.bs.modal', () => this.onRejectModalShown());
        $('#editApplicationModal').on('shown.bs.modal', () => this.onEditModalShown());

        // Handle approve button
        $('#approve-btn').on('click', (e) => {
            e.preventDefault();
            const formData = new FormData($('#approvalForm')[0]);
            formData.append('action', 'approve_application');

            $.ajax({
                url: 'pages/admin/api/loan-applications.php?action=approve_application',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        let message = response.message;
                        if (response.contract_id && response.contract_code) {
                            message += `\nMã hợp đồng: ${response.contract_code}\nID hợp đồng: ${response.contract_id}`;
                        }
                        this.showMessage(message, 'success');
                        $('#approvalModal').modal('hide');
                        this.loadApplicationsList();
                    } else {
                        this.showError('Lỗi khi phê duyệt: ' + response.message);
                    }
                },
                error: (xhr, status, error) => {
                    this.showError('Lỗi khi phê duyệt: ' + error);
                }
            });
        });

        // Handle reject button
        $('#reject-btn').on('click', (e) => {
            e.preventDefault();
            const formData = new FormData($('#approvalForm')[0]);
            formData.append('action', 'reject_application');

            $.ajax({
                url: 'pages/admin/api/loan-applications.php?action=reject_application',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showMessage('Từ chối thành công!', 'success');
                        $('#approvalModal').modal('hide');
                        this.loadApplicationsList();
                    } else {
                        this.showError('Lỗi khi từ chối: ' + response.message);
                    }
                },
                error: (xhr, status, error) => {
                    this.showError('Lỗi khi từ chối: ' + error);
                }
            });
        });
    }

    showAddModal() {
        console.log('Showing add modal');
        $('#addApplicationModal').modal('show');
    }

    showDetailModal(e) {
        const applicationId = $(e.currentTarget).data('id');
        console.log('Showing detail modal for ID:', applicationId); // Debug

        // Kiểm tra xem modal có tồn tại không
        const modal = $('#applicationDetailModal');
        console.log('Detail modal exists:', modal.length); // Debug

        if (modal.length === 0) {
            console.error('Detail modal not found!'); // Debug
            this.showError('Modal chi tiết không tồn tại');
            return;
        }

        // Kiểm tra loading state và content
        console.log('Loading state exists:', $('#detail-loading-state').length); // Debug
        console.log('Modal content exists:', $('#detail-modal-content').length); // Debug

        this.loadApplicationDetail(applicationId);
        modal.modal('show');
        console.log('Detail modal shown'); // Debug
    }

    showEditModal(e) {
        const applicationId = $(e.currentTarget).data('id');
        console.log('Showing edit modal for ID:', applicationId); // Debug

        // Kiểm tra xem modal có tồn tại không
        const modal = $('#editApplicationModal');
        console.log('Edit modal exists:', modal.length); // Debug

        if (modal.length === 0) {
            console.error('Edit modal not found!'); // Debug
            this.showError('Modal chỉnh sửa không tồn tại');
            return;
        }

        // Kiểm tra loading state và content
        console.log('Edit loading state exists:', $('#edit-loading-state').length); // Debug
        console.log('Edit modal content exists:', $('#edit-modal-content').length); // Debug

        this.loadApplicationForEdit(applicationId);
        modal.modal('show');
        console.log('Edit modal shown'); // Debug
    }

    showApprovalModal(e) {
        const applicationId = $(e.currentTarget).data('id');
        console.log('Showing approval modal for ID:', applicationId);
        this.loadApplicationForApproval(applicationId);
        $('#approvalModal').modal('show');
    }

    showRejectModal(e) {
        const applicationId = $(e.currentTarget).data('id');
        this.loadApplicationForReject(applicationId);
        $('#rejectApplicationModal').modal('show');
    }

    showDeleteModal(e) {
        const applicationId = $(e.currentTarget).data('id');
        $('#deleteConfirmModal').modal('show');
        $('#confirmDeleteBtn').data('id', applicationId);
    }

    loadApplicationDetail(applicationId) {
        console.log('Loading application detail for ID:', applicationId); // Debug

        // Hiển thị loading state
        $('#detail-loading-state').show();
        $('#detail-modal-content').hide();
        console.log('Loading state shown'); // Debug

        $.ajax({
            url: 'pages/admin/api/loan-application-detail.php',
            method: 'GET',
            data: { id: applicationId },
            success: (response) => {
                console.log('Detail API response:', response); // Debug
                if (response.success) {
                    // Ẩn loading state và hiển thị nội dung
                    $('#detail-loading-state').hide();
                    $('#detail-modal-content').show();
                    console.log('Loading state hidden, content shown'); // Debug
                    this.populateDetailModal(response.data);
                } else {
                    console.error('Detail API error:', response.message); // Debug
                    $('#detail-loading-state').hide();
                    this.showError('Không thể tải thông tin đơn vay');
                }
            },
            error: (xhr, status, error) => {
                console.error('Detail API request failed:', error); // Debug
                $('#detail-loading-state').hide();
                this.showError('Có lỗi xảy ra khi tải thông tin');
            }
        });
    }

    loadApplicationForEdit(applicationId) {
        console.log('Loading application for edit, ID:', applicationId); // Debug

        // Hiển thị loading state
        $('#edit-loading-state').show();
        $('#edit-modal-content').hide();
        console.log('Edit loading state shown'); // Debug

        $.ajax({
            url: 'pages/admin/api/loan-application-detail.php',
            method: 'GET',
            data: { id: applicationId },
            success: (response) => {
                console.log('Edit API response:', response); // Debug
                if (response.success) {
                    // Ẩn loading state và hiển thị nội dung
                    $('#edit-loading-state').hide();
                    $('#edit-modal-content').show();
                    console.log('Edit loading state hidden, content shown'); // Debug
                    this.populateEditModal(response.data);
                } else {
                    console.error('Edit API error:', response.message); // Debug
                    $('#edit-loading-state').hide();
                    this.showError('Không thể tải thông tin đơn vay');
                }
            },
            error: (xhr, status, error) => {
                console.log('Edit API error:', error); // Debug
                $('#edit-loading-state').hide();
                this.showError('Có lỗi xảy ra khi tải thông tin');
            }
        });
    }

    loadApplicationForApproval(applicationId) {
        console.log('Loading application for approval, ID:', applicationId);

        $('#loading-state').show();
        $('#modal-content').hide();

        $.ajax({
            url: 'pages/admin/api/loan-application-detail.php',
            method: 'GET',
            data: { id: applicationId },
            success: (response) => {
                console.log('Approval API response:', response);
                $('#loading-state').hide();
                if (response.success) {
                    this.populateApprovalModal(response.data);
                    $('#modal-content').show();
                } else {
                    this.showError('Không thể tải thông tin đơn vay: ' + response.message);
                }
            },
            error: (xhr, status, error) => {
                console.error('Approval API error:', error);
                $('#loading-state').hide();
                this.showError('Có lỗi xảy ra khi tải thông tin: ' + error);
            }
        });
    }

    loadApplicationForReject(applicationId) {
        $('#reject-loading-state').show();
        $('#reject-modal-content').hide();

        $.ajax({
            url: 'pages/admin/api/loan-application-detail.php',
            method: 'GET',
            data: { id: applicationId },
            success: (response) => {
                $('#reject-loading-state').hide();
                if (response.success) {
                    this.populateRejectModal(response.data);
                    $('#reject-modal-content').show();
                } else {
                    this.showError('Không thể tải thông tin đơn vay');
                }
            },
            error: () => {
                $('#reject-loading-state').hide();
                this.showError('Có lỗi xảy ra khi tải thông tin');
            }
        });
    }

    populateDetailModal(data) {
        console.log('Populating detail modal with data:', data); // Debug

        // Debug từng trường
        console.log('Application code:', data.application_code);
        console.log('Customer name:', data.customer_name);
        console.log('Customer CMND:', data.customer_cmnd);
        console.log('Customer birth:', data.customer_birth_date);
        console.log('Customer phone:', data.customer_phone_main);
        console.log('Customer email:', data.customer_email);
        console.log('Customer job:', data.customer_job);
        console.log('Customer income:', data.customer_income);
        console.log('Customer company:', data.customer_company);
        console.log('Customer address:', data.customer_address);

        // Kiểm tra xem các element có tồn tại không
        console.log('Detail application code element exists:', $('#detail-application-code').length);
        console.log('Detail customer name element exists:', $('#detail-customer-name').length);

        $('#detail-application-code').text(data.application_code || 'N/A');
        $('#detail-customer-name').text(data.customer_name || 'N/A');
        $('#detail-customer-cmnd').text(data.customer_cmnd || 'N/A');
        $('#detail-customer-birth').text(data.customer_birth_date || 'N/A');
        $('#detail-customer-phone').text(data.customer_phone_main || 'N/A');
        $('#detail-customer-email').text(data.customer_email || 'N/A');
        $('#detail-customer-job').text(data.customer_job || 'N/A');
        $('#detail-customer-income').text(this.formatCurrency(data.customer_income));
        $('#detail-customer-company').text(data.customer_company || 'N/A');
        $('#detail-customer-address').text(data.customer_address || 'N/A');

        $('#detail-loan-amount').text(this.formatCurrency(data.loan_amount));
        $('#detail-approved-amount').text(this.formatCurrency(data.approved_amount));
        $('#detail-loan-term').text(data.loan_term_months + ' tháng');
        $('#detail-loan-purpose').text(data.loan_purpose || 'N/A');
        $('#detail-interest-rate').text(data.monthly_rate + '%/tháng');
        $('#detail-status').text(this.getStatusLabel(data.status));
        $('#detail-created-date').text(this.formatDate(data.created_at));
        $('#detail-decision-date').text(data.decision_date ? this.formatDate(data.decision_date) : 'Chưa có');

        $('#detail-asset-name').text(data.asset_name || 'N/A');
        $('#detail-asset-quantity').text(data.asset_quantity || 'N/A');
        $('#detail-asset-license').text(data.asset_license_plate || 'N/A');
        $('#detail-asset-frame').text(data.asset_frame_number || 'N/A');
        $('#detail-asset-engine').text(data.asset_engine_number || 'N/A');
        $('#detail-asset-value').text(this.formatCurrency(data.asset_value));
        $('#detail-asset-condition').text(data.asset_condition || 'N/A');
        $('#detail-asset-brand').text(data.asset_brand || 'N/A');
        $('#detail-asset-model').text(data.asset_model || 'N/A');
        $('#detail-asset-year').text(data.asset_year || 'N/A');

        $('#detail-emergency-name').text(data.emergency_contact_name || 'N/A');
        $('#detail-emergency-phone').text(data.emergency_contact_phone || 'N/A');
        $('#detail-emergency-relationship').text(data.emergency_contact_relationship || 'N/A');
        $('#detail-emergency-address').text(data.emergency_contact_address || 'N/A');
        $('#detail-emergency-note').text(data.emergency_contact_note || 'N/A');

        $('#detail-health-insurance').text(data.has_health_insurance ? 'Có' : 'Không');
        $('#detail-life-insurance').text(data.has_life_insurance ? 'Có' : 'Không');
        $('#detail-vehicle-insurance').text(data.has_vehicle_insurance ? 'Có' : 'Không');

        if (data.final_decision) {
            $('#detail-decision-section').show();
            $('#detail-final-decision').text(this.getDecisionLabel(data.final_decision));
            $('#detail-decision-notes').text(data.decision_notes || 'N/A');
        } else {
            $('#detail-decision-section').hide();
        }

        console.log('Detail modal populated successfully'); // Debug
    }

    populateEditModal(data) {
        console.log('Populating edit modal with data:', data); // Debug

        $('#edit-application-id').val(data.id);
        $('#edit-application-code').text(data.application_code);

        // Populate customer information display
        $('#edit-customer-id').val(data.customer_id);
        $('#edit-customer-name-display').text(data.customer_name_from_customer || data.customer_name || 'N/A');
        $('#edit-customer-cmnd-display').text(data.customer_cmnd || 'N/A');
        $('#edit-customer-phone-display').text(data.customer_phone_from_customer || data.customer_phone_main || 'N/A');

        $('#edit-customer-cmnd').val(data.customer_cmnd);
        $('#edit-customer-phone').val(data.customer_phone_main);
        $('#edit-customer-email').val(data.customer_email);
        $('#edit-customer-id-issued-place').val(data.customer_id_issued_place);
        $('#edit-emergency-name').val(data.emergency_contact_name);
        $('#edit-emergency-phone').val(data.emergency_contact_phone);
        $('#edit-emergency-relationship').val(data.emergency_contact_relationship);
        $('#edit-emergency-address').val(data.emergency_contact_address);
        $('#edit-emergency-note').val(data.emergency_contact_note);

        $('#edit-loan-amount').val(this.formatCurrencyForInput(data.loan_amount));
        $('#edit-loan-term').val(data.loan_term_months);
        $('#edit-loan-purpose').val(data.loan_purpose);
        $('#edit-interest-rate-id').val(data.interest_rate_id);
        $('#edit-approved-amount').val(this.formatCurrencyForInput(data.approved_amount));
        $('#edit-status').val(data.status);

        // Thêm các field còn thiếu
        $('#edit-customer-job').val(data.customer_job);
        $('#edit-customer-income').val(this.formatCurrencyForInput(data.customer_income));
        $('#edit-customer-company').val(data.customer_company);
        $('#edit-customer-address').val(data.customer_address);
        $('#edit-customer-birth-date').val(data.customer_birth_date);
        $('#edit-customer-id-issued-date').val(data.customer_id_issued_date);

        // Populate asset information display
        $('#edit-asset-id').val(data.asset_id);
        $('#edit-asset-name-display').text(data.asset_name || 'N/A');
        $('#edit-asset-license-display').text(data.asset_license_plate || 'N/A');
        $('#edit-asset-value-display').text(this.formatCurrency(data.asset_value) || 'N/A');

        $('#edit-asset-quantity').val(data.asset_quantity);
        $('#edit-asset-value').val(this.formatCurrencyForInput(data.asset_value));
        $('#edit-asset-condition').val(data.asset_condition);
        $('#edit-asset-notes').val(data.asset_description);

        // Insurance fields - sử dụng has_* thay vì health_insurance, life_insurance, vehicle_insurance
        $('#edit-health-insurance').val(data.has_health_insurance ? 'Có' : 'Không');
        $('#edit-life-insurance').val(data.has_life_insurance ? 'Có' : 'Không');
        $('#edit-vehicle-insurance').val(data.has_vehicle_insurance ? 'Có' : 'Không');

        $('#edit-notes').val(data.decision_notes);

        // Debug: Kiểm tra các field quan trọng
        console.log('Debug field values after population:');
        console.log('edit-customer-id:', $('#edit-customer-id').val());
        console.log('edit-customer-cmnd:', $('#edit-customer-cmnd').val());
        console.log('edit-customer-phone:', $('#edit-customer-phone').val());
        console.log('edit-loan-amount:', $('#edit-loan-amount').val());
        console.log('edit-loan-term:', $('#edit-loan-term').val());
        console.log('edit-loan-purpose:', $('#edit-loan-purpose').val());
        console.log('edit-interest-rate-id:', $('#edit-interest-rate-id').val());
        console.log('edit-asset-id:', $('#edit-asset-id').val());

        console.log('Edit modal populated successfully'); // Debug
    }

    populateApprovalModal(data) {
        console.log('Populating approval modal with data:', data);

        $('#modal-application-code').text(data.application_code);
        $('#modal-application-id').val(data.id);

        // Populate customer information
        $('#modal-customer-name').text(data.customer_name || data.customer_name_from_customer || 'N/A');
        $('#modal-customer-cmnd').text(data.customer_cmnd || 'N/A');
        $('#modal-customer-phone').text(data.customer_phone_main || data.customer_phone_from_customer || 'N/A');
        $('#modal-customer-email').text(data.customer_email || data.customer_email_from_customer || 'N/A');
        $('#modal-customer-job').text(data.customer_job || 'N/A');
        $('#modal-customer-income').text(this.formatCurrency(data.customer_income) || 'N/A');
        $('#modal-customer-address').text(data.customer_address || 'N/A');

        // Populate loan information
        $('#modal-loan-amount').text(this.formatCurrency(data.loan_amount));
        $('#modal-loan-term').text(data.loan_term_months + ' tháng');
        $('#modal-loan-purpose').text(data.loan_purpose || 'N/A');
        $('#modal-submission-date').text(this.formatDate(data.created_at));
        $('#modal-status').text(this.getStatusLabel(data.status));
        $('#modal-interest-rate').text((data.monthly_rate || data.interest_monthly_rate || 0) + '%/tháng');
        $('#modal-asset-name').text(data.asset_name || 'N/A');

        // Set default approved amount
        $('#modal-approved-amount-hidden').val(data.loan_amount);

        // Check approval permissions
        this.checkApprovalPermissions(data.id);
    }

    populateRejectModal(data) {
        $('#reject-application-id').val(data.id);
        $('#reject-application-code').text(data.application_code);
        $('#reject-customer-name').text(data.customer_name);
        $('#reject-loan-amount').text(this.formatCurrency(data.loan_amount));
        $('#reject-loan-term').text(data.loan_term_months + ' tháng');
        $('#reject-customer-phone').text(data.customer_phone_main);
        $('#reject-loan-purpose').text(data.loan_purpose);
        $('#reject-created-date').text(this.formatDate(data.created_at));
        $('#reject-current-status').text(this.getStatusLabel(data.status));
    }

    checkApprovalPermissions(applicationId) {
        $.ajax({
            url: 'pages/admin/api/check-approval-permissions.php',
            method: 'GET',
            data: { application_id: applicationId },
            success: (response) => {
                $('#permission-check').hide();
                if (response.success) {
                    const data = response.data;

                    // Set approval level and role
                    $('#modal-approval-level').val(data.approval_level);
                    $('#modal-current-user-id').val(data.application.created_by);

                    if (data.can_approve && data.current_status === 'pending') {
                        $('#approval-form-container').show();
                        $('#approve-btn').show();
                        $('#reject-btn').show();
                    } else {
                        $('#approval-form-container').hide();
                        $('#approve-btn').hide();
                        $('#reject-btn').hide();

                        if (!data.can_approve) {
                            this.showError(`Bạn không có quyền phê duyệt khoản vay ${this.formatCurrency(data.loan_amount)}. Cần ${data.approval_role} để phê duyệt.`);
                        } else if (data.current_status !== 'pending') {
                            this.showError('Đơn vay này đã được xử lý');
                        }
                    }
                } else {
                    this.showError('Không thể kiểm tra quyền phê duyệt');
                }
            },
            error: () => {
                $('#permission-check').hide();
                this.showError('Không thể kiểm tra quyền phê duyệt');
            }
        });
    }

    populateApprovalHistory(history) {
        const historyContainer = $('#approval-history');
        historyContainer.empty();

        if (history.length === 0) {
            historyContainer.html('<p class="text-muted">Chưa có lịch sử phê duyệt</p>');
            return;
        }

        let historyHtml = '<div class="timeline">';
        history.forEach((item, index) => {
            const actionClass = item.action === 'approve' ? 'success' :
                item.action === 'reject' ? 'danger' : 'warning';
            const actionIcon = item.action === 'approve' ? 'check' :
                item.action === 'reject' ? 'times' : 'question';

            historyHtml += `
                <div class="timeline-item">
                    <div class="timeline-marker bg-${actionClass}">
                        <i class="fas fa-${actionIcon}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">${item.role_name}</h6>
                            <small class="text-muted">${this.formatDate(item.approval_date)}</small>
                        </div>
                        <p class="mb-1"><strong>Người phê duyệt:</strong> ${item.approver_name}</p>
                        <p class="mb-1"><strong>Hành động:</strong> ${this.getActionLabel(item.action)}</p>
                        ${item.approved_amount ? `<p class="mb-1"><strong>Số tiền duyệt:</strong> ${this.formatCurrency(item.approved_amount)}</p>` : ''}
                        ${item.comments ? `<p class="mb-1"><strong>Ghi chú:</strong> ${item.comments}</p>` : ''}
                    </div>
                </div>
            `;
        });
        historyHtml += '</div>';

        historyContainer.html(historyHtml);
    }

    getActionLabel(action) {
        const labels = {
            'approve': 'Phê duyệt',
            'reject': 'Từ chối',
            'request_info': 'Yêu cầu bổ sung',
            'cancel': 'Hủy bỏ'
        };
        return labels[action] || action;
    }

    handleAddSubmit(e) {
        e.preventDefault(); // Ngăn form submit thông thường

        // Chuyển đổi currency format về số trước khi submit
        this.convertCurrencyInputsToNumbers();

        // Validate form
        if (!this.validateAddForm()) {
            return false;
        }

        // Calculate insurance fees
        this.calculateInsuranceFees();

        // Tạo application code tự động
        const applicationCode = 'LA' + new Date().getFullYear() +
            String(new Date().getMonth() + 1).padStart(2, '0') +
            String(new Date().getDate()).padStart(2, '0') +
            Math.floor(Math.random() * 10000).toString().padStart(4, '0');

        // Thêm application code vào form
        $('#addApplicationForm').append(`<input type="hidden" name="application_code" value="${applicationCode}">`);

        // Submit form bằng AJAX
        const formData = new FormData($('#addApplicationForm')[0]);

        // Disable submit button
        $('#addApplicationForm button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Đang tạo...');

        $.ajax({
            url: 'pages/admin/api/loan-applications.php?action=create_application',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: (response) => {
                console.log('Create application response:', response); // Debug
                if (response.success) {
                    this.showMessage('Tạo đơn vay thành công!', 'success');
                    $('#addApplicationModal').modal('hide');
                    this.loadApplicationsList();
                } else {
                    this.showError('Lỗi khi tạo đơn vay: ' + response.message);
                }
            },
            error: (xhr, status, error) => {
                console.log('Create application error:', error); // Debug
                this.showError('Lỗi khi tạo đơn vay: ' + error);
            },
            complete: () => {
                // Re-enable submit button
                $('#addApplicationForm button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Tạo đơn vay');
            }
        });

        return false;
    }

    handleApprovalSubmit(e) {
        // Validate form
        if (!this.validateApprovalForm()) {
            e.preventDefault();
            return false;
        }

        return true;
    }

    handleRejectSubmit(e) {
        // Validate form
        if (!this.validateRejectForm()) {
            e.preventDefault();
            return false;
        }

        return true;
    }

    validateAddForm() {
        const requiredFields = [
            'customer_name', 'customer_cmnd', 'customer_phone_main',
            'loan_amount', 'loan_term_months', 'monthly_rate',
            'customer_id', 'department_id'
        ];

        let isValid = true;

        requiredFields.forEach(field => {
            const value = $(`#${field}`).val();
            if (!value || value.trim() === '') {
                this.showFieldError(field, 'Trường này là bắt buộc');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        // Validate CCCD
        const cmnd = $('#customer_cmnd').val();
        if (cmnd && !/^[0-9]{12}$/.test(cmnd)) {
            this.showFieldError('customer_cmnd', 'CCCD phải có 12 số');
            isValid = false;
        }

        // Validate phone
        const phone = $('#customer_phone_main').val();
        if (phone && !/^[0-9]{10,11}$/.test(phone)) {
            this.showFieldError('customer_phone_main', 'Số điện thoại phải có 10-11 số');
            isValid = false;
        }

        // Validate email
        const email = $('#customer_email').val();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            this.showFieldError('customer_email', 'Email không hợp lệ');
            isValid = false;
        }

        // Validate loan term
        const loanTerm = $('#loan_term_months').val();
        if (loanTerm && ![3, 6, 12].includes(parseInt(loanTerm))) {
            this.showFieldError('loan_term_months', 'Thời hạn phải là 3, 6 hoặc 12 tháng');
            isValid = false;
        }

        return isValid;
    }

    validateApprovalForm() {
        const approvedAmount = $('#modal-approved-amount').val();
        const comments = $('#modal-comments').val();

        if (!approvedAmount || approvedAmount.trim() === '') {
            this.showError('Vui lòng nhập số tiền được duyệt');
            return false;
        }

        if (!comments || comments.trim() === '') {
            this.showError('Vui lòng nhập ghi chú đánh giá');
            return false;
        }

        return true;
    }

    validateRejectForm() {
        const reason = $('#reject-reason').val();
        const comments = $('#reject-comments').val();

        if (!reason) {
            this.showError('Vui lòng chọn lý do từ chối');
            return false;
        }

        if (!comments || comments.trim() === '') {
            this.showError('Vui lòng nhập ghi chú chi tiết');
            return false;
        }

        return true;
    }

    validateEditForm() {
        const requiredFields = [
            'customer_id', 'customer_cmnd', 'customer_phone',
            'loan_amount', 'loan_term', 'loan_purpose',
            'interest_rate_id', 'asset_id'
        ];

        let isValid = true;

        requiredFields.forEach(field => {
            const value = $(`#edit-${field}`).val();
            console.log(`Checking field: edit-${field}, value:`, value); // Debug
            if (!value || value.trim() === '') {
                this.showFieldError(`edit-${field}`, 'Trường này là bắt buộc');
                isValid = false;
            } else {
                this.clearFieldError(`edit-${field}`);
            }
        });

        // Validate CCCD
        const cmnd = $('#edit-customer-cmnd').val();
        console.log('CCCD value:', cmnd); // Debug
        if (cmnd && !/^[0-9]{12}$/.test(cmnd)) {
            this.showFieldError('edit-customer-cmnd', 'CCCD phải có 12 số');
            isValid = false;
        }

        // Validate phone
        const phone = $('#edit-customer-phone').val();
        console.log('Phone value:', phone); // Debug
        if (phone && !/^[0-9]{10,11}$/.test(phone)) {
            this.showFieldError('edit-customer-phone', 'Số điện thoại phải có 10-11 số');
            isValid = false;
        }

        // Validate emergency phone
        const emergencyPhone = $('#edit-emergency-phone').val();
        console.log('Emergency phone value:', emergencyPhone); // Debug
        if (emergencyPhone && !/^[0-9]{10,11}$/.test(emergencyPhone)) {
            this.showFieldError('edit-emergency-phone', 'Số điện thoại phải có 10-11 số');
            isValid = false;
        }

        // Validate email
        const email = $('#edit-customer-email').val();
        console.log('Email value:', email); // Debug
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            this.showFieldError('edit-customer-email', 'Email không hợp lệ');
            isValid = false;
        }

        // Validate loan term
        const loanTerm = $('#edit-loan-term').val();
        console.log('Loan term value:', loanTerm); // Debug
        if (loanTerm && ![3, 6, 12].includes(parseInt(loanTerm))) {
            this.showFieldError('edit-loan-term', 'Thời hạn phải là 3, 6 hoặc 12 tháng');
            isValid = false;
        }

        console.log('Form validation result:', isValid); // Debug
        return isValid;
    }

    handleInterestRateChange(e) {
        const selectedOption = $(e.target).find('option:selected');
        const monthlyRate = selectedOption.data('monthly-rate');
        const dailyRate = selectedOption.data('daily-rate');

        if (monthlyRate) {
            $('#monthly_rate').val(monthlyRate);
        }
        if (dailyRate) {
            $('#daily_rate').val(dailyRate);
        }
    }

    calculateInsuranceFees() {
        const loanAmount = parseFloat($('#loan_amount').val().replace(/,/g, '')) || 0;
        const termMonths = parseInt($('#loan_term_months').val()) || 0;
        const monthlyRate = parseFloat($('#monthly_rate').val()) || 0;

        // Calculate interest
        const monthlyInterest = loanAmount * (monthlyRate / 100);
        const totalInterest = monthlyInterest * termMonths;
        const totalAmount = loanAmount + totalInterest;

        // Calculate insurance fees
        const healthInsurance = $('#has_health_insurance').is(':checked') ? totalAmount * 0.0125 : 0;
        const lifeInsurance = $('#has_life_insurance').is(':checked') ? totalAmount * 0.02 : 0;
        const vehicleInsurance = $('#has_vehicle_insurance').is(':checked') ? totalAmount * 0.0075 : 0;

        const totalInsuranceFee = healthInsurance + lifeInsurance + vehicleInsurance;

        // Update display
        $('#disbursement_amount').val(this.formatCurrencyForInput(loanAmount));
        $('#interest_in_term').val(this.formatCurrencyForInput(totalInterest));
        $('#total_for_insurance').val(this.formatCurrencyForInput(totalAmount));
        $('#total_insurance_fee').val(this.formatCurrencyForInput(totalInsuranceFee));

        $('#health_insurance_fee').val(this.formatCurrencyForInput(healthInsurance));
        $('#hospitalization_insurance_fee').val(this.formatCurrencyForInput(lifeInsurance));
        $('#vehicle_insurance_fee').val(this.formatCurrencyForInput(vehicleInsurance));
    }

    formatCurrency(e) {
        const input = $(e.target);
        let value = input.val().replace(/[^\d]/g, '');

        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
            input.val(value);
        }
    }

    formatCurrencyForInput(amount) {
        if (!amount) return '';
        return parseInt(amount).toLocaleString('vi-VN');
    }

    formatCurrency(amount) {
        if (!amount) return '0 VNĐ';
        return parseInt(amount).toLocaleString('vi-VN') + ' VNĐ';
    }

    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    getStatusLabel(status) {
        const labels = {
            'draft': 'Nháp',
            'pending': 'Chờ duyệt',
            'approved': 'Đã duyệt',
            'rejected': 'Đã từ chối',
            'disbursed': 'Đã giải ngân',
            'cancelled': 'Đã hủy'
        };
        return labels[status] || status;
    }

    getDecisionLabel(decision) {
        const labels = {
            'approved': 'Duyệt',
            'rejected': 'Từ chối',
            'pending': 'Chờ xem xét'
        };
        return labels[decision] || decision;
    }

    showError(message) {
        // Create alert
        const alert = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Add to page
        $('.container-fluid').prepend(alert);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }

    showFieldError(fieldId, message) {
        const field = $(`#${fieldId}`);
        field.addClass('is-invalid');

        // Remove existing error message
        field.siblings('.invalid-feedback').remove();

        // Add error message
        field.after(`<div class="invalid-feedback">${message}</div>`);
    }

    clearFieldError(fieldId) {
        const field = $(`#${fieldId}`);
        field.removeClass('is-invalid');
        field.siblings('.invalid-feedback').remove();
    }

    initCurrencyFormatters() {
        // Initialize currency formatters for existing inputs
        $('.currency-input').each((index, element) => {
            $(element).on('input', (e) => this.formatCurrency(e));
        });
    }

    initDataTables() {
        // Initialize DataTables if available
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json'
                },
                pageLength: 25,
                order: [[6, 'desc']] // Sort by created date descending
            });
        }
    }

    onAddModalShown() {
        // Focus on first input
        $('#customer_name').focus();

        // Initialize currency formatters for new inputs
        this.initCurrencyFormatters();

        // Auto-select default values
        $('#loan_term_months').val('12'); // Default to 12 months
    }

    onApprovalModalShown() {
        // Focus on approved amount input
        $('#modal-approved-amount').focus();
    }

    onRejectModalShown() {
        // Focus on reason select
        $('#reject-reason').focus();
    }

    /**
     * Xử lý submit form chỉnh sửa
     */
    handleEditSubmit() {
        console.log('handleEditSubmit called'); // Debug
        const form = $('#editApplicationForm')[0];

        console.log('Form validity:', form.checkValidity()); // Debug
        if (!form.checkValidity()) {
            console.log('Form HTML5 validation failed'); // Debug
            form.reportValidity();
            return;
        }

        // Chuyển đổi currency inputs từ format về số
        this.convertEditCurrencyInputsToNumbers();

        // Validate form manually
        console.log('Starting manual validation...'); // Debug
        if (!this.validateEditForm()) {
            console.log('Manual validation failed'); // Debug
            return;
        }

        console.log('All validations passed, proceeding with submit'); // Debug
        const formData = new FormData(form);
        formData.append('action', 'edit');

        // Debug form data
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Disable button
        $('#saveEditBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...');

        $.ajax({
            url: 'pages/admin/api/loan-applications.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: (response) => {
                console.log('Submit response:', response); // Debug
                if (response.success) {
                    // Hiển thị thông báo thành công
                    this.showMessage('Cập nhật đơn vay thành công!', 'success');

                    // Đóng modal
                    $('#editApplicationModal').modal('hide');

                    // Reload bảng
                    this.loadApplicationsList();
                } else {
                    this.showError('Lỗi khi cập nhật đơn vay: ' + response.message);
                }
            },
            error: (xhr, status, error) => {
                console.log('Submit error:', error); // Debug
                this.showError('Lỗi khi cập nhật đơn vay: ' + error);
            },
            complete: () => {
                // Re-enable button
                $('#saveEditBtn').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Lưu thay đổi');
            }
        });
    }

    /**
     * Xử lý khi modal edit được hiển thị
     */
    onEditModalShown() {
        // Load danh sách khách hàng, tài sản, lãi suất
        this.loadEditFormData();

        // Focus on first input after data is loaded
        setTimeout(() => {
            $('#edit-customer-cmnd').focus();
        }, 500);
    }

    /**
     * Load dữ liệu cho form edit
     */
    loadEditFormData() {
        console.log('Loading edit form data...'); // Debug

        // Load customers - tạo API endpoint đơn giản
        $.get('pages/admin/api/customers.php?action=get_all', (response) => {
            console.log('Customers API response:', response); // Debug
            if (response.success) {
                const customerSelect = $('#edit-customer-id');
                customerSelect.find('option:not(:first)').remove();
                response.data.forEach(customer => {
                    customerSelect.append(`<option value="${customer.id}">${customer.name} - ${customer.cmnd || customer.id_number}</option>`);
                });
                console.log('Customers loaded:', response.data.length); // Debug
            } else {
                console.error('Failed to load customers:', response.message); // Debug
            }
        });

        // Load assets - tạo API endpoint đơn giản
        $.get('pages/admin/api/assets.php?action=get_all', (response) => {
            console.log('Assets API response:', response); // Debug
            if (response.success) {
                const assetSelect = $('#edit-asset-id');
                assetSelect.find('option:not(:first)').remove();
                response.data.forEach(asset => {
                    assetSelect.append(`<option value="${asset.id}">${asset.name} - ${asset.license_plate}</option>`);
                });
                console.log('Assets loaded:', response.data.length); // Debug
            } else {
                console.error('Failed to load assets:', response.message); // Debug
            }
        });

        // Load interest rates
        $.get('pages/admin/api/interest-rates.php?action=get_rates', (response) => {
            console.log('Interest rates API response:', response); // Debug
            if (response.success) {
                const rateSelect = $('#edit-interest-rate-id');
                rateSelect.find('option:not(:first)').remove();
                response.data.forEach(rate => {
                    rateSelect.append(`<option value="${rate.id}">${rate.monthly_rate}% - ${rate.description}</option>`);
                });
                console.log('Interest rates loaded:', response.data.length); // Debug
            } else {
                console.error('Failed to load interest rates:', response.message); // Debug
            }
        });

        console.log('Edit form data loading completed'); // Debug
    }

    /**
     * Hiển thị thông báo thành công
     */
    showMessage(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-info';
        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('.container-fluid').prepend(alert);

        setTimeout(() => {
            $('.alert').alert('close');
        }, 3000);
    }

    /**
     * Reload danh sách đơn vay
     */
    loadApplicationsList() {
        // Reload page để cập nhật bảng
        location.reload();
    }

    /**
     * Chuyển đổi currency inputs từ format về số trước khi submit
     */
    convertCurrencyInputsToNumbers() {
        $('.currency-input').each((index, element) => {
            const input = $(element);
            const value = input.val();
            if (value) {
                // Loại bỏ tất cả ký tự không phải số
                const numberValue = value.replace(/[^\d]/g, '');
                input.val(numberValue);
            }
        });
    }

    /**
     * Chuyển đổi currency inputs từ format về số cho form edit
     */
    convertEditCurrencyInputsToNumbers() {
        $('#editApplicationForm .currency-input').each((index, element) => {
            const input = $(element);
            const value = input.val();
            if (value) {
                // Loại bỏ tất cả ký tự không phải số
                const numberValue = value.replace(/[^\d]/g, '');
                input.val(numberValue);
            }
        });
    }
}

// Initialize when document is ready
$(document).ready(() => {
    console.log('Document ready, initializing LoanApplicationsJS...');
    window.loanApplicationsJS = new LoanApplicationsJS();
    console.log('LoanApplicationsJS instance created:', window.loanApplicationsJS);
}); 