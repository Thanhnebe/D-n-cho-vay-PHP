    </div> <!-- End main-content -->
    </div> <!-- End d-flex -->

    <!-- Notification Area -->
    <div id="notificationArea" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Lazy load non-critical JavaScript -->
    <script>
        // Performance optimization - Load resources only when needed
        document.addEventListener('DOMContentLoaded', function() {
            // Load non-critical resources asynchronously
            const loadNonCriticalResources = async function() {
                try {
                    // Load Bootstrap Datepicker
                    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js');
                    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.vi.min.js');

                    // Load DataTables
                    await loadScript('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js');
                    await loadScript('https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js');

                    // Load Select2
                    await loadScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');

                    // Load Chart.js
                    await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js');

                    // Load SheetJS for Excel export
                    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js');

                    // Initialize components after loading
                    initializeComponents();

                } catch (error) {
                    console.error('Error loading non-critical resources:', error);
                }
            };

            // Load resources in background
            setTimeout(loadNonCriticalResources, 1000);

            // Initialize critical components immediately
            initializeCriticalComponents();
        });

        // Global notification function
        function showNotification(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

            const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

            $('#notificationArea').html(notification);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut('slow');
            }, 5000);
        }

        // Initialize critical components (load immediately)
        function initializeCriticalComponents() {
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Initialize popovers
            $('[data-bs-toggle="popover"]').popover();
        }

        // Initialize all components (load after resources)
        function initializeComponents() {
            // Initialize DataTables if present
            if ($.fn.DataTable) {
                $('.datatable').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [0, 'desc']
                    ]
                });
            }

            // Initialize Select2 if present
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Chọn...',
                    allowClear: true
                });
            }

            // Initialize datepickers
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                language: 'vi',
                autoclose: true,
                todayHighlight: true
            });

            // Initialize currency formatters
            if (typeof initializeCurrencyFormatters === 'function') {
                initializeCurrencyFormatters();
            }
        }

        // Confirm delete function
        function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
            return confirm(message);
        }

        // Format currency function
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Format date function
        function formatDate(date) {
            return new Date(date).toLocaleDateString('vi-VN');
        }

        // Format datetime function
        function formatDateTime(dateTime) {
            return new Date(dateTime).toLocaleString('vi-VN');
        }

        // Show loading spinner
        function showLoading() {
            $('#loadingSpinner').show();
        }

        // Hide loading spinner
        function hideLoading() {
            $('#loadingSpinner').hide();
        }

        // AJAX helper function with error handling
        function ajaxRequest(url, data, successCallback, errorCallback) {
            showLoading();

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (successCallback) {
                        successCallback(response);
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    console.error('AJAX Error:', error);
                    showNotification('Có lỗi xảy ra: ' + error, 'error');
                    if (errorCallback) {
                        errorCallback(xhr, status, error);
                    }
                }
            });
        }

        // Utility function to load script
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Performance monitoring
        window.addEventListener('load', function() {
            // Log page load time
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            console.log('Page load time:', loadTime + 'ms');

            // Show notification if page loads slowly
            if (loadTime > 3000) {
                showNotification('Trang web đang tải chậm, vui lòng kiểm tra kết nối mạng', 'warning');
            }
        });

        // Error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            showNotification('Có lỗi JavaScript xảy ra', 'error');
        });

        // Unhandled promise rejection
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled Promise Rejection:', e.reason);
            showNotification('Có lỗi xảy ra trong quá trình xử lý', 'error');
        });
    </script>

    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
    </body>

    </html>