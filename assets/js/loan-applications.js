/**
 * Loan Applications Management System
 * JavaScript Module for Loan Applications Management
 * 
 * @author VayCamCo System
 * @version 1.0.0
 * @license MIT
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        API_ENDPOINTS: {
            GET_APPLICATION: 'pages/admin/api/get-loan-application.php',
            UPDATE_APPLICATION: 'pages/admin/api/update-loan-application.php',
            DELETE_APPLICATION: 'pages/admin/api/delete-loan-application.php',
            APPROVE_APPLICATION: 'pages/admin/api/approve-loan-application.php',
            REJECT_APPLICATION: 'pages/admin/api/reject-loan-application.php'
        },
        CURRENCY_LOCALE: 'vi-VN',
        CURRENCY_CODE: 'VND',
        DATE_LOCALE: 'vi-VN',
        MODAL_ANIMATION_DURATION: 300,
        LOADING_TIMEOUT: 10000
    };

    // Utility Functions
    const Utils = {
        /**
         * Format currency to Vietnamese format
         * @param {number} amount - Amount to format
         * @returns {string} Formatted currency string
         */
        formatCurrency: function(amount) {
            if (!amount && amount !== 0) return '0 ₫';
            
            return new Intl.NumberFormat(CONFIG.CURRENCY_LOCALE, {
                style: 'currency',
                currency: CONFIG.CURRENCY_CODE,
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        },

        /**
         * Format date to Vietnamese format
         * @param {string|Date} date - Date to format
         * @returns {string} Formatted date string
         */
        formatDate: function(date) {
            if (!date) return '';
            
            return new Date(date).toLocaleDateString(CONFIG.DATE_LOCALE, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },

        /**
         * Format datetime to Vietnamese format
         * @param {string|Date} datetime - Datetime to format
         * @returns {string} Formatted datetime string
         */
        formatDateTime: function(datetime) {
            if (!datetime) return '';
            
            return new Date(datetime).toLocaleString(CONFIG.DATE_LOCALE, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        /**
         * Generate application code
         * @returns {string} Generated application code
         */
        generateApplicationCode: function() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');

            return `DV${year}${month}${day}${hours}${minutes}${seconds}${random}`;
        },

        /**
         * Show notification message
         * @param {string} message - Message to show
         * @param {string} type - Type of notification (success, error, warning, info)
         */
        showNotification: function(message, type = 'info') {
            const alertClass = `alert-${type}`;
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Remove existing alerts
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
            
            // Add new alert
            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
            }
        },

        /**
         * Show loading spinner
         * @param {string} elementId - ID of loading element
         */
        showLoading: function(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.style.display = 'block';
            }
        },

        /**
         * Hide loading spinner
         * @param {string} elementId - ID of loading element
         */
        hideLoading: function(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.style.display = 'none';
            }
        },

        /**
         * Validate form data
         * @param {Object} data - Form data to validate
         * @returns {Object} Validation result
         */
        validateFormData: function(data) {
            const errors = [];
            
            // Required fields validation
            const requiredFields = [
                'customer_name', 'customer_cmnd', 'customer_phone_main',
                'loan_amount', 'loan_term_months', 'monthly_rate'
            ];
            
            requiredFields.forEach(field => {
                if (!data[field] || data[field].toString().trim() === '') {
                    errors.push(`Trường ${field} là bắt buộc`);
                }
            });
            
            // Numeric validation
            if (data.loan_amount && isNaN(parseFloat(data.loan_amount))) {
                errors.push('Số tiền vay phải là số');
            }
            
            if (data.loan_term_months && (isNaN(parseInt(data.loan_term_months)) || parseInt(data.loan_term_months) < 1)) {
                errors.push('Thời hạn vay phải là số nguyên dương');
            }
            
            return {
                isValid: errors.length === 0,
                errors: errors
            };
        }
    };

    // API Service
    const ApiService = {
        /**
         * Make API request
         * @param {string} url - API endpoint
         * @param {Object} options - Request options
         * @returns {Promise} API response
         */
        request: async function(url, options = {}) {
            try {
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return await response.json();
            } catch (error) {
                console.error('API request failed:', error);
                throw error;
            }
        },

        /**
         * Get application details
         * @param {number} applicationId - Application ID
         * @returns {Promise} Application data
         */
        getApplication: async function(applicationId) {
            return await this.request(`${CONFIG.API_ENDPOINTS.GET_APPLICATION}?id=${applicationId}`);
        },

        /**
         * Update application
         * @param {Object} data - Application data
         * @returns {Promise} Update result
         */
        updateApplication: async function(data) {
            return await this.request(CONFIG.API_ENDPOINTS.UPDATE_APPLICATION, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },

        /**
         * Delete application
         * @param {number} applicationId - Application ID
         * @returns {Promise} Delete result
         */
        deleteApplication: async function(applicationId) {
            return await this.request(CONFIG.API_ENDPOINTS.DELETE_APPLICATION, {
                method: 'POST',
                body: JSON.stringify({ application_id: applicationId })
            });
        },

        /**
         * Approve application
         * @param {Object} data - Approval data
         * @returns {Promise} Approval result
         */
        approveApplication: async function(data) {
            return await this.request(CONFIG.API_ENDPOINTS.APPROVE_APPLICATION, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },

        /**
         * Reject application
         * @param {Object} data - Rejection data
         * @returns {Promise} Rejection result
         */
        rejectApplication: async function(data) {
            return await this.request(CONFIG.API_ENDPOINTS.REJECT_APPLICATION, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }
    };

    // Modal Management
    const ModalManager = {
        /**
         * Show modal
         * @param {string} modalId - Modal ID
         * @returns {Object} Bootstrap modal instance
         */
        show: function(modalId) {
            const modalElement = document.getElementById(modalId);
            if (!modalElement) {
                throw new Error(`Modal with ID '${modalId}' not found`);
            }

            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            return modal;
        },

        /**
         * Hide modal
         * @param {string} modalId - Modal ID
         */
        hide: function(modalId) {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }
        },

        /**
         * Reset form in modal
         * @param {string} formId - Form ID
         */
        resetForm: function(formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.reset();
                
                // Clear all input values
                form.querySelectorAll('input, textarea, select').forEach(input => {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
            }
        }
    };

    // Form Management
    const FormManager = {
        /**
         * Initialize currency inputs
         */
        initCurrencyInputs: function() {
            document.querySelectorAll('.currency-input').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^\d]/g, '');
                    if (value) {
                        value = parseInt(value);
                        e.target.value = Utils.formatCurrency(value);
                    }
                });

                input.addEventListener('blur', function(e) {
                    let value = e.target.value.replace(/[^\d]/g, '');
                    if (value) {
                        value = parseInt(value);
                        e.target.value = Utils.formatCurrency(value);
                    }
                });
            });
        },

        /**
         * Initialize interest rate selection
         */
        initInterestRateSelection: function() {
            const interestRateSelect = document.getElementById('interest_rate_id');
            const monthlyRateInput = document.getElementById('monthly_rate');
            const dailyRateInput = document.getElementById('daily_rate');

            if (interestRateSelect && monthlyRateInput && dailyRateInput) {
                interestRateSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.monthlyRate) {
                        monthlyRateInput.value = selectedOption.dataset.monthlyRate;
                        dailyRateInput.value = selectedOption.dataset.dailyRate || '';
                    }
                });
            }
        },

        /**
         * Initialize insurance calculation
         */
        initInsuranceCalculation: function() {
            const loanAmountInput = document.getElementById('loan_amount');
            const monthlyRateInput = document.getElementById('monthly_rate');
            const loanTermInput = document.getElementById('loan_term_months');
            const insuranceCheckboxes = document.querySelectorAll('.insurance-checkbox');

            const calculateInsurance = () => {
                const loanAmount = parseFloat(loanAmountInput.value.replace(/[^\d]/g, '')) || 0;
                const monthlyRate = parseFloat(monthlyRateInput.value) || 0;
                const loanTerm = parseInt(loanTermInput.value) || 0;

                if (loanAmount > 0 && monthlyRate > 0 && loanTerm > 0) {
                    const disbursementAmount = loanAmount;
                    const interestInTerm = (loanAmount * monthlyRate * loanTerm) / 100;
                    const totalForInsurance = disbursementAmount + interestInTerm;

                    // Calculate insurance fees
                    const healthInsuranceFee = totalForInsurance * 0.0125; // 1.25%
                    const hospitalizationInsuranceFee = totalForInsurance * 0.02; // 2%
                    const vehicleInsuranceFee = totalForInsurance * 0.0075; // 0.75%

                    const totalInsuranceFee = healthInsuranceFee + hospitalizationInsuranceFee + vehicleInsuranceFee;

                    // Update display
                    document.getElementById('disbursement_amount').value = Utils.formatCurrency(disbursementAmount);
                    document.getElementById('interest_in_term').value = Utils.formatCurrency(interestInTerm);
                    document.getElementById('total_for_insurance').value = Utils.formatCurrency(totalForInsurance);
                    document.getElementById('total_insurance_fee').value = Utils.formatCurrency(totalInsuranceFee);
                    document.getElementById('health_insurance_fee').value = Utils.formatCurrency(healthInsuranceFee);
                    document.getElementById('hospitalization_insurance_fee').value = Utils.formatCurrency(hospitalizationInsuranceFee);
                    document.getElementById('vehicle_insurance_fee').value = Utils.formatCurrency(vehicleInsuranceFee);
                }
            };

            // Add event listeners
            [loanAmountInput, monthlyRateInput, loanTermInput].forEach(input => {
                if (input) {
                    input.addEventListener('input', calculateInsurance);
                }
            });

            insuranceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', calculateInsurance);
            });
        }
    };

    // Application Management
    const ApplicationManager = {
        /**
         * Show add application modal
         */
        showAddModal: function() {
            try {
                // Reset form
                ModalManager.resetForm('addApplicationForm');

                // Generate application code
                const applicationCode = Utils.generateApplicationCode();
                const applicationCodeInput = document.getElementById('application_code');
                if (applicationCodeInput) {
                    applicationCodeInput.value = applicationCode;
                }

                // Show modal
                ModalManager.show('addApplicationModal');

                // Initialize form features
                FormManager.initCurrencyInputs();
                FormManager.initInterestRateSelection();
                FormManager.initInsuranceCalculation();

            } catch (error) {
                console.error('Error showing add modal:', error);
                Utils.showNotification('Có lỗi xảy ra khi mở modal: ' + error.message, 'error');
            }
        },

        /**
         * Show application detail modal
         * @param {number} applicationId - Application ID
         */
        showDetailModal: async function(applicationId) {
            try {
                // Show modal immediately
                const modal = ModalManager.show('applicationDetailModal');

                // Show loading state
                Utils.showLoading('detail-loading-state');
                Utils.hideLoading('detail-modal-content');

                // Fetch application data
                const data = await ApiService.getApplication(applicationId);

                // Hide loading state
                Utils.hideLoading('detail-loading-state');
                Utils.showLoading('detail-modal-content');

                if (data.success) {
                    this.populateDetailModal(data.application);
                } else {
                    Utils.showNotification(data.message || 'Không thể tải thông tin đơn vay', 'error');
                    modal.hide();
                }

            } catch (error) {
                console.error('Error showing detail modal:', error);
                Utils.showNotification('Có lỗi xảy ra khi tải thông tin đơn vay: ' + error.message, 'error');
                ModalManager.hide('applicationDetailModal');
            }
        },

        /**
         * Populate detail modal with application data
         * @param {Object} application - Application data
         */
        populateDetailModal: function(application) {
            // Basic information
            document.getElementById('detail-application-code').textContent = application.application_code || '';
            document.getElementById('detail-customer-name').textContent = application.customer_name || '';
            document.getElementById('detail-customer-cmnd').textContent = application.customer_cmnd || '';
            document.getElementById('detail-customer-birth').textContent = Utils.formatDate(application.customer_birth_date);
            document.getElementById('detail-customer-phone').textContent = application.customer_phone_main || '';
            document.getElementById('detail-customer-email').textContent = application.customer_email || '';
            document.getElementById('detail-customer-job').textContent = application.customer_job || '';
            document.getElementById('detail-customer-income').textContent = Utils.formatCurrency(application.customer_income);
            document.getElementById('detail-customer-company').textContent = application.customer_company || '';
            document.getElementById('detail-customer-address').textContent = application.customer_address || '';

            // Loan information
            document.getElementById('detail-loan-amount').textContent = Utils.formatCurrency(application.loan_amount);
            document.getElementById('detail-approved-amount').textContent = Utils.formatCurrency(application.approved_amount);
            document.getElementById('detail-loan-term').textContent = `${application.loan_term_months} tháng`;
            document.getElementById('detail-loan-purpose').textContent = application.loan_purpose || '';
            document.getElementById('detail-interest-rate').textContent = `${application.monthly_rate}%/tháng`;
            document.getElementById('detail-status').textContent = this.getStatusText(application.status);
            document.getElementById('detail-created-date').textContent = Utils.formatDateTime(application.created_at);
            document.getElementById('detail-decision-date').textContent = Utils.formatDate(application.decision_date);

            // Asset information
            document.getElementById('detail-asset-name').textContent = application.asset_name || '';
            document.getElementById('detail-asset-quantity').textContent = application.asset_quantity || '';
            document.getElementById('detail-asset-license').textContent = application.asset_license_plate || '';
            document.getElementById('detail-asset-frame').textContent = application.asset_frame_number || '';
            document.getElementById('detail-asset-engine').textContent = application.asset_engine_number || '';
            document.getElementById('detail-asset-value').textContent = Utils.formatCurrency(application.asset_value);
            document.getElementById('detail-asset-condition').textContent = application.asset_condition || '';
            document.getElementById('detail-asset-brand').textContent = application.asset_brand || '';
            document.getElementById('detail-asset-model').textContent = application.asset_model || '';
            document.getElementById('detail-asset-year').textContent = application.asset_year || '';

            // Emergency contact information
            document.getElementById('detail-emergency-name').textContent = application.emergency_contact_name || '';
            document.getElementById('detail-emergency-phone').textContent = application.emergency_contact_phone || '';
            document.getElementById('detail-emergency-relationship').textContent = application.emergency_contact_relationship || '';
            document.getElementById('detail-emergency-address').textContent = application.emergency_contact_address || '';
            document.getElementById('detail-emergency-note').textContent = application.emergency_contact_note || '';

            // Insurance information
            document.getElementById('detail-health-insurance').textContent = application.has_health_insurance ? 'Có' : 'Không';
            document.getElementById('detail-life-insurance').textContent = application.has_life_insurance ? 'Có' : 'Không';
            document.getElementById('detail-vehicle-insurance').textContent = application.has_vehicle_insurance ? 'Có' : 'Không';

            // Decision information (if available)
            if (application.final_decision) {
                document.getElementById('detail-final-decision').textContent = this.getDecisionText(application.final_decision);
                document.getElementById('detail-decision-notes').textContent = application.decision_notes || '';
                document.getElementById('detail-decision-section').style.display = 'block';
            } else {
                document.getElementById('detail-decision-section').style.display = 'none';
            }
        },

        /**
         * Show edit application modal
         * @param {number} applicationId - Application ID
         */
        showEditModal: async function(applicationId) {
            try {
                // Show modal immediately
                const modal = ModalManager.show('editApplicationModal');

                // Show loading state
                Utils.showLoading('edit-loading-state');
                Utils.hideLoading('edit-modal-content');

                // Fetch application data
                const data = await ApiService.getApplication(applicationId);

                // Hide loading state
                Utils.hideLoading('edit-loading-state');
                Utils.showLoading('edit-modal-content');

                if (data.success) {
                    this.populateEditModal(data.application);
                } else {
                    Utils.showNotification(data.message || 'Không thể tải thông tin đơn vay', 'error');
                    modal.hide();
                }

            } catch (error) {
                console.error('Error showing edit modal:', error);
                Utils.showNotification('Có lỗi xảy ra khi tải thông tin đơn vay: ' + error.message, 'error');
                ModalManager.hide('editApplicationModal');
            }
        },

        /**
         * Populate edit modal with application data
         * @param {Object} application - Application data
         */
        populateEditModal: function(application) {
            // Set application ID
            document.getElementById('edit-application-id').value = application.id;
            document.getElementById('edit-application-code').textContent = application.application_code;

            // Customer information
            document.getElementById('edit-customer-name').value = application.customer_name || '';
            document.getElementById('edit-customer-cmnd').value = application.customer_cmnd || '';
            document.getElementById('edit-customer-birth').value = application.customer_birth_date || '';
            document.getElementById('edit-customer-phone').value = application.customer_phone_main || '';
            document.getElementById('edit-customer-email').value = application.customer_email || '';
            document.getElementById('edit-customer-job').value = application.customer_job || '';
            document.getElementById('edit-customer-income').value = Utils.formatCurrency(application.customer_income);
            document.getElementById('edit-customer-company').value = application.customer_company || '';
            document.getElementById('edit-customer-address').value = application.customer_address || '';

            // Loan information
            document.getElementById('edit-loan-amount').value = Utils.formatCurrency(application.loan_amount);
            document.getElementById('edit-loan-purpose').value = application.loan_purpose || '';
            document.getElementById('edit-loan-term').value = application.loan_term_months || '';
            document.getElementById('edit-interest-rate').value = application.monthly_rate || '';
            document.getElementById('edit-status').value = application.status || '';
            document.getElementById('edit-approved-amount').value = Utils.formatCurrency(application.approved_amount);

            // Asset information
            document.getElementById('edit-asset-name').value = application.asset_name || '';
            document.getElementById('edit-asset-quantity').value = application.asset_quantity || '';
            document.getElementById('edit-asset-license').value = application.asset_license_plate || '';
            document.getElementById('edit-asset-frame').value = application.asset_frame_number || '';
            document.getElementById('edit-asset-engine').value = application.asset_engine_number || '';
            document.getElementById('edit-asset-value').value = Utils.formatCurrency(application.asset_value);
            document.getElementById('edit-asset-condition').value = application.asset_condition || '';
            document.getElementById('edit-asset-brand').value = application.asset_brand || '';
            document.getElementById('edit-asset-model').value = application.asset_model || '';
            document.getElementById('edit-asset-year').value = application.asset_year || '';

            // Emergency contact information
            document.getElementById('edit-emergency-name').value = application.emergency_contact_name || '';
            document.getElementById('edit-emergency-phone').value = application.emergency_contact_phone || '';
            document.getElementById('edit-emergency-relationship').value = application.emergency_contact_relationship || '';
            document.getElementById('edit-emergency-address').value = application.emergency_contact_address || '';
            document.getElementById('edit-emergency-note').value = application.emergency_contact_note || '';

            // Insurance information
            document.getElementById('edit-health-insurance').checked = application.has_health_insurance == 1;
            document.getElementById('edit-life-insurance').checked = application.has_life_insurance == 1;
            document.getElementById('edit-vehicle-insurance').checked = application.has_vehicle_insurance == 1;

            // Decision information
            document.getElementById('edit-final-decision').value = application.final_decision || '';
            document.getElementById('edit-decision-date').value = application.decision_date || '';
            document.getElementById('edit-decision-notes').value = application.decision_notes || '';

            // Initialize form features
            FormManager.initCurrencyInputs();
        },

        /**
         * Show approval modal
         * @param {number} applicationId - Application ID
         */
        showApprovalModal: async function(applicationId) {
            try {
                // Show modal immediately
                const modal = ModalManager.show('approvalModal');

                // Show loading state
                Utils.showLoading('loading-state');
                Utils.hideLoading('modal-content');

                // Fetch application data
                const data = await ApiService.getApplication(applicationId);

                // Hide loading state
                Utils.hideLoading('loading-state');
                Utils.showLoading('modal-content');

                if (data.success) {
                    this.populateApprovalModal(data.application);
                } else {
                    Utils.showNotification(data.message || 'Không thể tải thông tin đơn vay', 'error');
                    modal.hide();
                }

            } catch (error) {
                console.error('Error showing approval modal:', error);
                Utils.showNotification('Có lỗi xảy ra khi tải thông tin đơn vay: ' + error.message, 'error');
                ModalManager.hide('approvalModal');
            }
        },

        /**
         * Populate approval modal with application data
         * @param {Object} application - Application data
         */
        populateApprovalModal: function(application) {
            // Set application ID
            document.getElementById('modal-application-id').value = application.id;
            document.getElementById('modal-application-code').textContent = application.application_code;

            // Customer information
            document.getElementById('modal-customer-name').textContent = application.customer_name || '';
            document.getElementById('modal-customer-cmnd').textContent = application.customer_cmnd || '';
            document.getElementById('modal-customer-phone').textContent = application.customer_phone_main || '';
            document.getElementById('modal-customer-email').textContent = application.customer_email || '';
            document.getElementById('modal-customer-job').textContent = application.customer_job || '';
            document.getElementById('modal-customer-income').textContent = Utils.formatCurrency(application.customer_income);
            document.getElementById('modal-customer-address').textContent = application.customer_address || '';

            // Loan information
            document.getElementById('modal-loan-amount').textContent = Utils.formatCurrency(application.loan_amount);
            document.getElementById('modal-loan-term').textContent = `${application.loan_term_months} tháng`;
            document.getElementById('modal-loan-purpose').textContent = application.loan_purpose || '';
            document.getElementById('modal-submission-date').textContent = Utils.formatDate(application.created_at);
            document.getElementById('modal-status').textContent = this.getStatusText(application.status);
            document.getElementById('modal-interest-rate').textContent = `${application.monthly_rate}%/tháng`;
            document.getElementById('modal-asset-name').textContent = application.asset_name || '';

            // Set default approved amount
            document.getElementById('modal-approved-amount').value = Utils.formatCurrency(application.loan_amount);

            // Initialize form features
            FormManager.initCurrencyInputs();
        },

        /**
         * Show reject modal
         * @param {number} applicationId - Application ID
         */
        showRejectModal: async function(applicationId) {
            try {
                // Show modal immediately
                const modal = ModalManager.show('rejectApplicationModal');

                // Show loading state
                Utils.showLoading('reject-loading-state');
                Utils.hideLoading('reject-modal-content');

                // Fetch application data
                const data = await ApiService.getApplication(applicationId);

                // Hide loading state
                Utils.hideLoading('reject-loading-state');
                Utils.showLoading('reject-modal-content');

                if (data.success) {
                    this.populateRejectModal(data.application);
                } else {
                    Utils.showNotification(data.message || 'Không thể tải thông tin đơn vay', 'error');
                    modal.hide();
                }

            } catch (error) {
                console.error('Error showing reject modal:', error);
                Utils.showNotification('Có lỗi xảy ra khi tải thông tin đơn vay: ' + error.message, 'error');
                ModalManager.hide('rejectApplicationModal');
            }
        },

        /**
         * Populate reject modal with application data
         * @param {Object} application - Application data
         */
        populateRejectModal: function(application) {
            // Set application ID
            document.getElementById('reject-application-id').value = application.id;

            // Application information
            document.getElementById('reject-application-code').textContent = application.application_code || '';
            document.getElementById('reject-customer-name').textContent = application.customer_name || '';
            document.getElementById('reject-loan-amount').textContent = Utils.formatCurrency(application.loan_amount);
            document.getElementById('reject-loan-term').textContent = `${application.loan_term_months} tháng`;
            document.getElementById('reject-customer-phone').textContent = application.customer_phone_main || '';
            document.getElementById('reject-loan-purpose').textContent = application.loan_purpose || '';
            document.getElementById('reject-created-date').textContent = Utils.formatDate(application.created_at);
            document.getElementById('reject-current-status').textContent = this.getStatusText(application.status);
        },

        /**
         * Delete application
         * @param {number} applicationId - Application ID
         */
        deleteApplication: async function(applicationId) {
            if (!confirm('Bạn có chắc chắn muốn xóa đơn vay này?')) {
                return;
            }

            try {
                const result = await ApiService.deleteApplication(applicationId);
                
                if (result.success) {
                    Utils.showNotification('Xóa đơn vay thành công!', 'success');
                    // Reload page to refresh the list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Utils.showNotification(result.message || 'Có lỗi xảy ra khi xóa đơn vay', 'error');
                }
            } catch (error) {
                console.error('Error deleting application:', error);
                Utils.showNotification('Có lỗi xảy ra khi xóa đơn vay: ' + error.message, 'error');
            }
        },

        /**
         * Get status text
         * @param {string} status - Status code
         * @returns {string} Status text
         */
        getStatusText: function(status) {
            const statusMap = {
                'draft': 'Nháp',
                'pending': 'Chờ duyệt',
                'approved': 'Đã duyệt',
                'rejected': 'Đã từ chối',
                'disbursed': 'Đã giải ngân',
                'cancelled': 'Đã hủy'
            };
            return statusMap[status] || status;
        },

        /**
         * Get decision text
         * @param {string} decision - Decision code
         * @returns {string} Decision text
         */
        getDecisionText: function(decision) {
            const decisionMap = {
                'approved': 'Duyệt',
                'rejected': 'Từ chối',
                'pending': 'Chờ xem xét'
            };
            return decisionMap[decision] || decision;
        }
    };

    // Event Handlers
    const EventHandlers = {
        /**
         * Initialize all event handlers
         */
        init: function() {
            this.initAddApplicationButton();
            this.initDetailButtons();
            this.initEditButtons();
            this.initApprovalButtons();
            this.initRejectButtons();
            this.initDeleteButtons();
            this.initFormSubmissions();
            this.initModalEvents();
        },

        /**
         * Initialize add application button
         */
        initAddApplicationButton: function() {
            const addBtn = document.getElementById('addApplicationBtn');
            if (addBtn) {
                addBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    ApplicationManager.showAddModal();
                });
            }
        },

        /**
         * Initialize detail buttons
         */
        initDetailButtons: function() {
            document.querySelectorAll('.detail-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.dataset.id;
                    if (applicationId) {
                        ApplicationManager.showDetailModal(parseInt(applicationId));
                    }
                });
            });
        },

        /**
         * Initialize edit buttons
         */
        initEditButtons: function() {
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.dataset.id;
                    if (applicationId) {
                        ApplicationManager.showEditModal(parseInt(applicationId));
                    }
                });
            });
        },

        /**
         * Initialize approval buttons
         */
        initApprovalButtons: function() {
            document.querySelectorAll('.approval-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.dataset.id;
                    if (applicationId) {
                        ApplicationManager.showApprovalModal(parseInt(applicationId));
                    }
                });
            });
        },

        /**
         * Initialize reject buttons
         */
        initRejectButtons: function() {
            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.dataset.id;
                    if (applicationId) {
                        ApplicationManager.showRejectModal(parseInt(applicationId));
                    }
                });
            });
        },

        /**
         * Initialize delete buttons
         */
        initDeleteButtons: function() {
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const applicationId = this.dataset.id;
                    if (applicationId) {
                        ApplicationManager.deleteApplication(parseInt(applicationId));
                    }
                });
            });
        },

        /**
         * Initialize form submissions
         */
        initFormSubmissions: function() {
            // Add application form
            const addForm = document.getElementById('addApplicationForm');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    // Validate form data
                    const validation = Utils.validateFormData(data);
                    if (!validation.isValid) {
                        Utils.showNotification(validation.errors.join('\n'), 'error');
                        return;
                    }

                    // Submit form
                    this.submit();
                });
            }

            // Edit application form
            const editForm = document.getElementById('editApplicationForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    // Validate form data
                    const validation = Utils.validateFormData(data);
                    if (!validation.isValid) {
                        Utils.showNotification(validation.errors.join('\n'), 'error');
                        return;
                    }

                    // Submit form
                    this.submit();
                });
            }

            // Approval form
            const approvalForm = document.getElementById('approvalForm');
            if (approvalForm) {
                approvalForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    if (!data.approved_amount || parseFloat(data.approved_amount.replace(/[^\d]/g, '')) <= 0) {
                        Utils.showNotification('Vui lòng nhập số tiền được duyệt hợp lệ', 'error');
                        return;
                    }

                    if (!data.comments || data.comments.trim() === '') {
                        Utils.showNotification('Vui lòng nhập ghi chú đánh giá', 'error');
                        return;
                    }

                    // Submit form
                    this.submit();
                });
            }

            // Reject form
            const rejectForm = document.getElementById('rejectApplicationForm');
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());
                    
                    if (!data.reject_reason) {
                        Utils.showNotification('Vui lòng chọn lý do từ chối', 'error');
                        return;
                    }

                    if (!data.reject_comments || data.reject_comments.trim() === '') {
                        Utils.showNotification('Vui lòng nhập ghi chú chi tiết', 'error');
                        return;
                    }

                    // Submit form
                    this.submit();
                });
            }
        },

        /**
         * Initialize modal events
         */
        initModalEvents: function() {
            // Initialize currency inputs when modals are shown
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    FormManager.initCurrencyInputs();
                });
            });
        }
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize event handlers
        EventHandlers.init();

        // Initialize form features
        FormManager.initCurrencyInputs();
        FormManager.initInterestRateSelection();
        FormManager.initInsuranceCalculation();

        // Expose functions to global scope for backward compatibility
        window.showAddApplicationModal = ApplicationManager.showAddModal;
        window.showApplicationDetailModal = ApplicationManager.showDetailModal;
        window.showEditApplicationModal = ApplicationManager.showEditModal;
        window.showApprovalModal = ApplicationManager.showApprovalModal;
        window.showRejectModal = ApplicationManager.showRejectModal;
        window.deleteApplication = ApplicationManager.deleteApplication;

        console.log('Loan Applications Management System initialized successfully');
    });

    // Export for module systems (if needed)
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            ApplicationManager,
            ModalManager,
            FormManager,
            ApiService,
            Utils
        };
    }

})(); 