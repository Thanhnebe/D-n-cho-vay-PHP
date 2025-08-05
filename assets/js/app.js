// VayCamCo - Main JavaScript

$(document).ready(function () {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Mobile sidebar toggle
    $('.navbar-toggler').on('click', function () {
        $('.sidebar').toggleClass('show');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function (e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar, .navbar-toggler').length) {
                $('.sidebar').removeClass('show');
            }
        }
    });

    // Currency input formatting
    $('.currency-input').on('input', function () {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
            $(this).val(value);
        }
    });

    // Date input formatting
    $('.date-input').on('input', function () {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        $(this).val(value);
    });

    // Auto-hide alerts
    $('.alert').each(function () {
        let alert = $(this);
        setTimeout(function () {
            alert.fadeOut();
        }, 5000);
    });

    // Confirm delete buttons
    $('.btn-delete').on('click', function (e) {
        if (!confirm('Bạn có chắc chắn muốn xóa?')) {
            e.preventDefault();
        }
    });

    // Form validation
    $('form').on('submit', function () {
        let isValid = true;
        $(this).find('[required]').each(function () {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return isValid;
    });

    // Remove invalid class on input
    $('input, select, textarea').on('input change', function () {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });

    // DataTable initialization
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
            },
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }

    // Select2 initialization
    if ($.fn.select2) {
        $('.select2').select2({
            language: 'vi',
            placeholder: 'Chọn một tùy chọn...'
        });
    }

    // Chart.js initialization
    if (typeof Chart !== 'undefined') {
        // Sample chart configuration
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [{
                        label: 'Doanh thu',
                        data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 12],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
});

// Utility functions
window.VayCamCo = {
    // Show toast notification
    showToast: function (message, type = 'info') {
        const toast = document.getElementById('liveToast');
        const toastMessage = document.getElementById('toastMessage');

        if (toast && toastMessage) {
            toastMessage.textContent = message;
            toast.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');

            switch (type) {
                case 'success':
                    toast.classList.add('bg-success', 'text-white');
                    break;
                case 'error':
                    toast.classList.add('bg-danger', 'text-white');
                    break;
                case 'warning':
                    toast.classList.add('bg-warning');
                    break;
                default:
                    toast.classList.add('bg-info', 'text-white');
            }

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    },

    // Format currency
    formatCurrency: function (amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    },

    // Format date
    formatDate: function (date) {
        return new Date(date).toLocaleDateString('vi-VN');
    },

    // Confirm delete
    confirmDelete: function (message = 'Bạn có chắc chắn muốn xóa?') {
        return confirm(message);
    },

    // AJAX request helper
    ajax: function (url, data, method = 'POST') {
        return $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json'
        });
    },

    // Load modal content
    loadModal: function (url, modalId) {
        $.get(url, function (data) {
            $(modalId + ' .modal-body').html(data);
            $(modalId).modal('show');
        });
    },

    // Print element
    printElement: function (elementId) {
        const printContents = document.getElementById(elementId).innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;

        // Reinitialize scripts
        location.reload();
    }
};

// Global error handler
$(document).ajaxError(function (event, xhr, settings, error) {
    console.error('AJAX Error:', error);
    VayCamCo.showToast('Có lỗi xảy ra khi tải dữ liệu', 'error');
});

// Global success handler
$(document).ajaxSuccess(function (event, xhr, settings) {
    if (xhr.responseJSON && xhr.responseJSON.message) {
        VayCamCo.showToast(xhr.responseJSON.message, xhr.responseJSON.type || 'success');
    }
}); 