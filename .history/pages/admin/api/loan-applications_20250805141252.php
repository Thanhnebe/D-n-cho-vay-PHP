<?php
// Prevent any output before JSON
ob_clean();
ob_start();

// Set error handler to catch all errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Set exception handler
set_exception_handler(function ($e) {
    error_log("Uncaught Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Clear any output
    ob_clean();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi không xác định: ' . $e->getMessage()]);
    exit;
});

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt display errors để không hiển thị HTML
ini_set('log_errors', 1); // Chỉ log errors

// Define root path
$rootPath = dirname(dirname(dirname(__DIR__)));

require_once $rootPath . '/config/config.php';
require_once $rootPath . '/config/database.php';
require_once $rootPath . '/includes/auth.php';
require_once $rootPath . '/includes/functions.php';

// Override error reporting settings from config.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Kiểm tra đăng nhập - tạm thời comment để debug
// if (!isLoggedIn()) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

$db = getDB();

// Debug: Kiểm tra database connection
try {
    if (!$db) {
        throw new Exception('Database instance is null');
    }

    // Test connection
    $test = $db->fetchOne("SELECT 1 as test");
    if (!$test || !isset($test['test'])) {
        throw new Exception('Database connection test failed');
    }

    error_log('Database connection successful');
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

// Debug: Log action
error_log('API Action: ' . $action);

// Clear any previous output
ob_clean();

header('Content-Type: application/json');

// Helper function nếu chưa có
if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

try {
    // Debug: Log action và POST data
    error_log("API Debug - Action: " . $action);
    error_log("API Debug - POST data: " . print_r($_POST, true));
    
    switch ($action) {
        case 'approve_application':
            $applicationId = intval($_POST['application_id']);
            $approvalLevel = intval($_POST['approval_level']);
            $approvedAmount = floatval(str_replace(',', '', $_POST['approved_amount']));
            $comments = $_POST['comments'] ?? '';
            $currentUserId = $_SESSION['user_id'] ?? null;

            if (!$applicationId || !$currentUserId) {
                throw new Exception('Thông tin không hợp lệ');
            }

            // Lấy thông tin đơn vay
            $application = $db->fetchOne("
                SELECT * FROM loan_applications WHERE id = ?
            ", [$applicationId]);

            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            if ($application['status'] !== 'pending') {
                throw new Exception('Đơn vay này đã được xử lý');
            }

            // Kiểm tra quyền phê duyệt
            $userRole = $db->fetchOne("
                SELECT lar.* FROM loan_approval_users lau
                LEFT JOIN loan_approval_roles lar ON lau.role_id = lar.id
                WHERE lau.user_id = ? AND lau.status = 'active'
            ", [$currentUserId]);

            if (!$userRole) {
                throw new Exception('Bạn không có quyền phê duyệt');
            }

            $loanAmount = floatval($application['loan_amount']);
            if ($loanAmount < $userRole['min_amount'] || $loanAmount > $userRole['max_amount']) {
                throw new Exception('Bạn không có quyền phê duyệt khoản vay này');
            }

            // Ghi lịch sử phê duyệt
            $db->query("
                INSERT INTO loan_approvals (application_id, approver_id, approval_level, action, approved_amount, comments)
                VALUES (?, ?, ?, 'approve', ?, ?)
            ", [$applicationId, $currentUserId, $approvalLevel, $approvedAmount, $comments]);

            // Cập nhật trạng thái đơn vay
            $db->query("
                UPDATE loan_applications 
                SET status = 'approved', 
                    current_approval_level = ?, 
                    approved_amount = ?, 
                    decision_notes = ?,
                    final_decision = 'approved',
                    decision_date = CURRENT_DATE
                WHERE id = ?
            ", [$approvalLevel, $approvedAmount, $comments, $applicationId]);

            // Tự động tạo hợp đồng điện tử
            try {
                // Lấy thông tin đầy đủ của đơn vay
                $applicationDetail = $db->fetchOne("
                    SELECT la.*, 
                           c.name as customer_name, 
                           c.phone as customer_phone,
                           c.email as customer_email,
                           c.address as customer_address,
                           ir.description as rate_description,
                           ir.monthly_rate,
                           ir.daily_rate
                    FROM loan_applications la
                    LEFT JOIN customers c ON la.customer_id = c.id
                    LEFT JOIN interest_rates ir ON la.interest_rate_id = ir.id
                    WHERE la.id = ?
                ", [$applicationId]);

                if ($applicationDetail) {
                    // Tính ngày kết thúc dựa trên thời hạn vay
                    $startDate = date('Y-m-d');
                    $endDate = date('Y-m-d', strtotime("+{$applicationDetail['loan_term_months']} months"));

                    // Tạo mã hợp đồng
                    $contractCode = 'CT' . date('Ymd') . str_pad($applicationId, 4, '0', STR_PAD_LEFT);

                    // Dữ liệu hợp đồng điện tử
                    $contractData = [
                        'contract_code' => $contractCode,
                        'application_id' => $applicationId,
                        'customer_id' => $applicationDetail['customer_id'],
                        'asset_id' => $applicationDetail['asset_id'],
                        'loan_amount' => $applicationDetail['loan_amount'],
                        'approved_amount' => $approvedAmount,
                        'interest_rate_id' => $applicationDetail['interest_rate_id'],
                        'monthly_rate' => $applicationDetail['monthly_rate'],
                        'daily_rate' => $applicationDetail['daily_rate'],
                        'loan_term_months' => $applicationDetail['loan_term_months'],
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'active',
                        'disbursement_status' => 'pending',
                        'remaining_balance' => $approvedAmount,
                        'total_paid' => 0.00,
                        'monthly_payment' => $approvedAmount / $applicationDetail['loan_term_months'],
                        'created_by' => $currentUserId,
                        'approved_by' => $currentUserId
                    ];

                    // Thêm vào bảng electronic_contracts
                    $contractId = $db->insert('electronic_contracts', $contractData);

                    if ($contractId) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Phê duyệt thành công và đã tạo hợp đồng điện tử',
                            'contract_id' => $contractId,
                            'contract_code' => $contractCode
                        ]);
                    } else {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Phê duyệt thành công nhưng có lỗi khi tạo hợp đồng điện tử'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Phê duyệt thành công'
                    ]);
                }
            } catch (Exception $e) {
                // Nếu có lỗi khi tạo hợp đồng, vẫn trả về thành công cho việc phê duyệt
                error_log('Error creating electronic contract: ' . $e->getMessage());
                echo json_encode([
                    'success' => true,
                    'message' => 'Phê duyệt thành công nhưng có lỗi khi tạo hợp đồng điện tử'
                ]);
            }
            break;

        case 'reject_application':
            $applicationId = intval($_POST['application_id']);
            $approvalLevel = intval($_POST['approval_level']);
            $comments = $_POST['comments'] ?? '';
            $currentUserId = $_SESSION['user_id'] ?? null;

            if (!$applicationId || !$currentUserId) {
                throw new Exception('Thông tin không hợp lệ');
            }

            // Lấy thông tin đơn vay
            $application = $db->fetchOne("
                SELECT * FROM loan_applications WHERE id = ?
            ", [$applicationId]);

            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            if ($application['status'] !== 'pending') {
                throw new Exception('Đơn vay này đã được xử lý');
            }

            // Ghi lịch sử từ chối
            $db->query("
                INSERT INTO loan_approvals (application_id, approver_id, approval_level, action, comments)
                VALUES (?, ?, ?, 'reject', ?)
            ", [$applicationId, $currentUserId, $approvalLevel, $comments]);

            // Cập nhật trạng thái đơn vay
            $db->query("
                UPDATE loan_applications 
                SET status = 'rejected', 
                    decision_notes = ?,
                    final_decision = 'rejected',
                    decision_date = CURRENT_DATE
                WHERE id = ?
            ", [$comments, $applicationId]);

            echo json_encode([
                'success' => true,
                'message' => 'Từ chối thành công'
            ]);
            break;

        case 'get_application':
            $applicationId = intval($_GET['id']);

            if (!$applicationId) {
                throw new Exception('ID đơn vay không hợp lệ');
            }

            try {
                // Test database connection first
                if (!$db) {
                    throw new Exception('Không thể kết nối database');
                }

                $application = $db->fetchOne("
                    SELECT la.*, 
                           c.name as customer_name, 
                           c.phone as customer_phone,
                           a.name as asset_name,
                           ir.description as rate_description,
                           u.name as created_by_name,
                           d.name as department_name
                    FROM loan_applications la
                    LEFT JOIN customers c ON la.customer_id = c.id
                    LEFT JOIN assets a ON la.asset_id = a.id
                    LEFT JOIN interest_rates ir ON la.interest_rate_id = ir.id
                    LEFT JOIN users u ON la.created_by = u.id
                    LEFT JOIN departments d ON la.department_id = d.id
                    WHERE la.id = ?
                ", [$applicationId]);

                if (!$application) {
                    throw new Exception('Không tìm thấy đơn vay với ID: ' . $applicationId);
                }

                echo json_encode(['success' => true, 'data' => $application]);
            } catch (Exception $e) {
                error_log('API Error in get_application: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;

        case 'get_applications':
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $where = [];
            $params = [];

            // Tìm kiếm
            if (!empty($_GET['search'])) {
                $search = $_GET['search'];
                $where[] = "(la.application_code LIKE ? OR la.customer_name LIKE ? OR la.customer_phone_main LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // Lọc theo trạng thái
            if (!empty($_GET['status'])) {
                $where[] = "la.status = ?";
                $params[] = $_GET['status'];
            }

            // Lọc theo phòng ban
            if (!empty($_GET['department_id'])) {
                $where[] = "la.department_id = ?";
                $params[] = $_GET['department_id'];
            }

            // Lọc theo khoảng thời gian
            if (!empty($_GET['date_range'])) {
                $today = date('Y-m-d');
                switch ($_GET['date_range']) {
                    case 'today':
                        $where[] = "DATE(la.created_at) = ?";
                        $params[] = $today;
                        break;
                    case 'week':
                        $where[] = "la.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                        break;
                    case 'month':
                        $where[] = "la.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                    case 'quarter':
                        $where[] = "la.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
                        break;
                }
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // Đếm tổng số
            $countQuery = "
                SELECT COUNT(*) as total
                FROM loan_applications la
                LEFT JOIN customers c ON la.customer_id = c.id
                LEFT JOIN departments d ON la.department_id = d.id
                $whereClause
            ";
            $total = $db->fetchOne($countQuery, $params)['total'];

            // Lấy danh sách
            $query = "
                SELECT la.*, 
                       c.name as customer_name, 
                       c.phone as customer_phone,
                       a.name as asset_name,
                       ir.description as rate_description,
                       u.name as created_by_name,
                       d.name as department_name
                FROM loan_applications la
                LEFT JOIN customers c ON la.customer_id = c.id
                LEFT JOIN assets a ON la.asset_id = a.id
                LEFT JOIN interest_rates ir ON la.interest_rate_id = ir.id
                LEFT JOIN users u ON la.created_by = u.id
                LEFT JOIN departments d ON la.department_id = d.id
                $whereClause
                ORDER BY la.created_at DESC
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $applications = $db->fetchAll($query, $params);

            echo json_encode([
                'success' => true,
                'data' => $applications,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;

        case 'create_application':
            try {
                // Debug: Log input data
                error_log('Create application input: ' . json_encode($_POST));

                // Kiểm tra POST data
                if (empty($_POST)) {
                    throw new Exception('Không có dữ liệu POST');
                }

                // Kiểm tra các trường bắt buộc
                $requiredFields = ['application_code', 'customer_id', 'loan_amount', 'loan_term_months', 'interest_rate_id'];
                foreach ($requiredFields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("Trường {$field} là bắt buộc");
                    }
                }

                $applicationData = [
                    'application_code' => sanitize_input($_POST['application_code']),
                    'customer_id' => intval($_POST['customer_id']),
                    'asset_id' => $_POST['asset_id'] ? intval($_POST['asset_id']) : null,
                    'loan_amount' => floatval(str_replace(',', '', $_POST['loan_amount'])),
                    'loan_purpose' => sanitize_input($_POST['loan_purpose']),
                    'loan_term_months' => intval($_POST['loan_term_months']),
                    'interest_rate_id' => intval($_POST['interest_rate_id']),
                    'monthly_rate' => floatval($_POST['monthly_rate']),
                    'daily_rate' => floatval($_POST['daily_rate']),
                    'customer_name' => sanitize_input($_POST['customer_name']),
                    'customer_cmnd' => sanitize_input($_POST['customer_cmnd']),
                    'customer_address' => sanitize_input($_POST['customer_address']),
                    'customer_phone_main' => sanitize_input($_POST['customer_phone_main']),
                    'customer_birth_date' => $_POST['customer_birth_date'],
                    'customer_id_issued_place' => sanitize_input($_POST['customer_id_issued_place']),
                    'customer_id_issued_date' => $_POST['customer_id_issued_date'],
                    'customer_email' => sanitize_input($_POST['customer_email']),
                    'customer_job' => sanitize_input($_POST['customer_job']),
                    'customer_income' => $_POST['customer_income'] ? floatval(str_replace(',', '', $_POST['customer_income'])) : null,
                    'customer_company' => sanitize_input($_POST['customer_company']),
                    'asset_name' => sanitize_input($_POST['asset_name']),
                    'asset_quantity' => intval($_POST['asset_quantity']),
                    'asset_license_plate' => sanitize_input($_POST['asset_license_plate']),
                    'asset_frame_number' => sanitize_input($_POST['asset_frame_number']),
                    'asset_engine_number' => sanitize_input($_POST['asset_engine_number']),
                    'asset_registration_number' => sanitize_input($_POST['asset_registration_number']),
                    'asset_registration_date' => $_POST['asset_registration_date'],
                    'asset_value' => $_POST['asset_value'] ? floatval(str_replace(',', '', $_POST['asset_value'])) : null,
                    'asset_brand' => sanitize_input($_POST['asset_brand']),
                    'asset_model' => sanitize_input($_POST['asset_model']),
                    'asset_year' => intval($_POST['asset_year']),
                    'asset_color' => sanitize_input($_POST['asset_color']),
                    'asset_cc' => intval($_POST['asset_cc']),
                    'asset_fuel_type' => sanitize_input($_POST['asset_fuel_type']),
                    'asset_condition' => sanitize_input($_POST['asset_condition']),
                    'asset_description' => sanitize_input($_POST['asset_description']),
                    'emergency_contact_name' => sanitize_input($_POST['emergency_contact_name']),
                    'emergency_contact_phone' => sanitize_input($_POST['emergency_contact_phone']),
                    'emergency_contact_relationship' => sanitize_input($_POST['emergency_contact_relationship']),
                    'emergency_contact_address' => sanitize_input($_POST['emergency_contact_address']),
                    'emergency_contact_note' => sanitize_input($_POST['emergency_contact_note']),
                    'has_health_insurance' => isset($_POST['has_health_insurance']) ? 1 : 0,
                    'has_life_insurance' => isset($_POST['has_life_insurance']) ? 1 : 0,
                    'has_vehicle_insurance' => isset($_POST['has_vehicle_insurance']) ? 1 : 0,
                    'status' => $_POST['status'],
                    'current_approval_level' => intval($_POST['current_approval_level']),
                    'highest_approval_level' => intval($_POST['highest_approval_level']),
                    'total_approval_levels' => intval($_POST['total_approval_levels']),
                    'created_by' => $_SESSION['user_id'] ?? 1,
                    'department_id' => $_POST['department_id'] ? intval($_POST['department_id']) : null,
                    'final_decision' => sanitize_input($_POST['final_decision']),
                    'decision_date' => $_POST['decision_date'] ?: date('Y-m-d'),
                    'approved_amount' => $_POST['approved_amount'] ? intval(str_replace(',', '', $_POST['approved_amount'])) : null,
                    'decision_notes' => sanitize_input($_POST['decision_notes'])
                ];

                // Debug: Log processed data
                error_log('Processed application data: ' . json_encode($applicationData));

                // Validate required fields
                if (empty($applicationData['application_code'])) {
                    throw new Exception('Mã đơn vay không được để trống');
                }
                if (empty($applicationData['customer_id'])) {
                    throw new Exception('Khách hàng không được để trống');
                }
                if ($applicationData['loan_amount'] <= 0) {
                    throw new Exception('Số tiền vay phải lớn hơn 0');
                }

                $applicationId = $db->insert('loan_applications', $applicationData);

                // Debug: Log insert result
                error_log('Insert result: ' . $applicationId);

                if (!$applicationId) {
                    throw new Exception('Không thể tạo đơn vay trong database');
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Tạo đơn vay thành công',
                    'application_id' => $applicationId
                ]);
            } catch (Exception $e) {
                error_log('Error in create_application: ' . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi tạo đơn vay: ' . $e->getMessage()
                ]);
            }
            break;

        case 'update_application':
            $applicationId = intval($_POST['application_id']);
            $applicationData = [
                'application_code' => sanitize_input($_POST['application_code']),
                'customer_id' => intval($_POST['customer_id']),
                'asset_id' => $_POST['asset_id'] ? intval($_POST['asset_id']) : null,
                'loan_amount' => floatval(str_replace(',', '', $_POST['loan_amount'])),
                'loan_purpose' => sanitize_input($_POST['loan_purpose']),
                'loan_term_months' => intval($_POST['loan_term_months']),
                'interest_rate_id' => intval($_POST['interest_rate_id']),
                'monthly_rate' => floatval($_POST['monthly_rate']),
                'daily_rate' => floatval($_POST['daily_rate']),
                'customer_name' => sanitize_input($_POST['customer_name']),
                'customer_cmnd' => sanitize_input($_POST['customer_cmnd']),
                'customer_address' => sanitize_input($_POST['customer_address']),
                'customer_phone_main' => sanitize_input($_POST['customer_phone_main']),
                'customer_birth_date' => $_POST['customer_birth_date'],
                'customer_id_issued_place' => sanitize_input($_POST['customer_id_issued_place']),
                'customer_id_issued_date' => $_POST['customer_id_issued_date'],
                'customer_email' => sanitize_input($_POST['customer_email']),
                'customer_job' => sanitize_input($_POST['customer_job']),
                'customer_income' => $_POST['customer_income'] ? floatval(str_replace(',', '', $_POST['customer_income'])) : null,
                'customer_company' => sanitize_input($_POST['customer_company']),
                'asset_name' => sanitize_input($_POST['asset_name']),
                'asset_quantity' => intval($_POST['asset_quantity']),
                'asset_license_plate' => sanitize_input($_POST['asset_license_plate']),
                'asset_frame_number' => sanitize_input($_POST['asset_frame_number']),
                'asset_engine_number' => sanitize_input($_POST['asset_engine_number']),
                'asset_registration_number' => sanitize_input($_POST['asset_registration_number']),
                'asset_registration_date' => $_POST['asset_registration_date'],
                'asset_value' => $_POST['asset_value'] ? floatval(str_replace(',', '', $_POST['asset_value'])) : null,
                'asset_description' => sanitize_input($_POST['asset_description']),
                'emergency_contact_name' => sanitize_input($_POST['emergency_contact_name']),
                'emergency_contact_phone' => sanitize_input($_POST['emergency_contact_phone']),
                'emergency_contact_relationship' => sanitize_input($_POST['emergency_contact_relationship']),
                'emergency_contact_address' => sanitize_input($_POST['emergency_contact_address']),
                'emergency_contact_note' => sanitize_input($_POST['emergency_contact_note']),
                'has_health_insurance' => isset($_POST['has_health_insurance']) ? 1 : 0,
                'has_life_insurance' => isset($_POST['has_life_insurance']) ? 1 : 0,
                'has_vehicle_insurance' => isset($_POST['has_vehicle_insurance']) ? 1 : 0,
                'status' => $_POST['status'],
                'current_approval_level' => intval($_POST['current_approval_level']),
                'highest_approval_level' => intval($_POST['highest_approval_level']),
                'total_approval_levels' => intval($_POST['total_approval_levels']),
                'department_id' => $_POST['department_id'] ? intval($_POST['department_id']) : null,
                'final_decision' => sanitize_input($_POST['final_decision']),
                'decision_date' => $_POST['decision_date'] ?: date('Y-m-d'),
                'approved_amount' => $_POST['approved_amount'] ? intval(str_replace(',', '', $_POST['approved_amount'])) : null,
                'decision_notes' => sanitize_input($_POST['decision_notes'])
            ];

            // Validate required fields
            if (empty($applicationData['application_code'])) {
                throw new Exception('Mã đơn vay không được để trống');
            }
            if (empty($applicationData['customer_id'])) {
                throw new Exception('Khách hàng không được để trống');
            }
            if ($applicationData['loan_amount'] <= 0) {
                throw new Exception('Số tiền vay phải lớn hơn 0');
            }

            $result = $db->update('loan_applications', $applicationData, 'id = ?', ['id' => $applicationId]);

            if (!$result) {
                throw new Exception('Có lỗi xảy ra khi cập nhật đơn vay');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật đơn vay thành công'
            ]);
            break;

        case 'delete_application':
            $applicationId = intval($_POST['application_id']);

            // Kiểm tra xem đơn vay có tồn tại không
            $application = $db->fetchOne("SELECT id FROM loan_applications WHERE id = ?", [$applicationId]);
            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            $result = $db->delete('loan_applications', 'id = ?', [$applicationId]);

            if (!$result) {
                throw new Exception('Có lỗi xảy ra khi xóa đơn vay');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Xóa đơn vay thành công'
            ]);
            break;

        case 'approve_application':
            $applicationId = intval($_POST['application_id']);
            $approvedAmount = floatval(str_replace(',', '', $_POST['approved_amount']));
            $comments = sanitize_input($_POST['comments']);
            $approvalLevel = intval($_POST['approval_level']);

            // Kiểm tra đơn vay
            $application = $db->fetchOne("SELECT * FROM loan_applications WHERE id = ?", [$applicationId]);
            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            if ($application['status'] !== 'pending') {
                throw new Exception('Đơn vay không ở trạng thái chờ duyệt');
            }

            if ($approvedAmount <= 0) {
                throw new Exception('Số tiền được duyệt phải lớn hơn 0');
            }

            if ($approvedAmount > $application['loan_amount']) {
                throw new Exception('Số tiền được duyệt không được vượt quá số tiền vay');
            }

            // Cập nhật trạng thái đơn vay
            $updateData = [
                'status' => 'approved',
                'approved_amount' => $approvedAmount,
                'decision_notes' => $comments,
                'current_approval_level' => $approvalLevel,
                'highest_approval_level' => $approvalLevel,
                'final_decision' => 'approved',
                'decision_date' => date('Y-m-d')
            ];

            $result = $db->update('loan_applications', $updateData, 'id = ?', ['id' => $applicationId]);

            if ($result) {
                // Ghi log approval
                $approvalData = [
                    'application_id' => $applicationId,
                    'approver_id' => $_SESSION['user_id'] ?? 1,
                    'approval_level' => $approvalLevel,
                    'action' => 'approve',
                    'approved_amount' => $approvedAmount,
                    'comments' => $comments
                ];
                $db->insert('loan_approvals', $approvalData);

                echo json_encode([
                    'success' => true,
                    'message' => 'Duyệt đơn vay thành công'
                ]);
            } else {
                throw new Exception('Có lỗi xảy ra khi duyệt đơn vay');
            }
            break;

        case 'reject_application':
            $applicationId = intval($_POST['application_id']);
            $comments = sanitize_input($_POST['comments']);
            $approvalLevel = intval($_POST['approval_level']);

            // Kiểm tra đơn vay
            $application = $db->fetchOne("SELECT * FROM loan_applications WHERE id = ?", [$applicationId]);
            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            if ($application['status'] !== 'pending') {
                throw new Exception('Đơn vay không ở trạng thái chờ duyệt');
            }

            if (empty($comments)) {
                throw new Exception('Lý do từ chối không được để trống');
            }

            // Cập nhật trạng thái đơn vay
            $updateData = [
                'status' => 'rejected',
                'decision_notes' => $comments,
                'current_approval_level' => $approvalLevel,
                'highest_approval_level' => $approvalLevel,
                'final_decision' => 'rejected',
                'decision_date' => date('Y-m-d')
            ];

            $result = $db->update('loan_applications', $updateData, 'id = ?', ['id' => $applicationId]);

            if ($result) {
                // Ghi log approval
                $approvalData = [
                    'application_id' => $applicationId,
                    'approver_id' => $_SESSION['user_id'] ?? 1,
                    'approval_level' => $approvalLevel,
                    'action' => 'reject',
                    'comments' => $comments
                ];
                $db->insert('loan_approvals', $approvalData);

                echo json_encode([
                    'success' => true,
                    'message' => 'Từ chối đơn vay thành công'
                ]);
            } else {
                throw new Exception('Có lỗi xảy ra khi từ chối đơn vay');
            }
            break;

        case 'get_statistics':
            // Thống kê tổng đơn vay
            $totalApplications = $db->fetchOne("SELECT COUNT(*) as total FROM loan_applications")['total'];

            // Thống kê theo trạng thái
            $statusStats = $db->fetchAll("
                SELECT status, COUNT(*) as count 
                FROM loan_applications 
                GROUP BY status
            ");

            // Thống kê theo phòng ban
            $departmentStats = $db->fetchAll("
                SELECT d.name, COUNT(*) as count 
                FROM loan_applications la
                LEFT JOIN departments d ON la.department_id = d.id
                GROUP BY la.department_id, d.name
            ");

            // Tổng giá trị đơn vay
            $totalValue = $db->fetchOne("SELECT SUM(loan_amount) as total FROM loan_applications")['total'] ?? 0;

            // Tổng giá trị đã duyệt
            $totalApproved = $db->fetchOne("SELECT SUM(approved_amount) as total FROM loan_applications WHERE status = 'approved'")['total'] ?? 0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_applications' => $totalApplications,
                    'status_stats' => $statusStats,
                    'department_stats' => $departmentStats,
                    'total_value' => $totalValue,
                    'total_approved' => $totalApproved
                ]
            ]);
            break;

        case 'get_customers':
            $customers = $db->fetchAll("SELECT id, name, phone FROM customers ORDER BY name");
            echo json_encode(['success' => true, 'data' => $customers]);
            break;

        case 'get_assets':
            $assets = $db->fetchAll("SELECT id, name FROM assets WHERE status = 'available' ORDER BY name");
            echo json_encode(['success' => true, 'data' => $assets]);
            break;

        case 'get_interest_rates':
            $rates = $db->fetchAll("SELECT id, description, monthly_rate, daily_rate FROM interest_rates WHERE status = 'active' ORDER BY description");
            echo json_encode(['success' => true, 'data' => $rates]);
            break;

        case 'get_users':
            $users = $db->fetchAll("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");
            echo json_encode(['success' => true, 'data' => $users]);
            break;

        case 'get_departments':
            $departments = $db->fetchAll("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name");
            echo json_encode(['success' => true, 'data' => $departments]);
            break;

        case 'get_approval_history':
            $applicationId = intval($_GET['application_id']);
            $approvals = $db->fetchAll("
                SELECT la.*, u.name as approver_name
                FROM loan_approvals la
                LEFT JOIN users u ON la.approver_id = u.id
                WHERE la.application_id = ?
                ORDER BY la.approval_date DESC
            ", [$applicationId]);

            echo json_encode(['success' => true, 'data' => $approvals]);
            break;

        case 'check_permission':
            $applicationId = intval($_GET['application_id']);
            $userId = $_SESSION['user_id'] ?? 0;

            // Get application details
            $application = $db->fetchOne("
                SELECT la.*, c.phone as customer_phone
                FROM loan_applications la
                LEFT JOIN customers c ON la.customer_id = c.id
                WHERE la.id = ?
            ", [$applicationId]);

            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            // Check if user has approval role
            $userRole = $db->fetchOne("
                SELECT lau.role_id, lar.name as role_name, lar.approval_order, lar.min_amount, lar.max_amount
                FROM loan_approval_users lau
                LEFT JOIN loan_approval_roles lar ON lau.role_id = lar.id
                WHERE lau.user_id = ? AND lau.status = 'active'
            ", [$userId]);

            if (!$userRole) {
                echo json_encode([
                    'success' => true,
                    'can_approve' => false,
                    'message' => 'Bạn không có quyền phê duyệt đơn vay'
                ]);
                break;
            }

            // Check if loan amount is within user's approval limit
            $loanAmount = floatval($application['loan_amount']);
            $canApprove = ($loanAmount >= $userRole['min_amount'] && $loanAmount <= $userRole['max_amount']);

            echo json_encode([
                'success' => true,
                'can_approve' => $canApprove,
                'approval_level' => $userRole['approval_order'],
                'role_name' => $userRole['role_name'],
                'message' => $canApprove ? 'Có quyền phê duyệt' : 'Số tiền vượt quá hạn mức phê duyệt'
            ]);
            break;

        case 'approve_with_contract':
            $applicationId = intval($_POST['application_id']);
            $approvedAmount = floatval(str_replace(',', '', $_POST['approved_amount']));
            $comments = sanitize_input($_POST['comments']);
            $approvalLevel = intval($_POST['approval_level']);
            $userId = $_SESSION['user_id'] ?? 0;

            // Get application details
            $application = $db->fetchOne("
                SELECT la.*, c.phone as customer_phone, c.name as customer_name
                FROM loan_applications la
                LEFT JOIN customers c ON la.customer_id = c.id
                WHERE la.id = ?
            ", [$applicationId]);

            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            // Check permission again
            $userRole = $db->fetchOne("
                SELECT lau.role_id, lar.name as role_name, lar.approval_order, lar.min_amount, lar.max_amount
                FROM loan_approval_users lau
                LEFT JOIN loan_approval_roles lar ON lau.role_id = lar.id
                WHERE lau.user_id = ? AND lau.status = 'active'
            ", [$userId]);

            if (!$userRole) {
                throw new Exception('Bạn không có quyền phê duyệt đơn vay');
            }

            $loanAmount = floatval($application['loan_amount']);
            if ($loanAmount < $userRole['min_amount'] || $loanAmount > $userRole['max_amount']) {
                throw new Exception('Số tiền vượt quá hạn mức phê duyệt');
            }

            // Start transaction
            $db->beginTransaction();

            try {
                // Update application status
                $updateData = [
                    'status' => 'approved',
                    'approved_amount' => $approvedAmount,
                    'decision_notes' => $comments,
                    'current_approval_level' => $approvalLevel,
                    'highest_approval_level' => $approvalLevel,
                    'final_decision' => 'approved',
                    'decision_date' => date('Y-m-d')
                ];

                $result = $db->update('loan_applications', $updateData, 'id = ?', ['id' => $applicationId]);

                if (!$result) {
                    throw new Exception('Có lỗi xảy ra khi cập nhật đơn vay');
                }

                // Log approval
                $approvalData = [
                    'application_id' => $applicationId,
                    'approver_id' => $userId,
                    'approval_level' => $approvalLevel,
                    'action' => 'approve',
                    'approved_amount' => $approvedAmount,
                    'comments' => $comments
                ];

                $db->insert('loan_approvals', $approvalData);

                // Create electronic contract
                $contractCode = 'CT' . date('Ymd') . rand(1000, 9999);
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d', strtotime('+' . $application['loan_term_months'] . ' months'));

                $contractData = [
                    'contract_code' => $contractCode,
                    'application_id' => $applicationId,
                    'customer_id' => $application['customer_id'],
                    'asset_id' => $application['asset_id'],
                    'loan_amount' => $application['loan_amount'],
                    'approved_amount' => $approvedAmount,
                    'interest_rate_id' => $application['interest_rate_id'],
                    'monthly_rate' => $application['monthly_rate'],
                    'daily_rate' => $application['daily_rate'],
                    'loan_term_months' => $application['loan_term_months'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'draft',
                    'created_by' => $userId,
                    'approved_by' => $userId
                ];

                $contractId = $db->insert('electronic_contracts', $contractData);

                if (!$contractId) {
                    throw new Exception('Có lỗi xảy ra khi tạo hợp đồng điện tử');
                }

                // Generate and send OTP
                $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 minute'));

                $otpData = [
                    'application_id' => $applicationId,
                    'otp_code' => $otpCode,
                    'phone_number' => $application['customer_phone_main'],
                    'status' => 'sent',
                    'expires_at' => $expiresAt,
                    'customer_id' => $application['customer_id'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ];

                $db->insert('otp_logs', $otpData);

                // Update application with OTP
                $db->update('loan_applications', [
                    'otp_code' => $otpCode,
                    'otp_expires_at' => $expiresAt
                ], 'id = ?', ['id' => $applicationId]);

                $db->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Phê duyệt thành công và đã gửi OTP',
                    'contract_code' => $contractCode,
                    'otp_sent' => true
                ]);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;

        case 'verify_otp':
            $input = json_decode(file_get_contents('php://input'), true);
            $applicationId = intval($input['application_id']);
            $otpCode = $input['otp_code'];

            // Get application and OTP details
            $application = $db->fetchOne("
                SELECT la.*, ol.otp_code as stored_otp, ol.expires_at, ol.status as otp_status
                FROM loan_applications la
                LEFT JOIN otp_logs ol ON la.id = ol.application_id AND ol.status = 'sent'
                WHERE la.id = ?
                ORDER BY ol.sent_at DESC
                LIMIT 1
            ", [$applicationId]);

            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            if ($application['stored_otp'] !== $otpCode) {
                throw new Exception('Mã OTP không đúng');
            }

            if (strtotime($application['expires_at']) < time()) {
                throw new Exception('Mã OTP đã hết hạn');
            }

            if ($application['otp_status'] !== 'sent') {
                throw new Exception('Mã OTP đã được sử dụng');
            }

            // Start transaction
            $db->beginTransaction();

            try {
                // Update OTP status
                $db->update('otp_logs', [
                    'status' => 'verified',
                    'verified_at' => date('Y-m-d H:i:s')
                ], 'application_id = ? AND otp_code = ?', [$applicationId, $otpCode]);

                // Update application OTP verification
                $db->update('loan_applications', [
                    'otp_verified_at' => date('Y-m-d H:i:s')
                ], 'id = ?', ['id' => $applicationId]);

                // Update electronic contract status
                $db->update('electronic_contracts', [
                    'status' => 'active',
                    'customer_signature' => 'Đã xác thực qua OTP',
                    'signed_date' => date('Y-m-d H:i:s')
                ], 'application_id = ?', ['id' => $applicationId]);

                $db->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Xác thực OTP thành công'
                ]);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;

        case 'resend_otp':
            $input = json_decode(file_get_contents('php://input'), true);
            $applicationId = intval($input['application_id']);

            // Get application details
            $application = $db->fetchOne("
                SELECT la.*, c.phone as customer_phone
                FROM loan_applications la
                LEFT JOIN customers c ON la.customer_id = c.id
                WHERE la.id = ?
            ", [$applicationId]);

            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            // Generate new OTP
            $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 minute'));

            $otpData = [
                'application_id' => $applicationId,
                'otp_code' => $otpCode,
                'phone_number' => $application['customer_phone_main'],
                'status' => 'sent',
                'expires_at' => $expiresAt,
                'customer_id' => $application['customer_id'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];

            $db->insert('otp_logs', $otpData);

            // Update application with new OTP
            $db->update('loan_applications', [
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt
            ], 'id = ?', ['id' => $applicationId]);

            echo json_encode([
                'success' => true,
                'message' => 'Đã gửi lại mã OTP'
            ]);
            break;

        case 'edit':
            $applicationId = intval($_POST['application_id']);
            $currentUserId = $_SESSION['user_id'] ?? null;

            if (!$applicationId || !$currentUserId) {
                throw new Exception('Thông tin không hợp lệ');
            }

            // Lấy thông tin đơn vay hiện tại
            $currentApplication = $db->fetchOne("
                SELECT * FROM loan_applications WHERE id = ?
            ", [$applicationId]);

            if (!$currentApplication) {
                throw new Exception('Không tìm thấy đơn vay');
            }

            // Chỉ cho phép chỉnh sửa đơn vay ở trạng thái pending hoặc draft
            if (!in_array($currentApplication['status'], ['pending', 'draft'])) {
                throw new Exception('Chỉ có thể chỉnh sửa đơn vay chưa được xử lý');
            }

            // Chuẩn bị dữ liệu cập nhật
            $updateData = [
                'customer_name' => sanitize_input($_POST['customer_name']),
                'customer_cmnd' => sanitize_input($_POST['customer_cmnd']),
                'customer_phone_main' => sanitize_input($_POST['customer_phone_main']),
                'customer_email' => sanitize_input($_POST['customer_email']),
                'customer_birth_date' => $_POST['customer_birth_date'],
                'customer_id_issued_place' => sanitize_input($_POST['customer_id_issued_place']),
                'customer_id_issued_date' => $_POST['customer_id_issued_date'],
                'customer_job' => sanitize_input($_POST['customer_job']),
                'customer_income' => floatval(str_replace(',', '', $_POST['customer_income'])),
                'customer_company' => sanitize_input($_POST['customer_company']),
                'customer_address' => sanitize_input($_POST['customer_address']),
                'loan_amount' => floatval(str_replace(',', '', $_POST['loan_amount'])),
                'loan_term_months' => intval($_POST['loan_term_months']),
                'loan_purpose' => sanitize_input($_POST['loan_purpose']),
                'interest_rate_id' => intval($_POST['interest_rate_id']),
                'monthly_rate' => floatval($_POST['monthly_rate']),
                'daily_rate' => floatval($_POST['daily_rate']),
                'asset_name' => sanitize_input($_POST['asset_name']),
                'asset_quantity' => intval($_POST['asset_quantity']),
                'asset_value' => floatval(str_replace(',', '', $_POST['asset_value'])),
                'asset_license_plate' => sanitize_input($_POST['asset_license_plate']),
                'asset_frame_number' => sanitize_input($_POST['asset_frame_number']),
                'asset_engine_number' => sanitize_input($_POST['asset_engine_number']),
                'asset_registration_number' => sanitize_input($_POST['asset_registration_number']),
                'asset_registration_date' => $_POST['asset_registration_date'],
                'asset_brand' => sanitize_input($_POST['asset_brand']),
                'asset_model' => sanitize_input($_POST['asset_model']),
                'asset_year' => intval($_POST['asset_year']),
                'asset_fuel_type' => sanitize_input($_POST['asset_fuel_type']),
                'asset_color' => sanitize_input($_POST['asset_color']),
                'asset_cc' => sanitize_input($_POST['asset_cc']),
                'asset_condition' => sanitize_input($_POST['asset_condition']),
                'asset_description' => sanitize_input($_POST['asset_description']),
                'emergency_contact_name' => sanitize_input($_POST['emergency_contact_name']),
                'emergency_contact_phone' => sanitize_input($_POST['emergency_contact_phone']),
                'emergency_contact_relationship' => sanitize_input($_POST['emergency_contact_relationship']),
                'emergency_contact_address' => sanitize_input($_POST['emergency_contact_address']),
                'emergency_contact_note' => sanitize_input($_POST['emergency_contact_note']),
                'has_health_insurance' => isset($_POST['has_health_insurance']) ? 1 : 0,
                'has_life_insurance' => isset($_POST['has_life_insurance']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Cập nhật đơn vay
            $result = $db->update('loan_applications', $updateData, 'id = ?', ['id' => $applicationId]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật đơn vay thành công'
                ]);
            } else {
                throw new Exception('Có lỗi xảy ra khi cập nhật đơn vay');
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
            break;
    }
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    error_log('API Error Stack: ' . $e->getTraceAsString());

    // Clear any output
    ob_clean();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log('PHP Error: ' . $e->getMessage());
    error_log('PHP Error Stack: ' . $e->getTraceAsString());

    // Clear any output
    ob_clean();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi PHP: ' . $e->getMessage()]);
} catch (Throwable $e) {
    error_log('General Error: ' . $e->getMessage());
    error_log('General Error Stack: ' . $e->getTraceAsString());

    // Clear any output
    ob_clean();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi chung: ' . $e->getMessage()]);
}
