<?php
// Include các module riêng biệt
require_once __DIR__ . '/includes/loan-applications-model.php';
require_once __DIR__ . '/includes/loan-applications-controller.php';
require_once __DIR__ . '/includes/loan-applications-views.php';

// Khởi tạo controller
$controller = new LoanApplicationsController();

// Xử lý request
$controller->handleRequest();

// Hiển thị giao diện
$controller->renderView();
?> 