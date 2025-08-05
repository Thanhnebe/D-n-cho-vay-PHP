<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra Cứu - VIMARS Financial Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #28a745;
            --primary-dark: #1e7e34;
            --secondary-color: #6c757d;
            --light-green: #d4edda;
            --white: #ffffff;
            --dark: #343a40;
            --gray: #6c757d;
            --light-gray: #f8f9fa;
            --orange: #ff6b35;
            --blue: #007bff;
            --yellow: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--white);
        }

        /* Header Styles */
        .header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: bold;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }

        .logo-text p {
            font-size: 0.8rem;
            color: var(--gray);
            margin: 0;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        .nav-menu li a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .nav-menu li a:hover {
            color: var(--primary-color);
        }

        .nav-menu li a i {
            font-size: 1.1rem;
        }

        .cta-button {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .cta-button:hover {
            background: var(--primary-dark);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        /* Main Content */
        .main-content {
            margin-top: 100px;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Search Section */
        .search-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 4rem 0;
            text-align: center;
            border-radius: 0 0 30px 30px;
            margin-bottom: 3rem;
        }

        .search-hero h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .search-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .search-form {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            margin: 0 auto;
        }

        .search-input-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .search-input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }

        .search-button {
            background: var(--orange);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }

        .search-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            background: var(--white);
            font-size: 0.9rem;
        }

        /* Customer Information Card */
        .customer-info-card {
            background: var(--blue);
            color: var(--white);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 123, 255, 0.3);
        }

        /* Customer Card Modal Styles */
        .customer-card-container {
            background: var(--white);
        }

        .customer-card-container .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            background: #f8f9fa;
        }

        .customer-card-container .nav-tabs .nav-link {
            border: none;
            color: var(--gray);
            font-weight: 500;
            padding: 1rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s ease;
        }

        .customer-card-container .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: var(--white);
            border: none;
        }

        .customer-card-container .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary-color);
        }

        .info-section {
            background: var(--light-gray);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            gap: 0.75rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark);
            text-align: right;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        .modal-xl {
            max-width: 90%;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
            background: #f8f9fa;
        }

        @media print {

            .modal-header,
            .modal-footer,
            .nav-tabs {
                display: none !important;
            }

            .tab-content>.tab-pane {
                display: block !important;
            }

            .modal-dialog {
                max-width: 100% !important;
                margin: 0 !important;
            }
        }

        /* Document Categories Styles */
        .document-categories {
            background: #f8f9fa;
            min-height: 400px;
        }

        .document-category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            background: white;
            border: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .document-category-item:hover {
            background: #f8f9fa;
            border-color: #007bff;
        }

        .document-category-item.selected {
            background: #d4edda;
            border-color: #28a745;
        }

        .document-category-item.completed {
            background: #d4edda;
            border-color: #28a745;
        }

        .document-category-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }

        .document-count {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .document-status {
            background: white;
            border-left: 1px solid #e9ecef;
        }

        .document-status-content {
            text-align: center;
        }

        .document-status-content i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .document-status-content h6 {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .document-status-content p {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .customer-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .customer-info-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .status-badge {
            background: var(--light-green);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .customer-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .info-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .info-value {
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: flex-end;
        }

        .action-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-call {
            background: var(--blue);
            color: var(--white);
        }

        .btn-sync {
            background: var(--blue);
            color: var(--white);
        }

        .btn-debt {
            background: var(--yellow);
            color: var(--dark);
        }

        .btn-card {
            background: var(--primary-color);
            color: var(--white);
        }

        /* Tab Navigation */
        .tab-navigation {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .nav-tabs {
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--gray);
            font-weight: 500;
            padding: 1rem 2rem;
            border-radius: 0;
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: var(--white);
            border: none;
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary-color);
        }

        /* Tab Content */
        .tab-content {
            padding: 2rem;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .detail-section {
            background: var(--light-gray);
            border-radius: 10px;
            padding: 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 500;
            color: var(--gray);
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        .status-tag {
            background: var(--light-green);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Loading */
        .loading {
            text-align: center;
            padding: 3rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 3rem;
            background: var(--light-gray);
            border-radius: 15px;
        }

        .no-results i {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .no-results h3 {
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .no-results p {
            color: var(--gray);
            margin-bottom: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .search-hero h1 {
                font-size: 2rem;
            }

            .search-input-group {
                flex-direction: column;
            }

            .search-filters {
                justify-content: center;
            }

            .customer-info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.5rem 0;
            }

            .logo-text h1 {
                font-size: 1.2rem;
            }

            .search-hero {
                padding: 2rem 0;
            }

            .search-form {
                padding: 1.5rem;
            }

            .customer-info-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Logo -->
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="logo-text">
                        <h1>VIMARS</h1>
                        <p>Financial Services</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php?type=customer&page=home"><i class="fas fa-home"></i> Trang Chủ</a></li>
                        <li><a href="index.php?type=customer&page=search" class="active"><i class="fas fa-search"></i> Tra Cứu</a></li>
                        <li><a href="index.php?type=customer&page=calculator"><i class="fas fa-calculator"></i> Tính Toán</a></li>
                        <li><a href="index.php?type=customer&page=contact"><i class="fas fa-phone"></i> Liên Hệ</a></li>
                    </ul>
                </nav>

                <!-- CTA Button -->
                <a href="index.php?type=customer&page=contact" class="cta-button">
                    <i class="fas fa-rocket"></i>
                    Đăng Ký Ngay
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Search Hero Section -->
        <section class="search-hero">
            <div class="container">
                <h1>Tra Cứu Khoản Vay</h1>
                <p>Tìm kiếm và theo dõi trạng thái khoản vay của bạn một cách nhanh chóng và thuận tiện</p>

                <!-- Search Form -->
                <div class="search-form">
                    <form id="searchForm">
                        <div class="search-input-group">
                            <input type="text" class="search-input" id="searchInput" placeholder="Nhập số hợp đồng, CMND/CCCD hoặc số điện thoại..." required>
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                                Tra Cứu
                            </button>
                        </div>
                        <div class="search-suggestions" style="font-size: 0.8rem; color: var(--gray); margin-top: 0.5rem; text-align: left;">
                            <i class="fas fa-info-circle"></i>
                            Ví dụ: VMS-2024-001, 123456789, 0901234567
                        </div>

                        <div class="search-filters">
                            <select class="filter-select" id="searchType">
                                <option value="all">Tất cả loại</option>
                                <option value="contract">Số hợp đồng</option>
                                <option value="id">CMND/CCCD</option>
                                <option value="phone">Số điện thoại</option>
                            </select>

                            <select class="filter-select" id="statusFilter">
                                <option value="all">Tất cả trạng thái</option>
                                <option value="approved">Đã duyệt</option>
                                <option value="pending">Đang xử lý</option>
                                <option value="rejected">Từ chối</option>
                            </select>

                            <select class="filter-select" id="dateFilter">
                                <option value="all">Tất cả thời gian</option>
                                <option value="today">Hôm nay</option>
                                <option value="week">Tuần này</option>
                                <option value="month">Tháng này</option>
                                <option value="year">Năm nay</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Search Results Section -->
        <section class="search-results" id="searchResults">
            <!-- Loading State -->
            <div class="loading" id="loadingSection" style="display: none;">
                <div class="spinner"></div>
                <p>Đang tìm kiếm...</p>
            </div>

            <!-- No Results State -->
            <section class="no-results" id="noResultsSection" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>Không tìm thấy kết quả</h3>
                <p>Vui lòng kiểm tra lại thông tin tìm kiếm hoặc liên hệ với chúng tôi để được hỗ trợ</p>
                <a href="index.php?type=customer&page=contact" class="action-btn btn-primary">
                    <i class="fas fa-phone"></i>
                    Liên Hệ Hỗ Trợ
                </a>
            </section>

            <!-- Results Container -->
            <div class="container" id="resultsContainer">
                <!-- Results will be populated by JavaScript -->
            </div>
        </section>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Search functionality
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        function performSearch() {
            const searchInput = document.getElementById('searchInput').value;
            const searchType = document.getElementById('searchType').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            // Show loading
            showLoading();

            // Build API URL
            const apiUrl = new URL('/vaycamco/pages/admin/api/search.php', window.location.origin);
            apiUrl.searchParams.set('search', searchInput);
            apiUrl.searchParams.set('type', searchType);
            apiUrl.searchParams.set('status', statusFilter);
            apiUrl.searchParams.set('date', dateFilter);

            // Make API call
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    console.log('API Response:', data); // Debug log

                    if (data.success && data.data.length > 0) {
                        displayResults(data.data);
                    } else {
                        showNoResults();
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    hideLoading();
                    showNoResults();
                });
        }

        function displayResults(results) {
            const container = document.getElementById('resultsContainer');
            container.innerHTML = '';

            results.forEach((result, index) => {
                try {
                    const resultCard = createResultCard(result);
                    container.appendChild(resultCard);
                } catch (error) {
                    console.error(`Error creating card ${index}:`, error);
                    // Create a simple error card
                    const errorCard = document.createElement('div');
                    errorCard.className = 'alert alert-danger mb-4';
                    errorCard.innerHTML = `<strong>Lỗi hiển thị kết quả ${index + 1}:</strong> ${error.message}`;
                    container.appendChild(errorCard);
                }
            });

            showResults();
            console.log('Displayed results:', results); // Debug log
        }

        function createResultCard(result) {
            console.log('Creating result card for:', result); // Debug log
            try {
                const card = document.createElement('div');
                card.className = 'mb-4';

                card.innerHTML = `
                <!-- Customer Information Card -->
                <div class="customer-info-card">
                    <div class="customer-info-header">
                        <div class="customer-info-title">
                            <i class="fas fa-user"></i>
                            Thông tin khách hàng
                        </div>
                        <div class="status-badge">${result.status_text}</div>
                    </div>
                    
                    <div class="customer-info-grid">
                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">Tên khách hàng:</span>
                                <span class="info-value">${result.customer_name}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value">${result.customer_phone}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">CMND/CCCD:</span>
                                <span class="info-value">${result.identity_number}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Địa chỉ:</span>
                                <span class="info-value">${result.customer_address}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Mã hợp đồng:</span>
                                <span class="info-value">${result.contract_id}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Số tiền vay:</span>
                                <span class="info-value">${result.loan_amount}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày bắt đầu:</span>
                                <span class="info-value">${result.created_date}</span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <div class="info-item">
                                <span class="info-label">Trạng thái:</span>
                                <span class="info-value">
                                    <span class="status-badge">${result.status_text}</span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Lãi suất/tháng:</span>
                                <span class="info-value">${result.interest_rate}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Lãi suất/ngày:</span>
                                <span class="info-value">${calculateDailyRate(result.interest_rate)}%</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tiền đã trả:</span>
                                <span class="info-value">0 VNĐ</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Còn lại:</span>
                                <span class="info-value">0 VNĐ</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ghi chú:</span>
                                <span class="info-value">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="action-btn btn-call" onclick="callCustomer('${result.customer_phone}')">
                            <i class="fas fa-phone"></i>
                            Gọi khách hàng
                        </button>
                        <button class="action-btn btn-sync" onclick="syncData('${result.contract_id}')">
                            <i class="fas fa-sync"></i>
                            Đồng bộ
                        </button>
                        <button class="action-btn btn-debt" onclick="viewCurrentDebt('${result.contract_id}')">
                            <i class="fas fa-money-bill-wave"></i>
                            Dư nợ hiện tại
                        </button>
                        <button class="action-btn btn-card" onclick="viewCustomerCard('${result.contract_id}')">
                            <i class="fas fa-id-card"></i>
                            Thẻ Khách Hàng
                        </button>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="tab-navigation">
                    <ul class="nav nav-tabs" id="loanTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                                Tổng hợp
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">
                                Lịch sử đóng lãi + phí
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">
                                Chứng từ
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="loanTabContent">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                            <div class="detail-grid">
                                <div class="detail-section">
                                    <div class="detail-item">
                                        <span class="detail-label">Sản phẩm:</span>
                                        <span class="detail-value">CÓ</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Trạng thái HĐ:</span>
                                        <span class="detail-value">
                                            KHÔNG <span class="status-tag">BÌNH THƯỜNG</span>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Ký điện tử:</span>
                                        <span class="detail-value">Có</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Ngày ký:</span>
                                        <span class="detail-value">${result.created_date}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Cửa hàng:</span>
                                        <span class="detail-value">${result.department || 'PDV TP.HCM'}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Ngày kết thúc:</span>
                                        <span class="detail-value">${calculateEndDate(result.created_date, result.loan_term)}</span>
                                    </div>
                                </div>
                                
                                <div class="detail-section">
                                    <div class="detail-item">
                                        <span class="detail-label">Bảo hiểm vật chất:</span>
                                        <span class="detail-value">-</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Bảo hiểm nằm viện:</span>
                                        <span class="detail-value">-</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Ngày vay:</span>
                                        <span class="detail-value">-</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Ngày kín hạn:</span>
                                        <span class="detail-value">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                            <div class="p-4">
                                <!-- Payment Summary -->
                                <div class="row mb-4" id="paymentSummary" style="display: none;">
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tổng số lần thanh toán</h6>
                                                <h4 id="totalPayments">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tổng tiền đã trả</h6>
                                                <h4 id="totalAmount">0 VNĐ</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Lần thanh toán đầu</h6>
                                                <h6 id="firstPayment">N/A</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Lần thanh toán cuối</h6>
                                                <h6 id="lastPayment">N/A</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment History Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="paymentHistoryTable" style="display: none;">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>STT</th>
                                                <th>Ngày thanh toán</th>
                                                <th>Loại thanh toán</th>
                                                <th>Số tiền</th>
                                                <th>Phương thức</th>
                                                <th>Số tham chiếu</th>
                                                <th>Mô tả</th>
                                                <th>Người tạo</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody id="paymentHistoryBody">
                                            <!-- Payment history rows will be populated here -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Loading State -->
                                <div id="paymentHistoryLoading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Đang tải lịch sử thanh toán...</p>
                                </div>

                                <!-- No Data State -->
                                <div id="paymentHistoryNoData" class="text-center py-4" style="display: none;">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <h5>Lịch sử thanh toán</h5>
                                    <p class="text-muted">Chưa có lịch sử thanh toán</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                            <div class="p-0">
                                <!-- Document Header -->
                                <div class="bg-primary text-white p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-file-alt me-2"></i>
                                            Chứng từ ${result.application_code}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" aria-label="Close"></button>
                                    </div>
                                </div>

                                <div class="row m-0">
                                    <!-- Left Panel - Document Categories -->
                                    <div class="col-md-8 p-0">
                                        <div class="document-categories p-3">
                                            <div id="documentCategoriesList">
                                                <!-- Document categories will be populated here -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Panel - Status Message -->
                                    <div class="col-md-4 p-0">
                                        <div class="document-status p-3 h-100 d-flex align-items-center justify-content-center">
                                            <div class="text-center">
                                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">Chưa có chứng từ</h6>
                                                <p class="text-muted small">Vui lòng chọn loại chứng từ để xem chi tiết</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loading State -->
                                <div id="documentsLoading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Đang tải danh sách chứng từ...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                return card;
            } catch (error) {
                console.error('Error creating result card:', error);
                const errorCard = document.createElement('div');
                errorCard.className = 'alert alert-danger';
                errorCard.innerHTML = `<strong>Lỗi:</strong> ${error.message}`;
                return errorCard;
            }
        }

        function calculateDailyRate(monthlyRate) {
            // Convert monthly rate to daily rate (approximate)
            const rate = parseFloat(monthlyRate.replace('%/năm', ''));
            return (rate / 365).toFixed(4);
        }

        function calculateEndDate(startDate, termMonths) {
            const start = new Date(startDate.split('/').reverse().join('-'));
            const end = new Date(start);
            end.setMonth(end.getMonth() + parseInt(termMonths));
            return end.toLocaleDateString('vi-VN');
        }

        function showLoading() {
            document.getElementById('loadingSection').style.display = 'block';
            document.getElementById('noResultsSection').style.display = 'none';
            document.getElementById('resultsContainer').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loadingSection').style.display = 'none';
        }

        function showNoResults() {
            document.getElementById('noResultsSection').style.display = 'block';
            document.getElementById('resultsContainer').style.display = 'none';
        }

        function showResults() {
            document.getElementById('noResultsSection').style.display = 'none';
            document.getElementById('resultsContainer').style.display = 'block';
            console.log('Showing results container'); // Debug log
        }

        // Action functions
        function callCustomer(phone) {
            window.open(`tel:${phone}`, '_self');
        }

        function syncData(contractId) {
            alert(`Đồng bộ dữ liệu cho hợp đồng: ${contractId}`);
        }

        function viewCurrentDebt(contractId) {
            alert(`Xem dư nợ hiện tại cho hợp đồng: ${contractId}`);
        }

        function viewCustomerCard(contractId) {
            // Fetch customer card data
            fetch(`/vaycamco/pages/admin/api/customer-card.php?contract_id=${contractId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showCustomerCardModal(data.data);
                    } else {
                        alert('Không thể tải thông tin thẻ khách hàng');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải thông tin');
                });
        }

        function showCustomerCardModal(customerData) {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'customerCardModal';
            modal.setAttribute('tabindex', '-1');
            modal.setAttribute('aria-labelledby', 'customerCardModalLabel');
            modal.setAttribute('aria-hidden', 'true');

            modal.innerHTML = `
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="customerCardModalLabel">
                                <i class="fas fa-id-card me-2"></i>
                                Thẻ Khách Hàng - ${customerData.application_code}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="customer-card-container">
                                <!-- Tab Navigation -->
                                <ul class="nav nav-tabs nav-fill" id="customerCardTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="loan-info-tab" data-bs-toggle="tab" data-bs-target="#loan-info" type="button" role="tab">
                                            <i class="fas fa-money-bill-wave me-2"></i>
                                            Thông Tin Khoản Vay
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="customer-info-tab" data-bs-toggle="tab" data-bs-target="#customer-info" type="button" role="tab">
                                            <i class="fas fa-user me-2"></i>
                                            Thông Tin Khách Hàng
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="emergency-info-tab" data-bs-toggle="tab" data-bs-target="#emergency-info" type="button" role="tab">
                                            <i class="fas fa-phone-alt me-2"></i>
                                            Thông Tin Khẩn Cấp
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="asset-info-tab" data-bs-toggle="tab" data-bs-target="#asset-info" type="button" role="tab">
                                            <i class="fas fa-car me-2"></i>
                                            Thông Tin Tài Sản
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="approval-info-tab" data-bs-toggle="tab" data-bs-target="#approval-info" type="button" role="tab">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Thông Tin Phê Duyệt
                                        </button>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="customerCardTabContent">
                                    <!-- Loan Information Tab -->
                                    <div class="tab-pane fade show active" id="loan-info" role="tabpanel">
                                        <div class="p-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-file-contract text-primary"></i>
                                                            Thông Tin Hợp Đồng
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Mã hợp đồng:</span>
                                                                <span class="info-value">${customerData.application_code}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Số tiền vay:</span>
                                                                <span class="info-value">${customerData.loan_amount}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Thời hạn:</span>
                                                                <span class="info-value">${customerData.loan_term}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Lãi suất/tháng:</span>
                                                                <span class="info-value">${customerData.interest_rate}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Lãi suất/ngày:</span>
                                                                <span class="info-value">${customerData.daily_rate}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Mục đích vay:</span>
                                                                <span class="info-value">${customerData.loan_purpose}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-shield-alt text-success"></i>
                                                            Bảo Hiểm
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Bảo hiểm sức khỏe:</span>
                                                                <span class="info-value">
                                                                    <span class="badge ${customerData.insurance.health === 'Có' ? 'bg-success' : 'bg-secondary'}">
                                                                        ${customerData.insurance.health}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Bảo hiểm nhân thọ:</span>
                                                                <span class="info-value">
                                                                    <span class="badge ${customerData.insurance.life === 'Có' ? 'bg-success' : 'bg-secondary'}">
                                                                        ${customerData.insurance.life}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Bảo hiểm xe:</span>
                                                                <span class="info-value">
                                                                    <span class="badge ${customerData.insurance.vehicle === 'Có' ? 'bg-success' : 'bg-secondary'}">
                                                                        ${customerData.insurance.vehicle}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Information Tab -->
                                    <div class="tab-pane fade" id="customer-info" role="tabpanel">
                                        <div class="p-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-user text-info"></i>
                                                            Thông Tin Cá Nhân
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Họ và tên:</span>
                                                                <span class="info-value">${customerData.customer_name}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">CMND/CCCD:</span>
                                                                <span class="info-value">${customerData.identity_number}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Số điện thoại:</span>
                                                                <span class="info-value">${customerData.customer_phone}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Email:</span>
                                                                <span class="info-value">${customerData.customer_email || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ngày sinh:</span>
                                                                <span class="info-value">${customerData.customer_birth_date || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Địa chỉ:</span>
                                                                <span class="info-value">${customerData.customer_address}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-briefcase text-warning"></i>
                                                            Thông Tin Công Việc
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Nghề nghiệp:</span>
                                                                <span class="info-value">${customerData.customer_job || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Công ty:</span>
                                                                <span class="info-value">${customerData.customer_company || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Thu nhập:</span>
                                                                <span class="info-value">${customerData.customer_income ? number_format(customerData.customer_income) + ' VNĐ' : 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Nơi cấp CMND:</span>
                                                                <span class="info-value">${customerData.customer_id_issued_place || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ngày cấp CMND:</span>
                                                                <span class="info-value">${customerData.customer_id_issued_date || 'N/A'}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Emergency Information Tab -->
                                    <div class="tab-pane fade" id="emergency-info" role="tabpanel">
                                        <div class="p-4">
                                            <div class="info-section">
                                                <h6 class="section-title">
                                                    <i class="fas fa-phone-alt text-danger"></i>
                                                    Thông Tin Liên Hệ Khẩn Cấp
                                                </h6>
                                                <div class="info-grid">
                                                    <div class="info-item">
                                                        <span class="info-label">Tên người liên hệ:</span>
                                                        <span class="info-value">${customerData.emergency_contact.name}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Số điện thoại:</span>
                                                        <span class="info-value">${customerData.emergency_contact.phone}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Mối quan hệ:</span>
                                                        <span class="info-value">${customerData.emergency_contact.relationship || 'N/A'}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Địa chỉ:</span>
                                                        <span class="info-value">${customerData.emergency_contact.address || 'N/A'}</span>
                                                    </div>
                                                    <div class="info-item">
                                                        <span class="info-label">Ghi chú:</span>
                                                        <span class="info-value">${customerData.emergency_contact.note || 'N/A'}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Asset Information Tab -->
                                    <div class="tab-pane fade" id="asset-info" role="tabpanel">
                                        <div class="p-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-car text-primary"></i>
                                                            Thông Tin Tài Sản
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Tên tài sản:</span>
                                                                <span class="info-value">${customerData.asset_name}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Số lượng:</span>
                                                                <span class="info-value">${customerData.asset_quantity || 1}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Giá trị:</span>
                                                                <span class="info-value">${customerData.asset_value}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Biển số xe:</span>
                                                                <span class="info-value">${customerData.asset_license_plate || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Số khung:</span>
                                                                <span class="info-value">${customerData.asset_frame_number || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Số máy:</span>
                                                                <span class="info-value">${customerData.asset_engine_number || 'N/A'}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-file-alt text-success"></i>
                                                            Thông Tin Đăng Ký
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Số đăng ký:</span>
                                                                <span class="info-value">${customerData.asset_registration_number || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ngày đăng ký:</span>
                                                                <span class="info-value">${customerData.asset_registration_date || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Mô tả:</span>
                                                                <span class="info-value">${customerData.asset_description || 'N/A'}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Approval Information Tab -->
                                    <div class="tab-pane fade" id="approval-info" role="tabpanel">
                                        <div class="p-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-check-circle text-success"></i>
                                                            Trạng Thái Phê Duyệt
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Trạng thái:</span>
                                                                <span class="info-value">
                                                                    <span class="badge ${getStatusBadgeClass(customerData.status)}">
                                                                        ${customerData.status_text}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Cấp phê duyệt hiện tại:</span>
                                                                <span class="info-value">${customerData.current_approval_level || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Cấp phê duyệt cao nhất:</span>
                                                                <span class="info-value">${customerData.highest_approval_level || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Tổng số cấp:</span>
                                                                <span class="info-value">${customerData.total_approval_levels || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Quyết định cuối:</span>
                                                                <span class="info-value">${customerData.final_decision || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ngày quyết định:</span>
                                                                <span class="info-value">${customerData.decision_date || 'N/A'}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="info-section">
                                                        <h6 class="section-title">
                                                            <i class="fas fa-money-bill-wave text-warning"></i>
                                                            Thông Tin Tài Chính
                                                        </h6>
                                                        <div class="info-grid">
                                                            <div class="info-item">
                                                                <span class="info-label">Số tiền được duyệt:</span>
                                                                <span class="info-value">${customerData.approved_amount ? number_format(customerData.approved_amount) + ' VNĐ' : 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ngày tạo:</span>
                                                                <span class="info-value">${customerData.created_date}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ngày cập nhật:</span>
                                                                <span class="info-value">${customerData.updated_date}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Người tạo:</span>
                                                                <span class="info-value">${customerData.created_by || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Phòng ban:</span>
                                                                <span class="info-value">${customerData.department || 'N/A'}</span>
                                                            </div>
                                                            <div class="info-item">
                                                                <span class="info-label">Ghi chú:</span>
                                                                <span class="info-value">${customerData.decision_notes || 'N/A'}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>
                                Đóng
                            </button>
                            <button type="button" class="btn btn-primary" onclick="printCustomerCard()">
                                <i class="fas fa-print me-2"></i>
                                In Thẻ
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Initialize Bootstrap modal
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();

            // Remove modal from DOM when hidden
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        }

        function getStatusBadgeClass(status) {
            const classMap = {
                'pending': 'bg-warning',
                'approved': 'bg-success',
                'rejected': 'bg-danger',
                'completed': 'bg-success',
                'cancelled': 'bg-secondary'
            };
            return classMap[status] || 'bg-secondary';
        }

        function number_format(number) {
            return new Intl.NumberFormat('vi-VN').format(number);
        }

        function printCustomerCard() {
            window.print();
        }

        function getStatusBadgeClass(status) {
            const classMap = {
                'pending': 'bg-warning',
                'approved': 'bg-success',
                'rejected': 'bg-danger',
                'completed': 'bg-success',
                'cancelled': 'bg-secondary'
            };
            return classMap[status] || 'bg-secondary';
        }

        function number_format(number) {
            return new Intl.NumberFormat('vi-VN').format(number);
        }

        // Load payment history when tab is clicked
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener for history tab
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'history-tab') {
                    const contractId = e.target.closest('.customer-card-container')?.querySelector('.modal-title')?.textContent?.split(' - ')[1];
                    if (contractId) {
                        loadPaymentHistory(contractId);
                    }
                }
            });
        });

        function loadPaymentHistory(contractId) {
            // Show loading
            document.getElementById('paymentHistoryLoading').style.display = 'block';
            document.getElementById('paymentHistoryNoData').style.display = 'none';
            document.getElementById('paymentHistoryTable').style.display = 'none';
            document.getElementById('paymentSummary').style.display = 'none';

            // Fetch payment history
            fetch(`/vaycamco/pages/admin/api/payment-history.php?contract_id=${contractId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('paymentHistoryLoading').style.display = 'none';

                    if (data.success && data.data.length > 0) {
                        displayPaymentHistory(data.data, data.summary);
                    } else {
                        showPaymentHistoryNoData();
                    }
                })
                .catch(error => {
                    console.error('Payment history error:', error);
                    document.getElementById('paymentHistoryLoading').style.display = 'none';
                    showPaymentHistoryNoData();
                });
        }

        function displayPaymentHistory(payments, summary) {
            // Update summary
            document.getElementById('totalPayments').textContent = summary.total_payments;
            document.getElementById('totalAmount').textContent = summary.total_amount;
            document.getElementById('firstPayment').textContent = summary.first_payment;
            document.getElementById('lastPayment').textContent = summary.last_payment;

            // Populate table
            const tbody = document.getElementById('paymentHistoryBody');
            tbody.innerHTML = '';

            payments.forEach((payment, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${payment.payment_date}</td>
                    <td>
                        <span class="badge bg-primary">${payment.payment_type}</span>
                    </td>
                    <td class="fw-bold text-success">${payment.amount}</td>
                    <td>${payment.payment_method}</td>
                    <td>${payment.reference_number}</td>
                    <td>${payment.description}</td>
                    <td>${payment.created_by}</td>
                    <td>
                        <span class="badge bg-success">${payment.status}</span>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Show table and summary
            document.getElementById('paymentHistoryTable').style.display = 'table';
            document.getElementById('paymentSummary').style.display = 'flex';
        }

        function showPaymentHistoryNoData() {
            document.getElementById('paymentHistoryNoData').style.display = 'block';
        }

        // Load documents when tab is clicked
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener for documents tab
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'documents-tab') {
                    const contractId = e.target.closest('.customer-card-container')?.querySelector('.modal-title')?.textContent?.split(' - ')[1];
                    if (contractId) {
                        loadDocuments(contractId);
                    }
                }
            });
        });

        function loadDocuments(contractId) {
            // Show loading
            document.getElementById('documentsLoading').style.display = 'block';
            document.querySelector('.document-categories').style.display = 'none';
            document.querySelector('.document-status').style.display = 'none';

            // Fetch documents
            fetch(`/vaycamco/pages/admin/api/documents.php?contract_id=${contractId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('documentsLoading').style.display = 'none';

                    if (data.success && data.data.length > 0) {
                        displayDocuments(data.data);
                    } else {
                        showDocumentsNoData();
                    }
                })
                .catch(error => {
                    console.error('Documents error:', error);
                    document.getElementById('documentsLoading').style.display = 'none';
                    showDocumentsNoData();
                });
        }

        function displayDocuments(documents) {
            const container = document.getElementById('documentCategoriesList');
            container.innerHTML = '';

            documents.forEach((doc, index) => {
                const item = document.createElement('div');
                item.className = `document-category-item ${doc.status === 'completed' ? 'completed' : ''}`;
                item.onclick = () => selectDocumentCategory(doc, index);

                item.innerHTML = `
                    <div class="document-category-name">${doc.name}</div>
                    <div class="document-count">${doc.count}</div>
                `;

                container.appendChild(item);
            });

            // Show containers
            document.querySelector('.document-categories').style.display = 'block';
            document.querySelector('.document-status').style.display = 'flex';
        }

        function selectDocumentCategory(doc, index) {
            // Remove previous selection
            document.querySelectorAll('.document-category-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Add selection to clicked item
            event.target.closest('.document-category-item').classList.add('selected');

            // Update status message
            const statusContainer = document.querySelector('.document-status');
            statusContainer.innerHTML = `
                <div class="document-status-content">
                    <i class="fas fa-file-alt"></i>
                    <h6>${doc.name}</h6>
                    <p>${doc.description}</p>
                    <div class="mt-3">
                        <span class="badge ${doc.status === 'completed' ? 'bg-success' : 'bg-warning'}">
                            ${doc.status === 'completed' ? 'Đã hoàn thành' : 'Chưa hoàn thành'}
                        </span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Số lượng: ${doc.count} tài liệu</small>
                    </div>
                </div>
            `;
        }

        function showDocumentsNoData() {
            document.querySelector('.document-categories').style.display = 'block';
            document.querySelector('.document-status').style.display = 'flex';

            const container = document.getElementById('documentCategoriesList');
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Chưa có chứng từ</h6>
                    <p class="text-muted small">Vui lòng liên hệ nhân viên để được hỗ trợ</p>
                </div>
            `;
        }

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>

</html>