<?php
// Test file để kiểm tra cấu trúc modular
echo "<h1>Test Loan Applications Modular Structure</h1>";

// Test include các file
try {
    require_once __DIR__ . '/includes/loan-applications-model.php';
    echo "<p style='color: green;'>✓ Model loaded successfully</p>";
    
    require_once __DIR__ . '/includes/loan-applications-controller.php';
    echo "<p style='color: green;'>✓ Controller loaded successfully</p>";
    
    require_once __DIR__ . '/includes/loan-applications-views.php';
    echo "<p style='color: green;'>✓ View loaded successfully</p>";
    
    // Test khởi tạo các class
    $model = new LoanApplicationsModel();
    echo "<p style='color: green;'>✓ Model instantiated successfully</p>";
    
    $controller = new LoanApplicationsController();
    echo "<p style='color: green;'>✓ Controller instantiated successfully</p>";
    
    $view = new LoanApplicationsView();
    echo "<p style='color: green;'>✓ View instantiated successfully</p>";
    
    // Test các method cơ bản
    $customers = $model->getCustomers();
    echo "<p style='color: green;'>✓ getCustomers() method works</p>";
    
    $departments = $model->getDepartments();
    echo "<p style='color: green;'>✓ getDepartments() method works</p>";
    
    $interestRates = $model->getInterestRates();
    echo "<p style='color: green;'>✓ getInterestRates() method works</p>";
    
    echo "<h2>Test Results:</h2>";
    echo "<p>✓ All classes loaded successfully</p>";
    echo "<p>✓ All basic methods working</p>";
    echo "<p>✓ Modular structure is ready to use</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?> 