<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIMARS Financial Services - Dịch vụ tài chính uy tín</title>
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

        /* Product Cards */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .product-icon {
            width: 60px;
            height: 60px;
            background: var(--light-green);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .product-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .product-description {
            color: var(--gray);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .product-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .detail-item i {
            color: var(--primary-color);
            font-size: 1rem;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        .product-button {
            background: var(--light-green);
            color: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .product-button:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Floating Sidebar */
        .floating-sidebar {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            z-index: 999;
        }

        .social-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .social-icon:hover {
            transform: scale(1.1);
        }

        .social-icon.plus {
            background: var(--primary-color);
        }

        .social-icon.ai {
            background: var(--primary-color);
        }

        .social-icon.chat {
            background: #6f42c1;
        }

        .social-icon.zalo {
            background: var(--primary-color);
        }

        .social-icon.phone {
            background: var(--primary-color);
        }

        .social-icon.youtube {
            background: #dc3545;
        }

        .social-icon.facebook {
            background: #1877f2;
        }

        .social-icon.camera {
            background: #17a2b8;
        }

        .action-button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .action-button:hover {
            transform: scale(1.1);
            background: var(--primary-dark);
        }

        /* FAQ Styles */
        .accordion-button:not(.collapsed) {
            background-color: var(--light-green);
            color: var(--primary-color);
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }

        /* CTA Styles */
        .cta-box {
            transition: all 0.3s ease;
        }

        .cta-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* Feature Card Styles */
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* Service Card Styles */
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* Contact Panel Responsive */
        @media (max-width: 768px) {
            .contact-panels {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }

            .product-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .floating-sidebar {
                right: 10px;
            }

            .social-icon,
            .action-button {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 0.5rem 0;
            }

            .logo-text h1 {
                font-size: 1.2rem;
            }

            .product-card {
                padding: 1.5rem;
            }

            .product-details {
                grid-template-columns: 1fr;
                gap: 0.5rem;
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
                        <li><a href="#home"><i class="fas fa-home"></i> Trang Chủ</a></li>
                        <li><a href="#services"><i class="fas fa-star"></i> Dịch Vụ</a></li>
                        <li><a href="#about"><i class="fas fa-info-circle"></i> Giới Thiệu</a></li>
                        <li><a href="#calculator"><i class="fas fa-calculator"></i> Tính Toán</a></li>
                        <li><a href="index.php?type=customer&page=search"><i class="fas fa-search"></i> Tra Cứu</a></li>
                        <li><a href="#faq"><i class="fas fa-question-circle"></i> Hỏi Đáp</a></li>
                        <li><a href="#contact"><i class="fas fa-phone"></i> Liên Hệ</a></li>
                    </ul>
                </nav>

                <!-- CTA Button -->
                <a href="#contact" class="cta-button">
                    <i class="fas fa-rocket"></i>
                    Đăng Ký Ngay
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Car Valuation Section -->
        <section class="car-valuation-section" style="margin-top: 0; min-height: 100vh; display: flex; align-items: center; position: relative; overflow: hidden; background: linear-gradient(135deg, rgba(26,26,26,0.9) 0%, rgba(26,26,26,0.7) 100%), linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="container" style="position: relative; z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="valuation-content" style="color: var(--white);">
                            <h1 class="valuation-title" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 1.5rem; line-height: 1.2;">
                                <span style="color: #ff6b35;">Định Giá Xe Ngay</span>
                                <br>
                                <span style="color: var(--white); font-size: 2rem;">- Tiếp Cận Vốn Vay</span>
                            </h1>

                            <p class="valuation-description" style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 2.5rem; opacity: 0.9;">
                                Công cụ đầu tiên trên thị trường giúp bạn kiểm tra ngay số vốn vay từ chính xế cưng
                            </p>

                            <div class="valuation-features" style="margin-bottom: 3rem;">
                                <div class="feature-item" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                                    <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.1rem; margin-top: 0.2rem;"></i>
                                    <span style="font-size: 1rem;">Định giá xe theo dữ liệu tổng hợp của các nền tảng tín dụng uy tín</span>
                                </div>
                                <div class="feature-item" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                                    <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.1rem; margin-top: 0.2rem;"></i>
                                    <span style="font-size: 1rem;">Cung cấp đa dạng các gói vay từ nhiều tổ chức tài chính trên thị trường</span>
                                </div>
                            </div>

                            <!-- Car Valuation Form -->
                            <div class="valuation-form" style="background: var(--white); border-radius: 12px; padding: 2rem; box-shadow: 0 8px 25px rgba(0,0,0,0.2); max-width: 500px;">
                                <form>
                                    <div class="form-group mb-3">
                                        <label class="form-label" style="font-weight: 600; color: var(--dark); margin-bottom: 0.8rem; font-size: 0.95rem;">
                                            Chọn phương tiện
                                        </label>
                                        <div class="vehicle-type" style="display: flex; gap: 2rem;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="vehicleType" id="car" checked style="accent-color: var(--primary-color);">
                                                <label class="form-check-label" for="car" style="color: var(--dark); font-weight: 500; font-size: 0.95rem;">
                                                    Ô tô
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="vehicleType" id="motorcycle" style="accent-color: var(--primary-color);">
                                                <label class="form-check-label" for="motorcycle" style="color: var(--dark); font-weight: 500; font-size: 0.95rem;">
                                                    Xe máy
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" style="font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; font-size: 0.9rem;">
                                                Hãng xe
                                            </label>
                                            <select class="form-select" style="border: 1px solid #e9ecef; border-radius: 6px; padding: 0.6rem; font-size: 0.9rem;">
                                                <option selected>Chọn hãng xe</option>
                                                <option value="toyota">Toyota</option>
                                                <option value="honda">Honda</option>
                                                <option value="ford">Ford</option>
                                                <option value="bmw">BMW</option>
                                                <option value="mercedes">Mercedes</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" style="font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; font-size: 0.9rem;">
                                                Năm sản xuất
                                            </label>
                                            <select class="form-select" style="border: 1px solid #e9ecef; border-radius: 6px; padding: 0.6rem; font-size: 0.9rem;">
                                                <option selected>Chọn năm sản xuất</option>
                                                <option value="2024">2024</option>
                                                <option value="2023">2023</option>
                                                <option value="2022">2022</option>
                                                <option value="2021">2021</option>
                                                <option value="2020">2020</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" style="font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; font-size: 0.9rem;">
                                                Tên xe
                                            </label>
                                            <select class="form-select" style="border: 1px solid #e9ecef; border-radius: 6px; padding: 0.6rem; font-size: 0.9rem;">
                                                <option selected>Chọn tên xe</option>
                                                <option value="camry">Camry</option>
                                                <option value="accord">Accord</option>
                                                <option value="civic">Civic</option>
                                                <option value="cr-v">CR-V</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" style="font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; font-size: 0.9rem;">
                                                ODO
                                            </label>
                                            <input type="number" class="form-control" placeholder="Nhập số km" style="border: 1px solid #e9ecef; border-radius: 6px; padding: 0.6rem; font-size: 0.9rem;">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100" onclick="valueCar()" style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%); border: none; padding: 0.8rem; border-radius: 8px; font-weight: 600; font-size: 1rem; color: var(--white); transition: all 0.3s ease; margin-top: 0.5rem;">
                                        <i class="fas fa-calculator me-2"></i>
                                        Định giá xe
                                    </button>

                                    <!-- Test button for modal -->
                                    <button type="button" class="btn btn-secondary w-100 mt-2" onclick="testModal()" style="background: #6c757d; border: none; padding: 0.5rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; color: var(--white);">
                                        <i class="fas fa-eye me-2"></i>
                                        Test Modal
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <!-- Product Grid -->
            <section id="home" class="product-grid">
                <!-- Vay Cầm Cố Sổ Đỏ -->
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="product-title">Vay Cầm Cố Sổ Đỏ</h3>
                    <p class="product-description">
                        Cầm cố sổ đỏ nhanh chóng, giải ngân trong 24h. Lãi suất ưu đãi, thời hạn linh hoạt.
                    </p>
                    <div class="product-details">
                        <div class="detail-item">
                            <i class="fas fa-box"></i>
                            <span>Hạn mức: <span class="detail-value">2 tỷ VNĐ</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-percentage"></i>
                            <span>Lãi suất: <span class="detail-value">Từ 8%/năm</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Thời hạn: <span class="detail-value">1 - 20 năm</span></span>
                        </div>
                    </div>
                    <a href="#" class="product-button">
                        <i class="fas fa-info-circle"></i>
                        Chi Tiết
                    </a>
                </div>

                <!-- Vay Cầm Cố Xe Máy -->
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h3 class="product-title">Vay Cầm Cố Xe Máy</h3>
                    <p class="product-description">
                        Vay cầm cố xe máy, vẫn sử dụng xe bình thường. Thủ tục đơn giản, chỉ cần CMND và xe máy.
                    </p>
                    <div class="product-details">
                        <div class="detail-item">
                            <i class="fas fa-box"></i>
                            <span>Hạn mức: <span class="detail-value">50 triệu VNĐ</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-percentage"></i>
                            <span>Lãi suất: <span class="detail-value">Từ 15%/năm</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Thời hạn: <span class="detail-value">3 - 36 tháng</span></span>
                        </div>
                    </div>
                    <a href="#" class="product-button">
                        <i class="fas fa-info-circle"></i>
                        Chi Tiết
                    </a>
                </div>

                <!-- Vay Cầm Cố Ô Tô -->
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3 class="product-title">Vay Cầm Cố Ô Tô</h3>
                    <p class="product-description">
                        Cầm cố ô tô với giá trị lên đến 80% giá trị xe. Vẫn có thể sử dụng xe trong thời gian vay.
                    </p>
                    <div class="product-details">
                        <div class="detail-item">
                            <i class="fas fa-box"></i>
                            <span>Hạn mức: <span class="detail-value">800 triệu VNĐ</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-percentage"></i>
                            <span>Lãi suất: <span class="detail-value">Từ 12%/năm</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Thời hạn: <span class="detail-value">6 - 60 tháng</span></span>
                        </div>
                    </div>
                    <a href="#" class="product-button">
                        <i class="fas fa-info-circle"></i>
                        Chi Tiết
                    </a>
                </div>

                <!-- Vay Tín Chấp -->
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="product-title">Vay Tín Chấp</h3>
                    <p class="product-description">
                        Vay không cần tài sản đảm bảo, dựa trên uy tín và khả năng thu nhập của khách hàng.
                    </p>
                    <div class="product-details">
                        <div class="detail-item">
                            <i class="fas fa-box"></i>
                            <span>Hạn mức: <span class="detail-value">300 triệu VNĐ</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-percentage"></i>
                            <span>Lãi suất: <span class="detail-value">Từ 18%/năm</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Thời hạn: <span class="detail-value">6 - 60 tháng</span></span>
                        </div>
                    </div>
                    <a href="#" class="product-button">
                        <i class="fas fa-info-circle"></i>
                        Chi Tiết
                    </a>
                </div>

                <!-- Vay Kinh Doanh -->
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="product-title">Vay Kinh Doanh</h3>
                    <p class="product-description">
                        Hỗ trợ vốn kinh doanh cho cá nhân, doanh nghiệp vừa và nhỏ với lãi suất ưu đãi.
                    </p>
                    <div class="product-details">
                        <div class="detail-item">
                            <i class="fas fa-box"></i>
                            <span>Hạn mức: <span class="detail-value">1.5 tỷ VNĐ</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-percentage"></i>
                            <span>Lãi suất: <span class="detail-value">Từ 10%/năm</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Thời hạn: <span class="detail-value">12 - 120 tháng</span></span>
                        </div>
                    </div>
                    <a href="#" class="product-button">
                        <i class="fas fa-info-circle"></i>
                        Chi Tiết
                    </a>
                </div>

                <!-- Vay Bảo Hiểm Nhân Thọ -->
                <div class="product-card">
                    <div class="product-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="product-title">Vay Bảo Hiểm Nhân Thọ</h3>
                    <p class="product-description">
                        Sử dụng hợp đồng bảo hiểm nhân thọ làm tài sản đảm bảo vay với lãi suất ưu đãi.
                    </p>
                    <div class="product-details">
                        <div class="detail-item">
                            <i class="fas fa-box"></i>
                            <span>Hạn mức: <span class="detail-value">500 triệu VNĐ</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-percentage"></i>
                            <span>Lãi suất: <span class="detail-value">Từ 9%/năm</span></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Thời hạn: <span class="detail-value">12 - 84 tháng</span></span>
                        </div>
                    </div>
                    <a href="#" class="product-button">
                        <i class="fas fa-info-circle"></i>
                        Chi Tiết
                    </a>
                </div>
            </section>

            <!-- Services Section -->
            <section id="services" style="margin-top: 4rem;">
                <div class="container">
                    <h2 class="text-center mb-5" style="color: var(--dark); font-weight: bold;">Dịch Vụ Tài Chính</h2>
                    <div class="row">
                        <div class="col-lg-4 mb-4">
                            <div class="service-card" style="background: var(--white); border-radius: 15px; padding: 2rem; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef; transition: all 0.3s ease;">
                                <div class="service-icon mb-3">
                                    <i class="fas fa-handshake" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                </div>
                                <h3 class="service-title mb-3" style="color: var(--dark); font-weight: bold;">Tư Vấn Tài Chính</h3>
                                <p class="service-description" style="color: var(--gray); line-height: 1.6;">
                                    Đội ngũ chuyên gia tư vấn giàu kinh nghiệm sẽ giúp bạn lựa chọn sản phẩm vay phù hợp nhất
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="service-card" style="background: var(--white); border-radius: 15px; padding: 2rem; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef; transition: all 0.3s ease;">
                                <div class="service-icon mb-3">
                                    <i class="fas fa-shield-alt" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                </div>
                                <h3 class="service-title mb-3" style="color: var(--dark); font-weight: bold;">Bảo Hiểm Tài Chính</h3>
                                <p class="service-description" style="color: var(--gray); line-height: 1.6;">
                                    Bảo vệ tài chính cho bạn và gia đình với các gói bảo hiểm nhân thọ chất lượng cao
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="service-card" style="background: var(--white); border-radius: 15px; padding: 2rem; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef; transition: all 0.3s ease;">
                                <div class="service-icon mb-3">
                                    <i class="fas fa-chart-line" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                </div>
                                <h3 class="service-title mb-3" style="color: var(--dark); font-weight: bold;">Đầu Tư Tài Chính</h3>
                                <p class="service-description" style="color: var(--gray); line-height: 1.6;">
                                    Tư vấn đầu tư và quản lý tài sản để tối ưu hóa lợi nhuận và giảm thiểu rủi ro
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About Section -->
            <section id="about" style="margin-top: 4rem;">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <h2 style="color: var(--dark); font-weight: bold; margin-bottom: 2rem;">Về VIMARS Financial Services</h2>
                            <p style="color: var(--gray); line-height: 1.8; margin-bottom: 1.5rem;">
                                VIMARS là công ty tài chính hàng đầu tại Việt Nam, chuyên cung cấp các dịch vụ tài chính toàn diện bao gồm vay vốn, bảo hiểm và đầu tư.
                            </p>
                            <p style="color: var(--gray); line-height: 1.8; margin-bottom: 2rem;">
                                Với hơn 10 năm kinh nghiệm trong lĩnh vực tài chính, chúng tôi cam kết mang đến những giải pháp tài chính tối ưu cho khách hàng.
                            </p>
                            <div class="row">
                                <div class="col-6">
                                    <div class="stat-item text-center">
                                        <h3 style="color: var(--primary-color); font-weight: bold; font-size: 2rem;">10+</h3>
                                        <p style="color: var(--gray);">Năm Kinh Nghiệm</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item text-center">
                                        <h3 style="color: var(--primary-color); font-weight: bold; font-size: 2rem;">50K+</h3>
                                        <p style="color: var(--gray);">Khách Hàng</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div style="background: var(--light-green); border-radius: 20px; padding: 3rem; text-align: center;">
                                <h3 style="color: var(--dark); font-weight: bold; margin-bottom: 2rem;">Tại Sao Chọn Chúng Tôi?</h3>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                            <p style="color: var(--gray); font-size: 0.9rem;">Lãi suất thấp</p>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                            <p style="color: var(--gray); font-size: 0.9rem;">Thủ tục nhanh</p>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                            <p style="color: var(--gray); font-size: 0.9rem;">Hỗ trợ 24/7</p>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                                            <p style="color: var(--gray); font-size: 0.9rem;">Bảo mật thông tin</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Calculator Section -->
            <section id="calculator" style="margin-top: 4rem;">
                <div class="container">
                    <h2 class="text-center mb-5" style="color: var(--dark); font-weight: bold;">Tính Toán Khoản Vay</h2>
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div style="background: var(--white); border-radius: 15px; padding: 3rem; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: var(--dark);">
                                            <i class="fas fa-money-bill-wave me-2" style="color: var(--primary-color);"></i>
                                            Số tiền vay (VNĐ)
                                        </label>
                                        <input type="number" class="form-control" id="loanAmount" placeholder="Nhập số tiền vay">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: var(--dark);">
                                            <i class="fas fa-calendar me-2" style="color: var(--primary-color);"></i>
                                            Thời hạn vay (tháng)
                                        </label>
                                        <input type="number" class="form-control" id="loanTerm" placeholder="Nhập thời hạn">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: var(--dark);">
                                            <i class="fas fa-percentage me-2" style="color: var(--primary-color);"></i>
                                            Lãi suất (%/năm)
                                        </label>
                                        <input type="number" class="form-control" id="interestRate" placeholder="Nhập lãi suất">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" style="font-weight: 600; color: var(--dark);">
                                            <i class="fas fa-calculator me-2" style="color: var(--primary-color);"></i>
                                            Hình thức trả nợ
                                        </label>
                                        <select class="form-select" id="paymentType">
                                            <option value="monthly">Trả góp hàng tháng</option>
                                            <option value="quarterly">Trả góp hàng quý</option>
                                            <option value="yearly">Trả góp hàng năm</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button class="btn btn-primary btn-lg" onclick="calculateLoan()" style="background: var(--primary-color); border: none; padding: 1rem 2rem; border-radius: 25px; font-weight: 600;">
                                        <i class="fas fa-calculator me-2"></i>
                                        Tính Toán
                                    </button>
                                </div>
                                <div id="calculationResult" class="mt-4" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div style="background: var(--light-green); border-radius: 10px; padding: 1.5rem; text-align: center;">
                                                <h4 style="color: var(--dark); font-weight: bold;">Tiền gốc hàng tháng</h4>
                                                <p id="monthlyPrincipal" style="color: var(--primary-color); font-size: 1.2rem; font-weight: bold; margin: 0;"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div style="background: var(--light-green); border-radius: 10px; padding: 1.5rem; text-align: center;">
                                                <h4 style="color: var(--dark); font-weight: bold;">Tiền lãi hàng tháng</h4>
                                                <p id="monthlyInterest" style="color: var(--primary-color); font-size: 1.2rem; font-weight: bold; margin: 0;"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div style="background: var(--light-green); border-radius: 10px; padding: 1.5rem; text-align: center;">
                                                <h4 style="color: var(--dark); font-weight: bold;">Tổng tiền trả hàng tháng</h4>
                                                <p id="monthlyPayment" style="color: var(--primary-color); font-size: 1.2rem; font-weight: bold; margin: 0;"></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div style="background: var(--light-green); border-radius: 10px; padding: 1.5rem; text-align: center;">
                                                <h4 style="color: var(--dark); font-weight: bold;">Tổng tiền phải trả</h4>
                                                <p id="totalPayment" style="color: var(--primary-color); font-size: 1.2rem; font-weight: bold; margin: 0;"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="faq-section" style="margin-top: 4rem;">
                <div class="container">
                    <h2 class="text-center mb-4" style="color: var(--dark); font-weight: bold;">Câu Hỏi Thường Gặp</h2>
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="accordion" id="faqAccordion">
                                <!-- FAQ Item 1 -->
                                <div class="accordion-item" style="border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 1rem;">
                                    <h2 class="accordion-header" id="faq1">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1" style="background: var(--white); color: var(--dark); font-weight: 600; border: none; padding: 1.5rem;">
                                            <i class="fas fa-question-circle me-3" style="color: var(--primary-color);"></i>
                                            Có cần người bảo lãnh không?
                                        </button>
                                    </h2>
                                    <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body" style="padding: 1.5rem; color: var(--gray);">
                                            Tùy thuộc vào loại khoản vay và tài sản thế chấp. Với các khoản vay có tài sản đảm bảo như sổ đỏ, xe máy, ô tô thì thường không cần người bảo lãnh. Tuy nhiên, với khoản vay tín chấp có thể cần người bảo lãnh tùy theo mức độ rủi ro.
                                        </div>
                                    </div>
                                </div>

                                <!-- FAQ Item 2 -->
                                <div class="accordion-item" style="border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 1rem;">
                                    <h2 class="accordion-header" id="faq2">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2" style="background: var(--white); color: var(--dark); font-weight: 600; border: none; padding: 1.5rem;">
                                            <i class="fas fa-question-circle me-3" style="color: var(--primary-color);"></i>
                                            Có thu phí dịch vụ không?
                                        </button>
                                    </h2>
                                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body" style="padding: 1.5rem; color: var(--gray);">
                                            Chúng tôi cam kết minh bạch về phí dịch vụ. Tất cả các khoản phí sẽ được thông báo rõ ràng trước khi ký hợp đồng. Phí dịch vụ bao gồm phí thẩm định, phí công chứng và các chi phí liên quan khác.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section" style="margin-top: 4rem; margin-bottom: 4rem;">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="cta-box" style="background: var(--light-green); border-radius: 20px; padding: 3rem; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                <div class="cta-icon mb-4">
                                    <i class="fas fa-headset" style="font-size: 3rem; color: var(--primary-color);"></i>
                                </div>
                                <h2 class="cta-title mb-3" style="color: var(--dark); font-weight: bold; font-size: 2rem;">Vẫn Có Thắc Mắc?</h2>
                                <p class="cta-description mb-4" style="color: var(--gray); font-size: 1.1rem; line-height: 1.6;">
                                    Đội ngũ chuyên viên tư vấn của chúng tôi sẵn sàng giải đáp mọi câu hỏi của bạn 24/7
                                </p>
                                <div class="cta-buttons d-flex justify-content-center gap-3">
                                    <a href="tel:19001234" class="btn btn-primary btn-lg" style="background: var(--primary-color); border: none; padding: 1rem 2rem; border-radius: 25px; font-weight: 600;">
                                        <i class="fas fa-phone me-2"></i>
                                        Gọi Tư Vấn Miễn Phí
                                    </a>
                                    <a href="#" class="btn btn-outline-primary btn-lg" style="border: 2px solid var(--primary-color); color: var(--primary-color); background: transparent; padding: 1rem 2rem; border-radius: 25px; font-weight: 600;">
                                        <i class="fas fa-comments me-2"></i>
                                        Chat Trực Tuyến
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature Cards Section -->
            <section class="feature-cards" style="margin-bottom: 4rem;">
                <div class="container">
                    <h2 class="text-center mb-5" style="color: var(--dark); font-weight: bold;">Quy Trình Đăng Ký Vay</h2>
                    <div class="row">
                        <!-- Feature Card 1 -->
                        <div class="col-lg-4 mb-4">
                            <div class="feature-card" style="background: var(--white); border-radius: 15px; padding: 2rem; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef; transition: all 0.3s ease;">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-file-alt" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                </div>
                                <h3 class="feature-title mb-3" style="color: var(--dark); font-weight: bold;">Chuẩn Bị Giấy Tờ</h3>
                                <p class="feature-description" style="color: var(--gray); line-height: 1.6;">
                                    CMND/CCCD, giấy tờ tài sản thế chấp và chứng minh thu nhập (nếu có)
                                </p>
                            </div>
                        </div>

                        <!-- Feature Card 2 -->
                        <div class="col-lg-4 mb-4">
                            <div class="feature-card" style="background: var(--white); border-radius: 15px; padding: 2rem; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef; transition: all 0.3s ease;">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-calculator" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                </div>
                                <h3 class="feature-title mb-3" style="color: var(--dark); font-weight: bold;">Tính Toán Khoản Vay</h3>
                                <p class="feature-description" style="color: var(--gray); line-height: 1.6;">
                                    Xác định rõ số tiền cần vay và khả năng trả nợ hàng tháng
                                </p>
                            </div>
                        </div>

                        <!-- Feature Card 3 -->
                        <div class="col-lg-4 mb-4">
                            <div class="feature-card" style="background: var(--white); border-radius: 15px; padding: 2rem; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.1); border: 1px solid #e9ecef; transition: all 0.3s ease;">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-gift" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                                </div>
                                <h3 class="feature-title mb-3" style="color: var(--dark); font-weight: bold;">Chọn Sản Phẩm Phù Hợp</h3>
                                <p class="feature-description" style="color: var(--gray); line-height: 1.6;">
                                    Tư vấn với chuyên gia để chọn sản phẩm vay phù hợp nhất
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section id="contact" style="margin-top: 4rem;">
                <div class="container">
                    <h2 class="text-center mb-5" style="color: var(--dark); font-weight: bold;">Liên Hệ Với Chúng Tôi</h2>

                    <!-- Contact Panels -->
                    <div class="contact-panels" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                        <!-- Left Panel: Contact Information -->
                        <div class="contact-panel" style="background: var(--white); border-radius: 15px; padding: 2rem; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border: 1px solid #e9ecef;">
                            <h3 class="panel-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.3rem; font-weight: bold; color: var(--dark); margin-bottom: 1.5rem;">
                                <i class="fas fa-building" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                                Thông Tin Liên Hệ
                            </h3>

                            <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color); font-size: 1.2rem; margin-top: 0.2rem;"></i>
                                <div class="contact-info-content">
                                    <h4 style="font-size: 1rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Địa chỉ văn phòng</h4>
                                    <p style="color: var(--gray); margin: 0; line-height: 1.4;">Tầng 15, Tòa nhà Bitexco, Quận 1, TP.HCM</p>
                                </div>
                            </div>

                            <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                                <i class="fas fa-phone" style="color: var(--primary-color); font-size: 1.2rem; margin-top: 0.2rem;"></i>
                                <div class="contact-info-content">
                                    <h4 style="font-size: 1rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Hotline</h4>
                                    <p style="color: var(--gray); margin: 0; line-height: 1.4;">1900 8888</p>
                                    <p style="font-size: 0.9rem; color: var(--primary-color); margin: 0;">Hỗ trợ 24/7</p>
                                </div>
                            </div>

                            <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                                <i class="fas fa-envelope" style="color: var(--primary-color); font-size: 1.2rem; margin-top: 0.2rem;"></i>
                                <div class="contact-info-content">
                                    <h4 style="font-size: 1rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Email</h4>
                                    <p style="color: var(--gray); margin: 0; line-height: 1.4;">support@vimars.vn</p>
                                </div>
                            </div>

                            <div class="contact-info-item" style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                                <i class="fas fa-clock" style="color: var(--primary-color); font-size: 1.2rem; margin-top: 0.2rem;"></i>
                                <div class="contact-info-content">
                                    <h4 style="font-size: 1rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">Giờ làm việc</h4>
                                    <p style="color: var(--gray); margin: 0; line-height: 1.4;">08:00 - 22:00 (Tất cả các ngày trong tuần)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Contact Form -->
                        <div class="contact-panel" style="background: var(--white); border-radius: 15px; padding: 2rem; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border: 1px solid #e9ecef;">
                            <h3 class="panel-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.3rem; font-weight: bold; color: var(--dark); margin-bottom: 1.5rem;">
                                <i class="fas fa-edit" style="color: var(--primary-color); font-size: 1.5rem;"></i>
                                Gửi Thông Tin Liên Hệ
                            </h3>

                            <!-- Success Message -->
                            <div class="success-message" style="background: var(--light-green); border: 1px solid #c3e6cb; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; display: none; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.2rem;"></i>
                                <p style="color: var(--dark); margin: 0; font-weight: 500;">Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong vòng 15 phút.</p>
                            </div>

                            <form id="contactForm">
                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">
                                        <i class="fas fa-user" style="color: var(--primary-color);"></i>
                                        Họ và tên *
                                    </label>
                                    <input type="text" class="form-control" placeholder="Nhập họ và tên" required>
                                </div>

                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">
                                        <i class="fas fa-phone" style="color: var(--primary-color);"></i>
                                        Số điện thoại *
                                    </label>
                                    <input type="tel" class="form-control" placeholder="Nhập số điện thoại" required>
                                </div>

                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">
                                        <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                                        Email
                                    </label>
                                    <input type="email" class="form-control" placeholder="email@example.com">
                                </div>

                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">
                                        <i class="fas fa-calendar" style="color: var(--primary-color);"></i>
                                        Thời gian liên hệ
                                    </label>
                                    <select class="form-select">
                                        <option selected>Chọn thời gian</option>
                                        <option value="morning">Sáng (08:00 - 12:00)</option>
                                        <option value="afternoon">Chiều (13:00 - 17:00)</option>
                                        <option value="evening">Tối (18:00 - 22:00)</option>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem;">
                                        <i class="fas fa-comment" style="color: var(--primary-color);"></i>
                                        Nội dung tin nhắn
                                    </label>
                                    <textarea class="form-control" rows="4" placeholder="Mô tả nhu cầu vay vốn hoặc thắc mắc của bạn..."></textarea>
                                </div>

                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-primary" style="background: var(--primary-color); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                                        <i class="fas fa-paper-plane"></i>
                                        Gửi Thông Tin
                                    </button>
                                    <button type="reset" class="btn btn-outline-primary" style="border: 2px solid var(--primary-color); color: var(--primary-color); background: transparent; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                                        <i class="fas fa-redo"></i>
                                        Làm Mới
                                    </button>
                                </div>

                                <div class="privacy-message" style="font-size: 0.9rem; color: var(--gray); display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                                    <i class="fas fa-lock" style="color: var(--primary-color);"></i>
                                    <span>Thông tin của bạn được bảo mật 100% và chỉ được sử dụng để tư vấn dịch vụ</span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Support Panel -->
                    <div class="support-panel" style="background: var(--primary-color); border-radius: 15px; padding: 2rem; color: var(--white); box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);">
                        <h3 style="font-size: 1.3rem; font-weight: bold; margin-bottom: 1rem;">Cần Hỗ Trợ Gấp?</h3>
                        <p style="margin-bottom: 1.5rem; opacity: 0.9;">Gọi ngay hotline để được tư vấn miễn phí trong vòng 2 phút</p>
                        <div class="support-buttons" style="display: flex; flex-direction: column; gap: 1rem;">
                            <a href="tel:19008888" class="support-btn" style="background: var(--white); color: var(--primary-color); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; text-decoration: none; text-align: center;">
                                <i class="fas fa-phone"></i>
                                Gọi Ngay: 1900 8888
                            </a>
                            <a href="#home" class="support-btn" style="background: var(--white); color: var(--primary-color); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; text-decoration: none; text-align: center;">
                                <i class="fas fa-file-alt"></i>
                                Đăng Ký Vay Online
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Valuation Result Modal -->
    <div class="modal fade" id="valuationModal" tabindex="-1" aria-labelledby="valuationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: #343a40; color: white; border-radius: 15px 15px 0 0; border: none; padding: 1rem 2rem;">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div style="font-size: 0.9rem;">
                            <span style="margin-right: 2rem;">Tỉ giá ngoại tệ: USD 24</span>
                            <span style="margin-right: 2rem;">Định giá xe</span>
                            <span>Hướng dẫn tải app</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 0;">
                    <!-- Main Content Area -->
                    <div class="row" style="margin: 0;">
                        <!-- Left Column: Car Information -->
                        <div class="col-lg-6" style="padding: 2rem; background: white;">
                            <h4 style="color: var(--dark); font-weight: bold; margin-bottom: 1.5rem;">Thông tin xe của bạn</h4>

                            <div class="car-details" style="margin-bottom: 2rem;">
                                <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                                    <span style="color: var(--gray); font-weight: 500;">Hãng xe:</span>
                                    <span style="color: var(--dark); font-weight: 600;" id="carBrand">cadillac</span>
                                </div>
                                <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                                    <span style="color: var(--gray); font-weight: 500;">Năm sản xuất:</span>
                                    <span style="color: var(--dark); font-weight: 600;" id="carYear">2014</span>
                                </div>
                                <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                                    <span style="color: var(--gray); font-weight: 500;">Tên xe:</span>
                                    <span style="color: var(--dark); font-weight: 600;" id="carModel">escalade esv platinum 6.2 at</span>
                                </div>
                                <div class="detail-row" style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                                    <span style="color: var(--gray); font-weight: 500;">ODO:</span>
                                    <span style="color: var(--dark); font-weight: 600;" id="carOdo">10,000 km</span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; width: 100%;">
                                <i class="fas fa-car me-2"></i>
                                Định giá xe khác
                            </button>
                        </div>

                        <!-- Right Column: EsVIMARSted Value -->
                        <div class="col-lg-6" style="padding: 2rem; background: white;">
                            <h4 style="color: var(--dark); font-weight: bold; margin-bottom: 0.5rem;">Khoảng giá ước tính tại VIMARS</h4>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 2rem;">Tổng hợp từ hơn 350.000 nguồn dữ liệu</p>

                            <div class="esVIMARSted-value" style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%); border-radius: 12px; padding: 2rem; text-align: center; margin-bottom: 2rem;">
                                <h2 style="color: white; font-weight: bold; margin: 0; font-size: 2rem;" id="esVIMARStedValue">1,375,000,000 VNĐ</h2>
                            </div>

                            <div class="car-illustration" style="text-align: center; margin-top: 2rem;">
                                <svg width="200" height="120" viewBox="0 0 200 120" style="opacity: 0.7;">
                                    <!-- Car body -->
                                    <rect x="40" y="60" width="120" height="30" rx="15" fill="#e9ecef" />
                                    <!-- Car roof -->
                                    <rect x="60" y="40" width="80" height="20" rx="10" fill="#dee2e6" />
                                    <!-- Wheels -->
                                    <circle cx="60" cy="90" r="8" fill="#adb5bd" />
                                    <circle cx="140" cy="90" r="8" fill="#adb5bd" />
                                    <!-- City skyline -->
                                    <rect x="10" y="70" width="5" height="20" fill="#ced4da" />
                                    <rect x="20" y="60" width="5" height="30" fill="#ced4da" />
                                    <rect x="30" y="65" width="5" height="25" fill="#ced4da" />
                                    <rect x="180" y="75" width="5" height="15" fill="#ced4da" />
                                    <rect x="170" y="70" width="5" height="20" fill="#ced4da" />
                                    <rect x="160" y="65" width="5" height="25" fill="#ced4da" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Banner -->
                    <div style="background: #495057; padding: 1.5rem 2rem; color: white;">
                        <div class="d-flex align-items-center">
                            <div style="background: #dc3545; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                <i class="fas fa-question" style="color: white;"></i>
                            </div>
                            <div>
                                <h6 style="font-weight: bold; margin: 0; margin-bottom: 0.5rem;">Xoay tiền nhanh từ chiếc xe bạn đang có với VIMARS – SÀN KẾT NỐI TÀI CHÍNH LỚN NHẤT VIỆT NAM</h6>
                                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">VIMARS hỗ trợ khách hàng kết nối với các đơn vị tài chính Top đầu thị trường, thoải mái lựa chọn phương thức phù hợp nhất</p>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison Table -->
                    <div style="padding: 2rem;">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <thead>
                                    <tr>
                                        <th style="border: none; padding: 1rem; font-weight: 600; background: white;"></th>
                                        <th style="border: none; padding: 1rem; font-weight: 600; text-align: center; background: #ff6b35; color: white;">Sàn kết nối tài chính</th>
                                        <th style="border: none; padding: 1rem; font-weight: 600; text-align: center; background: #ff6b35; color: white;">Cửa hàng/Tổ chức cầm đồ</th>
                                        <th style="border: none; padding: 1rem; font-weight: 600; text-align: center; background: #ff6b35; color: white;">Công ty tài chính</th>
                                        <th style="border: none; padding: 1rem; font-weight: 600; text-align: center; background: #ff6b35; color: white;">Ngân hàng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="background: #f8f9fa; font-weight: 600; padding: 1rem; border: 1px solid #dee2e6;">Hạn mức vay</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">962,500,000₫ - 1,100,000,000₫</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">962,500,000₫ - 1,100,000,000₫</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center; color: var(--gray);">N/A</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">962,500,000₫</td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f8f9fa; font-weight: 600; padding: 1rem; border: 1px solid #dee2e6;">Lãi suất tối đa</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">1,33%</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">0.96% - 1.8%</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center; color: var(--gray);">N/A</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">8-10% năm</td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f8f9fa; font-weight: 600; padding: 1rem; border: 1px solid #dee2e6;">Phí dịch vụ</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>Phí dịch vụ: 3,17%</li>
                                                <li>Phí bảo hiểm: 0,75%</li>
                                                <li>Phí tất toán sớm: 3%</li>
                                            </ul>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>Phí dịch vụ: 3,4%</li>
                                                <li>Phí bảo hiểm: 2%</li>
                                                <li>Phí tất toán sớm: 3-8%</li>
                                            </ul>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center; color: var(--gray);">N/A</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>Bảo hiểm thân vỏ</li>
                                                <li>Bảo hiểm nhân thọ bắt buộc</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f8f9fa; font-weight: 600; padding: 1rem; border: 1px solid #dee2e6;">Điều kiện hồ sơ</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>CCCD</li>
                                                <li>Đăng ký xe</li>
                                                <li>Chứng minh công việc</li>
                                                <li>Chứng minh khả năng trả nợ</li>
                                            </ul>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>CCCD</li>
                                                <li>Đăng ký xe</li>
                                                <li>Chứng minh công việc</li>
                                                <li>Chứng minh khả năng trả nợ</li>
                                                <li>Hộ khẩu</li>
                                                <li>Check CIC</li>
                                            </ul>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center; color: var(--gray);">N/A</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>CCCD</li>
                                                <li>Đăng ký xe</li>
                                                <li>Hộ khẩu</li>
                                                <li>Check CIC</li>
                                                <li>Yêu cầu có chống ký</li>
                                                <li>Thẩm định công việc, nơi ở</li>
                                                <li>Chứng minh thu nhập ổn định</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f8f9fa; font-weight: 600; padding: 1rem; border: 1px solid #dee2e6;">Điều kiện tài sản</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>Xe chính chủ</li>
                                                <li>Không quy định niên hạn</li>
                                            </ul>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>Xe chính chủ</li>
                                                <li>Không quy định niên hạn</li>
                                            </ul>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center; color: var(--gray);">N/A</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6;">
                                            <ul style="margin: 0; padding-left: 1rem; font-size: 0.9rem;">
                                                <li>Xe không quá 5 năm</li>
                                                <li>Xe chính chủ</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f8f9fa; font-weight: 600; padding: 1rem; border: 1px solid #dee2e6;">Đăng ký</td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">
                                            <button class="btn btn-light" style="background: #e9ecef; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.9rem; color: #495057;">Đăng ký vay qua VIMARS</button>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center;">
                                            <button class="btn btn-light" style="background: #e9ecef; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.9rem; color: #495057;">Đăng ký vay qua VIMARS</button>
                                        </td>
                                        <td style="padding: 1rem; border: 1px solid #dee2e6; text-align: center; color: var(--gray);">N/A</td>
                                        <td style="padding: 1rem; border: 1rem; border: 1px solid #dee2e6; text-align: center;">
                                            <button class="btn btn-light" style="background: #e9ecef; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.9rem; color: #495057;">Đăng ký vay qua VIMARS</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border: none; padding: 1rem 2rem; background: white; border-radius: 0 0 15px 15px;">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div style="font-size: 0.9rem; color: var(--gray);">
                            <span style="font-weight: bold; color: var(--dark);">VIMARS</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--gray);">
                            © 2017 Bản quyền thuộc về VIMARS
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Sidebar -->
    <div class="floating-sidebar">
        <div class="social-icon plus">
            <i class="fas fa-plus"></i>
        </div>
        <div class="social-icon ai">
            <i class="fas fa-robot"></i>
        </div>
        <div class="social-icon chat">
            <i class="fas fa-comments"></i>
        </div>
        <div class="social-icon zalo">
            <i class="fas fa-phone"></i>
        </div>
        <div class="social-icon phone">
            <i class="fas fa-phone"></i>
        </div>
        <div class="social-icon youtube">
            <i class="fab fa-youtube"></i>
        </div>
        <div class="social-icon facebook">
            <i class="fab fa-facebook-f"></i>
        </div>
        <div class="social-icon camera">
            <i class="fas fa-camera"></i>
        </div>

        <!-- Action Buttons -->
        <button class="action-button" onclick="scrollToTop()">
            <i class="fas fa-arrow-up"></i>
        </button>
        <button class="action-button">
            <i class="fas fa-rocket"></i>
        </button>
        <button class="action-button">
            <i class="fas fa-cog"></i>
        </button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Add smooth scrolling to all links
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

        // Add hover effects to product cards
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add click effects to social icons
        document.querySelectorAll('.social-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Add hover effects to feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add hover effects to CTA box
        document.querySelectorAll('.cta-box').forEach(box => {
            box.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });

            box.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Loan Calculator Function
        function calculateLoan() {
            const loanAmount = parseFloat(document.getElementById('loanAmount').value);
            const loanTerm = parseFloat(document.getElementById('loanTerm').value);
            const interestRate = parseFloat(document.getElementById('interestRate').value);
            const paymentType = document.getElementById('paymentType').value;

            if (!loanAmount || !loanTerm || !interestRate) {
                alert('Vui lòng nhập đầy đủ thông tin!');
                return;
            }

            // Convert annual interest rate to monthly
            const monthlyRate = interestRate / 12 / 100;

            // Calculate monthly payment using loan amortization formula
            const monthlyPayment = (loanAmount * monthlyRate * Math.pow(1 + monthlyRate, loanTerm)) /
                (Math.pow(1 + monthlyRate, loanTerm) - 1);

            const totalPayment = monthlyPayment * loanTerm;
            const totalInterest = totalPayment - loanAmount;
            const monthlyPrincipal = loanAmount / loanTerm;

            // Format numbers with Vietnamese locale
            const formatter = new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            });

            // Display results
            document.getElementById('monthlyPrincipal').textContent = formatter.format(monthlyPrincipal);
            document.getElementById('monthlyInterest').textContent = formatter.format(monthlyPayment - monthlyPrincipal);
            document.getElementById('monthlyPayment').textContent = formatter.format(monthlyPayment);
            document.getElementById('totalPayment').textContent = formatter.format(totalPayment);

            // Show results
            document.getElementById('calculationResult').style.display = 'block';
        }

        // Contact Form Handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Show success message
            const successMessage = document.querySelector('.success-message');
            successMessage.style.display = 'flex';

            // Reset form
            this.reset();

            // Hide success message after 5 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        });

        // Add hover effects to service cards
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Car Valuation Function
        function valueCar() {
            console.log('valueCar function called'); // Debug log

            const vehicleType = document.querySelector('input[name="vehicleType"]:checked');
            const brand = document.querySelector('select');
            const year = document.querySelectorAll('select')[1];
            const model = document.querySelectorAll('select')[2];
            const odo = document.querySelector('input[type="number"]');

            console.log('Form elements:', {
                vehicleType,
                brand,
                year,
                model,
                odo
            }); // Debug log

            if (!brand.value || !year.value || !model.value || !odo.value) {
                alert('Vui lòng nhập đầy đủ thông tin xe!');
                return;
            }

            // Update modal with actual car information
            document.getElementById('carBrand').textContent = brand.value;
            document.getElementById('carYear').textContent = year.value;
            document.getElementById('carModel').textContent = model.value;
            document.getElementById('carOdo').textContent = parseInt(odo.value).toLocaleString('vi-VN') + ' km';

            // Simulate car valuation calculation
            const baseValue = 500000000; // 500 triệu VND base value
            const yearFactor = (2024 - parseInt(year.value)) * 0.1; // Giảm 10% mỗi năm
            const odoFactor = Math.max(0, (parseInt(odo.value) - 50000) / 10000 * 0.05); // Giảm 5% mỗi 10,000km

            const esVIMARStedValue = baseValue * (1 - yearFactor - odoFactor);

            // Format esVIMARSted value
            const formatter = new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND',
                minimumFractionDigits: 0
            });

            document.getElementById('esVIMARStedValue').textContent = formatter.format(esVIMARStedValue);

            console.log('EsVIMARSted value:', esVIMARStedValue); // Debug log

            // Show modal
            try {
                const modalElement = document.getElementById('valuationModal');
                console.log('Modal element:', modalElement); // Debug log

                if (modalElement) {
                    const valuationModal = new bootstrap.Modal(modalElement);
                    valuationModal.show();
                    console.log('Modal should be shown'); // Debug log
                } else {
                    console.error('Modal element not found');
                    alert('Có lỗi khi hiển thị modal. Vui lòng thử lại!');
                }
            } catch (error) {
                console.error('Error showing modal:', error);
                alert('Có lỗi khi hiển thị modal. Vui lòng thử lại!');
            }
        }

        // Add hover effects to valuation form
        document.querySelector('.valuation-form').addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 15px 40px rgba(0,0,0,0.4)';
        });

        document.querySelector('.valuation-form').addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
        });

        // Alternative way to show modal - add event listener to button
        document.addEventListener('DOMContentLoaded', function() {
            const valueCarButton = document.querySelector('button[onclick="valueCar()"]');
            if (valueCarButton) {
                valueCarButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Button clicked via event listener');
                    valueCar();
                });
            }
        });

        // Test modal function
        function testModal() {
            console.log('testModal function called');
            try {
                const modalElement = document.getElementById('valuationModal');
                console.log('Modal element found:', modalElement);

                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    console.log('Modal shown successfully');
                } else {
                    console.error('Modal element not found');
                    alert('Modal element not found!');
                }
            } catch (error) {
                console.error('Error in testModal:', error);
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>

</html>