<?php

/**
 * Loan Applications Modals
 * Chứa tất cả các modal cho loan applications
 */

// Modal Tạo đơn vay mới
?>
<!-- Modal Tạo đơn vay mới -->
<div class="modal fade" id="addApplicationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Tạo đơn vay mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addApplicationForm" method="POST" action="pages/admin/api/loan-applications.php?action=create_application">
                <div class="modal-body">
                    <!-- Thông tin khách hàng -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Thông tin khách hàng
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_cmnd" class="form-label">CCCD <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_cmnd" name="customer_cmnd" pattern="[0-9]{12}" maxlength="12" placeholder="123456789012" required>
                                <small class="form-text text-muted">Nhập 12 số CCCD</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_phone_main" class="form-label">Điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customer_phone_main" name="customer_phone_main" pattern="[0-9]{10,11}" placeholder="0123456789" required>
                                <small class="form-text text-muted">Nhập 10-11 số điện thoại</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" placeholder="example@email.com">
                                <small class="form-text text-muted">Nhập địa chỉ email hợp lệ</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_birth_date" class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" id="customer_birth_date" name="customer_birth_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id_issued_place" class="form-label">Nơi cấp CCCD</label>
                                <select class="form-select" id="customer_id_issued_place" name="customer_id_issued_place">
                                    <option value="">Chọn nơi cấp</option>
                                    <option value="Bộ Công an">Bộ Công an</option>
                                    <option value="Cục CSQLHV về TTXH">Cục CSQLHV về TTXH</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id_issued_date" class="form-label">Ngày cấp CCCD</label>
                                <input type="date" class="form-control" id="customer_id_issued_date" name="customer_id_issued_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_job" class="form-label">Nghề nghiệp</label>
                                <input type="text" class="form-control" id="customer_job" name="customer_job">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_income" class="form-label">Thu nhập</label>
                                <input type="text" class="form-control currency-input" id="customer_income" name="customer_income">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_company" class="form-label">Công ty</label>
                                <input type="text" class="form-control" id="customer_company" name="customer_company">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="customer_address" class="form-label">Địa chỉ</label>
                                <textarea class="form-control" id="customer_address" name="customer_address" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin khoản vay -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-money-bill me-2"></i>Thông tin khoản vay
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="loan_amount" class="form-label">Số tiền vay <span class="text-danger">*</span></label>
                                <input type="text" class="form-control currency-input" id="loan_amount" name="loan_amount" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="loan_term_months" class="form-label">Thời hạn (tháng) <span class="text-danger">*</span></label>
                                <select class="form-select" id="loan_term_months" name="loan_term_months" required>
                                    <option value="">Chọn thời hạn</option>
                                    <option value="3">3 tháng</option>
                                    <option value="6">6 tháng</option>
                                    <option value="12">12 tháng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="loan_purpose" class="form-label">Mục đích vay</label>
                                <input type="text" class="form-control" id="loan_purpose" name="loan_purpose">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monthly_rate" class="form-label">Lãi suất (%/tháng) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="monthly_rate" name="monthly_rate" required step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="daily_rate" class="form-label">Lãi suất (%/ngày)</label>
                                <input type="number" class="form-control" id="daily_rate" name="daily_rate" step="0.001" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate_id" class="form-label">Lãi suất <span class="text-danger">*</span></label>
                                <select class="form-control" id="interest_rate_id" name="interest_rate_id" required>
                                    <option value="">Chọn lãi suất</option>
                                    <?php foreach ($this->interestRates as $rate): ?>
                                        <option value="<?= $rate['id'] ?>" data-monthly-rate="<?= $rate['monthly_rate'] ?>" data-daily-rate="<?= $rate['daily_rate'] ?>">
                                            <?= htmlspecialchars($rate['description']) ?> - <?= number_format($rate['monthly_rate'], 2) ?>%/tháng
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                                <select class="form-control" id="customer_id" name="customer_id" required>
                                    <option value="">Chọn khách hàng</option>
                                    <?php foreach ($this->customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>">
                                            <?= htmlspecialchars($customer['name']) ?> - <?= htmlspecialchars($customer['phone']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft">Nháp</option>
                                    <option value="pending">Chờ duyệt</option>
                                    <option value="approved">Đã duyệt</option>
                                    <option value="rejected">Đã từ chối</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Phòng ban <span class="text-danger">*</span></label>
                                <select class="form-control" id="department_id" name="department_id" required>
                                    <option value="">Chọn phòng ban</option>
                                    <?php foreach ($this->departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>">
                                            <?= htmlspecialchars($dept['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin tài sản thế chấp -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-car me-2"></i>Tài sản thế chấp
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_name" class="form-label">Tên tài sản</label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_quantity" class="form-label">Số lượng</label>
                                <input type="number" class="form-control" id="asset_quantity" name="asset_quantity" min="1" value="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_value" class="form-label">Giá trị tài sản</label>
                                <input type="text" class="form-control currency-input" id="asset_value" name="asset_value">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_license_plate" class="form-label">Biển số xe</label>
                                <input type="text" class="form-control" id="asset_license_plate" name="asset_license_plate">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_frame_number" class="form-label">Số khung</label>
                                <input type="text" class="form-control" id="asset_frame_number" name="asset_frame_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_engine_number" class="form-label">Số máy</label>
                                <input type="text" class="form-control" id="asset_engine_number" name="asset_engine_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_registration_number" class="form-label">Số đăng ký</label>
                                <input type="text" class="form-control" id="asset_registration_number" name="asset_registration_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_registration_date" class="form-label">Ngày đăng ký</label>
                                <input type="date" class="form-control" id="asset_registration_date" name="asset_registration_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_brand" class="form-label">Thương hiệu</label>
                                <input type="text" class="form-control" id="asset_brand" name="asset_brand">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_model" class="form-label">Model</label>
                                <input type="text" class="form-control" id="asset_model" name="asset_model">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_year" class="form-label">Năm sản xuất</label>
                                <input type="number" class="form-control" id="asset_year" name="asset_year" min="1900" max="2030">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_fuel_type" class="form-label">Loại nhiên liệu</label>
                                <select class="form-select" id="asset_fuel_type" name="asset_fuel_type">
                                    <option value="">Chọn loại nhiên liệu</option>
                                    <option value="gasoline">Xăng</option>
                                    <option value="diesel">Dầu</option>
                                    <option value="electric">Điện</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_color" class="form-label">Màu sắc</label>
                                <input type="text" class="form-control" id="asset_color" name="asset_color">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_cc" class="form-label">Dung tích (cc)</label>
                                <input type="number" class="form-control" id="asset_cc" name="asset_cc" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_fuel_type" class="form-label">Loại nhiên liệu</label>
                                <select class="form-select" id="asset_fuel_type" name="asset_fuel_type">
                                    <option value="">Chọn loại nhiên liệu</option>
                                    <option value="gasoline">Xăng</option>
                                    <option value="diesel">Dầu</option>
                                    <option value="electric">Điện</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_condition" class="form-label">Tình trạng</label>
                                <select class="form-select" id="asset_condition" name="asset_condition">
                                    <option value="">Chọn tình trạng</option>
                                    <option value="new">Mới</option>
                                    <option value="good">Tốt</option>
                                    <option value="fair">Khá</option>
                                    <option value="poor">Kém</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="asset_description" class="form-label">Mô tả tài sản</label>
                                <textarea class="form-control" id="asset_description" name="asset_description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin liên hệ khẩn cấp -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-phone me-2"></i>Thông tin liên hệ khẩn cấp
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergency_contact_name" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergency_contact_phone" class="form-label">Điện thoại</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" pattern="[0-9]{10,11}" placeholder="0123456789">
                                <small class="form-text text-muted">Nhập 10-11 số điện thoại</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergency_contact_relationship" class="form-label">Mối quan hệ</label>
                                <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergency_contact_address" class="form-label">Địa chỉ</label>
                                <textarea class="form-control" id="emergency_contact_address" name="emergency_contact_address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="emergency_contact_note" class="form-label">Ghi chú liên hệ khẩn cấp</label>
                                <textarea class="form-control" id="emergency_contact_note" name="emergency_contact_note" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin bảo hiểm -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Thông tin bảo hiểm
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input insurance-checkbox" type="checkbox" id="has_health_insurance" name="has_health_insurance" value="1">
                                <label class="form-check-label" for="has_health_insurance">
                                    Bảo hiểm sức khỏe người vay tín dụng (1.25%)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input insurance-checkbox" type="checkbox" id="has_life_insurance" name="has_life_insurance" value="1">
                                <label class="form-check-label" for="has_life_insurance">
                                    Bảo hiểm trợ cấp nằm viện (2%)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input insurance-checkbox" type="checkbox" id="has_vehicle_insurance" name="has_vehicle_insurance" value="1">
                                <label class="form-check-label" for="has_vehicle_insurance">
                                    Bảo hiểm tự nguyện xe ô tô (0.75%)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Tạo đơn vay
                    </button>
                </div>

                <!-- Hidden fields for form submission -->
                <input type="hidden" name="final_decision" value="">
                <input type="hidden" name="decision_date" value="">
                <input type="hidden" name="approved_amount" value="">
                <input type="hidden" name="decision_notes" value="">
                <input type="hidden" name="asset_id" value="">
                <input type="hidden" name="current_approval_level" value="1">
                <input type="hidden" name="highest_approval_level" value="1">
                <input type="hidden" name="total_approval_levels" value="1">
            </form>
        </div>
    </div>
</div>

<!-- Modal phê duyệt đơn vay -->
<div class="modal fade" id="approvalModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>Chi tiết hồ sơ vay - <span id="modal-application-code"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="loading-state" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải thông tin đơn vay...</p>
                </div>

                <!-- Main Content -->
                <div id="modal-content" style="display: none;">
                    <p class="text-muted mb-4">Thông tin chi tiết và quyết định phê duyệt</p>

                    <!-- Thông tin cá nhân và khoản vay -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Thông tin cá nhân
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Họ tên:</strong> <span id="modal-customer-name"></span></p>
                                    <p><strong>CCCD:</strong> <span id="modal-customer-cmnd"></span></p>
                                    <p><strong>Điện thoại:</strong> <span id="modal-customer-phone"></span></p>
                                    <p><strong>Email:</strong> <span id="modal-customer-email"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Nghề nghiệp:</strong> <span id="modal-customer-job"></span></p>
                                    <p><strong>Thu nhập:</strong> <span id="modal-customer-income"></span></p>
                                    <p><strong>Địa chỉ:</strong> <span id="modal-customer-address"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-money-bill me-2"></i>Thông tin khoản vay
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Số tiền vay:</strong> <span id="modal-loan-amount"></span></p>
                                    <p><strong>Thời hạn:</strong> <span id="modal-loan-term"></span></p>
                                    <p><strong>Mục đích:</strong> <span id="modal-loan-purpose"></span></p>
                                    <p><strong>Ngày nộp:</strong> <span id="modal-submission-date"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Trạng thái:</strong> <span id="modal-status"></span></p>
                                    <p><strong>Lãi suất:</strong> <span id="modal-interest-rate"></span></p>
                                    <p><strong>Tài sản thế chấp:</strong> <span id="modal-asset-name"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tài liệu đính kèm -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-paperclip me-2"></i>Tài liệu đính kèm
                            </h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        <span>Chứng minh nhân dân</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        <span>Sổ hộ khẩu</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        <span>Bảng lương</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        <span>Sao kê ngân hàng</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ghi chú đánh giá -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-edit me-2"></i>Ghi chú đánh giá
                            </h6>
                            <div class="mb-3">
                                <textarea class="form-control" id="modal-comments" name="comments" rows="4"
                                    placeholder="Nhập ghi chú về quyết định của bạn..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Permission Check -->
                    <div class="alert alert-info" id="permission-check">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Đang kiểm tra...</span>
                            </div>
                            <span>Đang kiểm tra quyền phê duyệt...</span>
                        </div>
                    </div>

                    <!-- Approval Form -->
                    <div id="approval-form-container" style="display: none;">
                        <form id="approvalForm" method="POST" action="pages/admin/api/loan-applications.php?action=approve_application">
                            <input type="hidden" name="application_id" id="modal-application-id">
                            <input type="hidden" name="approval_level" id="modal-approval-level">
                            <input type="hidden" name="current_user_id" id="modal-current-user-id">
                            <input type="hidden" name="approved_amount" id="modal-approved-amount-hidden">
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" form="approvalForm" class="btn btn-primary" id="approve-btn" style="display: none;">
                    <i class="fas fa-check me-2"></i>Phê duyệt
                </button>
                <button type="button" class="btn btn-danger" id="reject-btn" style="display: none;">
                    <i class="fas fa-times me-2"></i>Từ chối
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal từ chối đơn vay -->
<div class="modal fade" id="rejectApplicationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>Từ chối đơn vay
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="reject-loading-state" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải thông tin đơn vay...</p>
                </div>

                <!-- Reject Form -->
                <div id="reject-modal-content" style="display: none;">
                    <form id="rejectApplicationForm" method="POST">
                        <input type="hidden" id="reject-application-id" name="application_id">
                        <input type="hidden" name="action" value="reject">

                        <!-- Thông tin đơn vay -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Thông tin đơn vay</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Mã đơn vay:</strong> <span id="reject-application-code"></span></p>
                                                <p><strong>Khách hàng:</strong> <span id="reject-customer-name"></span></p>
                                                <p><strong>Số tiền vay:</strong> <span id="reject-loan-amount"></span></p>
                                                <p><strong>Thời hạn:</strong> <span id="reject-loan-term"></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Điện thoại:</strong> <span id="reject-customer-phone"></span></p>
                                                <p><strong>Mục đích vay:</strong> <span id="reject-loan-purpose"></span></p>
                                                <p><strong>Ngày tạo:</strong> <span id="reject-created-date"></span></p>
                                                <p><strong>Trạng thái hiện tại:</strong> <span id="reject-current-status"></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lý do từ chối -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Lý do từ chối</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="reject-reason" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                            <select class="form-control" id="reject-reason" name="reject_reason" required>
                                                <option value="">Chọn lý do từ chối</option>
                                                <option value="insufficient_income">Thu nhập không đủ</option>
                                                <option value="poor_credit_history">Lịch sử tín dụng xấu</option>
                                                <option value="incomplete_documents">Thiếu hồ sơ</option>
                                                <option value="collateral_issues">Vấn đề về tài sản thế chấp</option>
                                                <option value="policy_violation">Vi phạm chính sách</option>
                                                <option value="risk_assessment">Đánh giá rủi ro cao</option>
                                                <option value="other">Lý do khác</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reject-comments" class="form-label">Ghi chú chi tiết <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="reject-comments" name="reject_comments" rows="4"
                                                placeholder="Nhập chi tiết lý do từ chối..." required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reject-level" class="form-label">Cấp độ từ chối</label>
                                            <select class="form-control" id="reject-level" name="reject_level">
                                                <option value="1">Cấp 1 - Nhân viên</option>
                                                <option value="2">Cấp 2 - Trưởng nhóm</option>
                                                <option value="3">Cấp 3 - Quản lý</option>
                                                <option value="4">Cấp 4 - Giám đốc</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cảnh báo -->
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Lưu ý:</strong> Hành động này sẽ thay đổi trạng thái đơn vay thành "Đã từ chối" và không thể hoàn tác.
                            Vui lòng kiểm tra kỹ thông tin trước khi xác nhận.
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="reject-confirm-btn">
                    <i class="fas fa-times me-2"></i>Xác nhận từ chối
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chi tiết đơn vay -->
<div class="modal fade" id="applicationDetailModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>Chi tiết đơn vay - <span id="detail-application-code"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="detail-loading-state" class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải thông tin đơn vay...</p>
                </div>

                <!-- Main Content -->
                <div id="detail-modal-content" style="display: none;">
                    <!-- Thông tin cơ bản -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin khách hàng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Họ tên:</strong> <span id="detail-customer-name"></span></p>
                                            <p><strong>CCCD:</strong> <span id="detail-customer-cmnd"></span></p>
                                            <p><strong>Ngày sinh:</strong> <span id="detail-customer-birth"></span></p>
                                            <p><strong>Điện thoại:</strong> <span id="detail-customer-phone"></span></p>
                                            <p><strong>Email:</strong> <span id="detail-customer-email"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Nghề nghiệp:</strong> <span id="detail-customer-job"></span></p>
                                            <p><strong>Thu nhập:</strong> <span id="detail-customer-income"></span></p>
                                            <p><strong>Công ty:</strong> <span id="detail-customer-company"></span></p>
                                            <p><strong>Địa chỉ:</strong> <span id="detail-customer-address"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-money-bill me-2"></i>Thông tin khoản vay</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Số tiền vay:</strong> <span id="detail-loan-amount"></span></p>
                                            <p><strong>Số tiền duyệt:</strong> <span id="detail-approved-amount"></span></p>
                                            <p><strong>Thời hạn:</strong> <span id="detail-loan-term"></span></p>
                                            <p><strong>Mục đích:</strong> <span id="detail-loan-purpose"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Lãi suất:</strong> <span id="detail-interest-rate"></span></p>
                                            <p><strong>Trạng thái:</strong> <span id="detail-status"></span></p>
                                            <p><strong>Ngày tạo:</strong> <span id="detail-created-date"></span></p>
                                            <p><strong>Ngày quyết định:</strong> <span id="detail-decision-date"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin tài sản thế chấp -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-car me-2"></i>Tài sản thế chấp</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Tên tài sản:</strong> <span id="detail-asset-name"></span></p>
                                            <p><strong>Số lượng:</strong> <span id="detail-asset-quantity"></span></p>
                                            <p><strong>Biển số:</strong> <span id="detail-asset-license"></span></p>
                                            <p><strong>Số khung:</strong> <span id="detail-asset-frame"></span></p>
                                            <p><strong>Số máy:</strong> <span id="detail-asset-engine"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Giá trị:</strong> <span id="detail-asset-value"></span></p>
                                            <p><strong>Tình trạng:</strong> <span id="detail-asset-condition"></span></p>
                                            <p><strong>Thương hiệu:</strong> <span id="detail-asset-brand"></span></p>
                                            <p><strong>Model:</strong> <span id="detail-asset-model"></span></p>
                                            <p><strong>Năm sản xuất:</strong> <span id="detail-asset-year"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin liên hệ khẩn cấp -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-phone me-2"></i>Thông tin liên hệ khẩn cấp</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Họ tên:</strong> <span id="detail-emergency-name"></span></p>
                                            <p><strong>Điện thoại:</strong> <span id="detail-emergency-phone"></span></p>
                                            <p><strong>Mối quan hệ:</strong> <span id="detail-emergency-relationship"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Địa chỉ:</strong> <span id="detail-emergency-address"></span></p>
                                            <p><strong>Ghi chú:</strong> <span id="detail-emergency-note"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin bảo hiểm -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Thông tin bảo hiểm</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Bảo hiểm y tế:</strong> <span id="detail-health-insurance"></span></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Bảo hiểm nhân thọ:</strong> <span id="detail-life-insurance"></span></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Bảo hiểm xe cộ:</strong> <span id="detail-vehicle-insurance"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ghi chú quyết định -->
                    <div class="row mb-4" id="detail-decision-section" style="display: none;">
                        <div class="col-12">
                            <div class="card border-dark">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Ghi chú quyết định</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Quyết định:</strong> <span id="detail-final-decision"></span></p>
                                    <p><strong>Ghi chú:</strong> <span id="detail-decision-notes"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="detail-edit-btn" style="display: none;">
                    <i class="fas fa-edit me-2"></i>Sửa đơn vay
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa đơn vay -->
<div class="modal fade" id="editApplicationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa đơn vay - <span id="edit-application-code"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="edit-loading-state" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2">Đang tải thông tin đơn vay...</p>
                </div>

                <!-- Main Content -->
                <div id="edit-modal-content" style="display: none;">
                    <form id="editApplicationForm">
                        <input type="hidden" id="edit-application-id" name="application_id">

                        <!-- Thông tin khách hàng -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin khách hàng</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="edit-customer-info" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                                                    <div class="form-control-plaintext" id="edit-customer-info" style="background-color: #f8f9fa; padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.375rem;">
                                                        <strong id="edit-customer-name-display"></strong><br>
                                                        <small class="text-muted">CCCD: <span id="edit-customer-cmnd-display"></span> | ĐT: <span id="edit-customer-phone-display"></span></small>
                                                    </div>
                                                    <input type="hidden" id="edit-customer-id" name="customer_id">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-customer-cmnd" class="form-label">CCCD <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="edit-customer-cmnd" name="customer_cmnd" pattern="[0-9]{12}" maxlength="12" placeholder="123456789012" required>
                                                    <small class="form-text text-muted">Nhập 12 số CCCD</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-customer-phone" class="form-label">Điện thoại <span class="text-danger">*</span></label>
                                                    <input type="tel" class="form-control" id="edit-customer-phone" name="customer_phone" pattern="[0-9]{10,11}" placeholder="0123456789" required>
                                                    <small class="form-text text-muted">Nhập 10-11 số điện thoại</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-customer-email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="edit-customer-email" name="customer_email" placeholder="example@email.com">
                                                    <small class="form-text text-muted">Nhập địa chỉ email hợp lệ</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-emergency-name" class="form-label">Tên người liên hệ khẩn cấp</label>
                                                    <input type="text" class="form-control" id="edit-emergency-name" name="emergency_contact_name">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-emergency-phone" class="form-label">Điện thoại liên hệ khẩn cấp</label>
                                                    <input type="tel" class="form-control" id="edit-emergency-phone" name="emergency_contact_phone" pattern="[0-9]{10,11}" placeholder="0123456789">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="edit-emergency-relationship" class="form-label">Mối quan hệ</label>
                                                    <input type="text" class="form-control" id="edit-emergency-relationship" name="emergency_contact_relationship">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-customer-id-issued-place" class="form-label">Nơi cấp CCCD</label>
                                                    <select class="form-select" id="edit-customer-id-issued-place" name="customer_id_issued_place">
                                                        <option value="">Chọn nơi cấp</option>
                                                        <option value="Bộ Công an">Bộ Công an</option>
                                                        <option value="Cục CSQLHV về TTXH">Cục CSQLHV về TTXH</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-emergency-address" class="form-label">Địa chỉ liên hệ khẩn cấp</label>
                                                    <textarea class="form-control" id="edit-emergency-address" name="emergency_contact_address" rows="2"></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-emergency-note" class="form-label">Ghi chú liên hệ khẩn cấp</label>
                                                    <textarea class="form-control" id="edit-emergency-note" name="emergency_contact_note" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin khoản vay -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-money-bill me-2"></i>Thông tin khoản vay</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="edit-loan-amount" class="form-label">Số tiền vay <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control currency-input" id="edit-loan-amount" name="loan_amount" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-loan-term" class="form-label">Thời hạn vay (tháng) <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="edit-loan-term" name="loan_term" required>
                                                        <option value="">Chọn thời hạn</option>
                                                        <option value="3">3 tháng</option>
                                                        <option value="6">6 tháng</option>
                                                        <option value="12">12 tháng</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-loan-purpose" class="form-label">Mục đích vay <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="edit-loan-purpose" name="loan_purpose" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="edit-interest-rate-id" class="form-label">Lãi suất <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="edit-interest-rate-id" name="interest_rate_id" required>
                                                        <option value="">Chọn lãi suất</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-approved-amount" class="form-label">Số tiền duyệt</label>
                                                    <input type="text" class="form-control currency-input" id="edit-approved-amount" name="approved_amount">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-status" class="form-label">Trạng thái</label>
                                                    <select class="form-select" id="edit-status" name="status">
                                                        <option value="pending">Chờ xử lý</option>
                                                        <option value="approved">Đã duyệt</option>
                                                        <option value="rejected">Từ chối</option>
                                                        <option value="cancelled">Đã hủy</option>
                                                    </select>
                                                    <small class="form-text text-muted">Mặc định: Chờ xử lý</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin tài sản thế chấp -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-car me-2"></i>Tài sản thế chấp</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="edit-asset-info" class="form-label">Tài sản thế chấp <span class="text-danger">*</span></label>
                                                    <div class="form-control-plaintext" id="edit-asset-info" style="background-color: #f8f9fa; padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.375rem;">
                                                        <strong id="edit-asset-name-display"></strong><br>
                                                        <small class="text-muted">Biển số: <span id="edit-asset-license-display"></span> | Giá trị: <span id="edit-asset-value-display"></span></small>
                                                    </div>
                                                    <input type="hidden" id="edit-asset-id" name="asset_id">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-asset-quantity" class="form-label">Số lượng</label>
                                                    <input type="number" class="form-control" id="edit-asset-quantity" name="asset_quantity" min="1" value="1">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-asset-value" class="form-label">Giá trị tài sản</label>
                                                    <input type="text" class="form-control currency-input" id="edit-asset-value" name="asset_value">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="edit-asset-condition" class="form-label">Tình trạng tài sản</label>
                                                    <select class="form-select" id="edit-asset-condition" name="asset_condition">
                                                        <option value="new">Mới</option>
                                                        <option value="good">Tốt</option>
                                                        <option value="fair">Khá</option>
                                                        <option value="poor">Kém</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit-asset-notes" class="form-label">Ghi chú tài sản</label>
                                                    <textarea class="form-control" id="edit-asset-notes" name="asset_notes" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin bảo hiểm -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Thông tin bảo hiểm</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="edit-health-insurance" class="form-label">Bảo hiểm y tế</label>
                                                    <input type="text" class="form-control" id="edit-health-insurance" name="health_insurance">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="edit-life-insurance" class="form-label">Bảo hiểm nhân thọ</label>
                                                    <input type="text" class="form-control" id="edit-life-insurance" name="life_insurance">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="edit-vehicle-insurance" class="form-label">Bảo hiểm xe cộ</label>
                                                    <input type="text" class="form-control" id="edit-vehicle-insurance" name="vehicle_insurance">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Ghi chú</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="edit-notes" class="form-label">Ghi chú đơn vay</label>
                                            <textarea class="form-control" id="edit-notes" name="notes" rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" id="saveEditBtn">
                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đơn vay này không?</p>
                <p class="text-muted">Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Xóa
                </button>
            </div>
        </div>
    </div>
</div>