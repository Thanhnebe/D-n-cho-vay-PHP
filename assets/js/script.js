/**
 * VayCamCo Smart Management - Optimized JavaScript
 * Performance optimized with lazy loading and error handling
 */

// Global variables
let isResourcesLoaded = false;
let pendingOperations = [];

// Initialize currency formatters
function initializeCurrencyFormatters() {
    // Currency input formatter
    document.querySelectorAll('.currency-input').forEach(input => {
        input.addEventListener('input', function () {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('vi-VN');
                this.value = value;
            }
        });

        input.addEventListener('blur', function () {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = parseInt(value).toLocaleString('vi-VN') + ' ₫';
            }
        });

        input.addEventListener('focus', function () {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = parseInt(value).toLocaleString('vi-VN');
            }
        });
    });
}

// Form validation helper
function validateForm(formId) {
    let isValid = true;
    const form = document.querySelector(formId);

    if (!form) return false;

    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });

    // Validate required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });

    // Validate email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });

    // Validate phone fields
    form.querySelectorAll('input[name*="phone"]').forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });

    return isValid;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone validation (Vietnamese format)
function isValidPhone(phone) {
    const phoneRegex = /^(0|\+84)(3[2-9]|5[689]|7[06-9]|8[1-689]|9[0-46-9])[0-9]{7}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('vi-VN');
}

// Format datetime
function formatDateTime(dateTime) {
    return new Date(dateTime).toLocaleString('vi-VN');
}

// Show loading spinner
function showLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.style.display = 'flex';
    }
}

// Hide loading spinner
function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.style.display = 'none';
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notificationArea = document.getElementById('notificationArea');
    if (!notificationArea) return;

    const alertClass = type === 'success' ? 'alert-success' :
        type === 'error' ? 'alert-danger' :
            type === 'warning' ? 'alert-warning' : 'alert-info';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    notificationArea.innerHTML = '';
    notificationArea.appendChild(notification);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// AJAX helper function
function ajaxRequest(url, data, successCallback, errorCallback) {
    showLoading();

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            if (successCallback) {
                successCallback(data);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('AJAX Error:', error);
            showNotification('Có lỗi xảy ra: ' + error.message, 'error');
            if (errorCallback) {
                errorCallback(error);
            }
        });
}

// Confirm delete
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return confirm(message);
}

// Export to Excel
function exportToExcel(tableId, filename = 'export') {
    if (typeof XLSX === 'undefined') {
        showNotification('Thư viện Excel chưa được tải', 'error');
        return;
    }

    const table = document.getElementById(tableId);
    if (!table) {
        showNotification('Không tìm thấy bảng để xuất', 'error');
        return;
    }

    try {
        const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
        XLSX.writeFile(wb, `${filename}_${new Date().toISOString().slice(0, 10)}.xlsx`);
        showNotification('Xuất Excel thành công', 'success');
    } catch (error) {
        console.error('Export error:', error);
        showNotification('Có lỗi khi xuất Excel', 'error');
    }
}

// Print page
function printPage() {
    window.print();
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Đã sao chép vào clipboard', 'success');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy to clipboard
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        showNotification('Đã sao chép vào clipboard', 'success');
    } catch (err) {
        showNotification('Không thể sao chép', 'error');
    }
    document.body.removeChild(textArea);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function () {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Lazy load images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Initialize tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize popovers
function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Performance monitoring
function monitorPerformance() {
    // Monitor page load time
    window.addEventListener('load', () => {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log('Page load time:', loadTime + 'ms');

        if (loadTime > 3000) {
            showNotification('Trang web đang tải chậm, vui lòng kiểm tra kết nối mạng', 'warning');
        }
    });

    // Monitor memory usage
    if ('memory' in performance) {
        setInterval(() => {
            const memory = performance.memory;
            const usedMB = Math.round(memory.usedJSHeapSize / 1048576);
            const totalMB = Math.round(memory.totalJSHeapSize / 1048576);

            if (usedMB > totalMB * 0.8) {
                console.warn('High memory usage:', usedMB + 'MB / ' + totalMB + 'MB');
            }
        }, 30000);
    }
}

// Error handling
function setupErrorHandling() {
    // Global error handler
    window.addEventListener('error', (e) => {
        console.error('JavaScript Error:', e.error);
        showNotification('Có lỗi JavaScript xảy ra', 'error');
    });

    // Unhandled promise rejection
    window.addEventListener('unhandledrejection', (e) => {
        console.error('Unhandled Promise Rejection:', e.reason);
        showNotification('Có lỗi xảy ra trong quá trình xử lý', 'error');
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Initialize critical components
    initializeCurrencyFormatters();
    initializeTooltips();
    initializePopovers();
    lazyLoadImages();
    monitorPerformance();
    setupErrorHandling();

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);

    // Add smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });


    // Add responsive sidebar toggle
    const sidebarToggle = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
});

// Export functions to global scope
window.VayCamCo = {
    formatCurrency,
    formatDate,
    formatDateTime,
    showNotification,
    ajaxRequest,
    confirmDelete,
    exportToExcel,
    printPage,
    copyToClipboard,
    validateForm,
    isValidEmail,
    isValidPhone
}; 