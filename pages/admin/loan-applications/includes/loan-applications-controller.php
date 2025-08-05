<?php
/**
 * Loan Applications Controller
 * Xử lý logic nghiệp vụ và điều hướng
 */
class LoanApplicationsController {
    private $model;
    private $view;
    private $message = '';
    private $messageType = '';
    
    public function __construct() {
        $this->model = new LoanApplicationsModel();
        $this->view = new LoanApplicationsView();
    }
    
    /**
     * Xử lý request chính
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? 'list';
        
        // Xử lý POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest($action);
        }
        
        // Xử lý GET requests
        $this->handleGetRequest($action);
    }
    
    /**
     * Xử lý POST requests
     */
    private function handlePostRequest($action) {
        switch ($action) {
            case 'add':
                $this->handleAddApplication();
                break;
            case 'edit':
                $this->handleEditApplication();
                break;
            case 'delete':
                $this->handleDeleteApplication();
                break;
            case 'approve':
                $this->handleApproveApplication();
                break;
            case 'reject':
                $this->handleRejectApplication();
                break;
            case 'approve_with_contract':
                $this->handleApproveWithContract();
                break;
        }
    }
    
    /**
     * Xử lý GET requests
     */
    private function handleGetRequest($action) {
        switch ($action) {
            case 'list':
                $this->loadApplicationsList();
                break;
            case 'edit':
                $this->loadApplicationForEdit();
                break;
            case 'detail':
                $this->loadApplicationDetail();
                break;
        }
    }
    
    /**
     * Xử lý thêm đơn vay mới
     */
    private function handleAddApplication() {
        try {
            $data = $this->prepareApplicationData($_POST);
            $applicationId = $this->model->createApplication($data);
            
            if ($applicationId) {
                $this->message = 'Tạo đơn vay thành công!';
                $this->messageType = 'success';
                
                // Hiển thị modal phê duyệt
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showApprovalModal(" . $applicationId . ");
                    });
                </script>";
            } else {
                $this->message = 'Có lỗi xảy ra khi tạo đơn vay!';
                $this->messageType = 'error';
            }
        } catch (Exception $e) {
            $this->message = 'Lỗi: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    /**
     * Xử lý sửa đơn vay
     */
    private function handleEditApplication() {
        try {
            $applicationId = $_POST['application_id'];
            $data = $this->prepareApplicationData($_POST);
            
            $result = $this->model->updateApplication($applicationId, $data);
            
            if ($result) {
                $this->message = 'Cập nhật đơn vay thành công!';
                $this->messageType = 'success';
            } else {
                $this->message = 'Có lỗi xảy ra khi cập nhật đơn vay!';
                $this->messageType = 'error';
            }
        } catch (Exception $e) {
            $this->message = 'Lỗi: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    /**
     * Xử lý xóa đơn vay
     */
    private function handleDeleteApplication() {
        try {
            $applicationId = $_POST['application_id'];
            $result = $this->model->deleteApplication($applicationId);
            
            if ($result) {
                $this->message = 'Xóa đơn vay thành công!';
                $this->messageType = 'success';
            } else {
                $this->message = 'Có lỗi xảy ra khi xóa đơn vay!';
                $this->messageType = 'error';
            }
        } catch (Exception $e) {
            $this->message = 'Lỗi: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    /**
     * Xử lý phê duyệt đơn vay
     */
    private function handleApproveApplication() {
        try {
            $applicationId = $_POST['application_id'];
            $data = [
                'approved_amount' => floatval(str_replace(',', '', $_POST['approved_amount'])),
                'comments' => sanitize_input($_POST['comments']),
                'approval_level' => intval($_POST['approval_level']),
                'approver_id' => $_SESSION['user_id'] ?? 1
            ];
            
            $result = $this->model->approveApplication($applicationId, $data);
            
            if ($result) {
                $this->message = 'Phê duyệt đơn vay thành công!';
                $this->messageType = 'success';
            } else {
                $this->message = 'Có lỗi xảy ra khi phê duyệt đơn vay!';
                $this->messageType = 'error';
            }
        } catch (Exception $e) {
            $this->message = 'Lỗi: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    /**
     * Xử lý từ chối đơn vay
     */
    private function handleRejectApplication() {
        try {
            $applicationId = $_POST['application_id'];
            $data = [
                'reject_reason' => sanitize_input($_POST['reject_reason']),
                'reject_comments' => sanitize_input($_POST['reject_comments']),
                'reject_level' => intval($_POST['reject_level']),
                'approver_id' => $_SESSION['user_id'] ?? 1
            ];
            
            // Validate required fields
            if (empty($data['reject_reason'])) {
                $this->message = 'Vui lòng chọn lý do từ chối!';
                $this->messageType = 'error';
                return;
            }
            
            if (empty($data['reject_comments'])) {
                $this->message = 'Vui lòng nhập ghi chú chi tiết!';
                $this->messageType = 'error';
                return;
            }
            
            $result = $this->model->rejectApplication($applicationId, $data);
            
            if ($result) {
                $this->message = 'Từ chối đơn vay thành công!';
                $this->messageType = 'success';
                
                // Return JSON response for AJAX requests
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $this->message]);
                    exit;
                }
            } else {
                $this->message = 'Có lỗi xảy ra khi từ chối đơn vay!';
                $this->messageType = 'error';
                
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $this->message]);
                    exit;
                }
            }
        } catch (Exception $e) {
            $this->message = 'Lỗi: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    /**
     * Xử lý phê duyệt với hợp đồng điện tử
     */
    private function handleApproveWithContract() {
        try {
            $applicationId = intval($_POST['application_id']);
            $approvedAmount = floatval(str_replace(',', '', $_POST['approved_amount']));
            $comments = sanitize_input($_POST['comments']);
            $approvalLevel = intval($_POST['approval_level']);
            $userId = $_SESSION['user_id'] ?? 1;
            
            // Lấy thông tin đơn vay
            $application = $this->model->getApplicationById($applicationId);
            
            if (!$application) {
                throw new Exception('Không tìm thấy đơn vay');
            }
            
            // Phê duyệt đơn vay
            $approvalData = [
                'approved_amount' => $approvedAmount,
                'comments' => $comments,
                'approval_level' => $approvalLevel,
                'approver_id' => $userId
            ];
            
            $this->model->approveApplication($applicationId, $approvalData);
            
            // Tạo hợp đồng điện tử
            $contractData = $this->prepareContractData($application, $approvedAmount, $userId);
            $contractId = $this->createElectronicContract($contractData);
            
            $this->message = 'Phê duyệt đơn vay và tạo hợp đồng điện tử thành công!';
            $this->messageType = 'success';
            
            // Redirect đến trang hợp đồng điện tử
            echo "<script>
                setTimeout(function() {
                    window.location.href = '?page=electronic-contracts&action=edit&id=" . $contractId . "&from_approval=1';
                }, 2000);
            </script>";
            
        } catch (Exception $e) {
            $this->message = 'Lỗi: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    /**
     * Chuẩn bị dữ liệu đơn vay
     */
    private function prepareApplicationData($postData) {
        return [
            'application_code' => sanitize_input($postData['application_code'] ?? ''),
            'customer_id' => intval($postData['customer_id']),
            'asset_id' => $postData['asset_id'] ? intval($postData['asset_id']) : null,
            'loan_amount' => floatval(str_replace(',', '', $postData['loan_amount'])),
            'loan_purpose' => sanitize_input($postData['loan_purpose']),
            'loan_term_months' => intval($postData['loan_term_months']),
            'interest_rate_id' => intval($postData['interest_rate_id']),
            'monthly_rate' => floatval($postData['monthly_rate']),
            'daily_rate' => floatval($postData['daily_rate']),
            'customer_name' => sanitize_input($postData['customer_name']),
            'customer_cmnd' => sanitize_input($postData['customer_cmnd']),
            'customer_address' => sanitize_input($postData['customer_address']),
            'customer_phone_main' => sanitize_input($postData['customer_phone_main']),
            'customer_birth_date' => $postData['customer_birth_date'],
            'customer_id_issued_place' => sanitize_input($postData['customer_id_issued_place']),
            'customer_id_issued_date' => $postData['customer_id_issued_date'],
            'customer_email' => sanitize_input($postData['customer_email']),
            'customer_job' => sanitize_input($postData['customer_job']),
            'customer_income' => $postData['customer_income'] ? floatval(str_replace(',', '', $postData['customer_income'])) : null,
            'customer_company' => sanitize_input($postData['customer_company']),
            'asset_name' => sanitize_input($postData['asset_name']),
            'asset_quantity' => intval($postData['asset_quantity']),
            'asset_license_plate' => sanitize_input($postData['asset_license_plate']),
            'asset_frame_number' => sanitize_input($postData['asset_frame_number']),
            'asset_engine_number' => sanitize_input($postData['asset_engine_number']),
            'asset_registration_number' => sanitize_input($postData['asset_registration_number']),
            'asset_registration_date' => $postData['asset_registration_date'],
            'asset_value' => $postData['asset_value'] ? floatval(str_replace(',', '', $postData['asset_value'])) : null,
            'asset_condition' => sanitize_input($postData['asset_condition']),
            'asset_brand' => sanitize_input($postData['asset_brand']),
            'asset_model' => sanitize_input($postData['asset_model']),
            'asset_year' => $postData['asset_year'] ? intval($postData['asset_year']) : null,
            'asset_color' => sanitize_input($postData['asset_color']),
            'asset_cc' => $postData['asset_cc'] ? intval($postData['asset_cc']) : null,
            'asset_fuel_type' => sanitize_input($postData['asset_fuel_type']),
            'asset_description' => sanitize_input($postData['asset_description']),
            'emergency_contact_name' => sanitize_input($postData['emergency_contact_name']),
            'emergency_contact_phone' => sanitize_input($postData['emergency_contact_phone']),
            'emergency_contact_relationship' => sanitize_input($postData['emergency_contact_relationship']),
            'emergency_contact_address' => sanitize_input($postData['emergency_contact_address']),
            'emergency_contact_note' => sanitize_input($postData['emergency_contact_note']),
            'has_health_insurance' => isset($postData['has_health_insurance']) ? 1 : 0,
            'has_life_insurance' => isset($postData['has_life_insurance']) ? 1 : 0,
            'has_vehicle_insurance' => isset($postData['has_vehicle_insurance']) ? 1 : 0,
            'status' => $postData['status'],
            'current_approval_level' => intval($postData['current_approval_level']),
            'highest_approval_level' => intval($postData['highest_approval_level']),
            'total_approval_levels' => intval($postData['total_approval_levels']),
            'created_by' => $_SESSION['user_id'] ?? 1,
            'department_id' => $postData['department_id'] ? intval($postData['department_id']) : null,
            'final_decision' => sanitize_input($postData['final_decision']),
            'decision_date' => $postData['decision_date'] ?: date('Y-m-d'),
            'approved_amount' => $postData['approved_amount'] ? intval(str_replace(',', '', $postData['approved_amount'])) : null,
            'decision_notes' => sanitize_input($postData['decision_notes'])
        ];
    }
    
    /**
     * Chuẩn bị dữ liệu hợp đồng điện tử
     */
    private function prepareContractData($application, $approvedAmount, $userId) {
        $contractCode = 'CT' . date('Ymd') . rand(1000, 9999);
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+' . $application['loan_term_months'] . ' months'));
        
        return [
            'contract_code' => $contractCode,
            'application_id' => $application['id'],
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
            'disbursement_status' => 'pending',
            'remaining_balance' => $approvedAmount,
            'total_paid' => 0.00,
            'created_by' => $userId,
            'approved_by' => $userId
        ];
    }
    
    /**
     * Tạo hợp đồng điện tử
     */
    private function createElectronicContract($contractData) {
        $db = getDB();
        return $db->insert('electronic_contracts', $contractData);
    }
    
    /**
     * Load danh sách đơn vay
     */
    private function loadApplicationsList() {
        $this->view->applications = $this->model->getAllApplications();
        $this->view->customers = $this->model->getCustomers();
        $this->view->assets = $this->model->getAssets();
        $this->view->interestRates = $this->model->getInterestRates();
        $this->view->users = $this->model->getUsers();
        $this->view->departments = $this->model->getDepartments();
        
        // Debug: Kiểm tra dữ liệu
        error_log('Applications count: ' . count($this->view->applications));
        error_log('Customers count: ' . count($this->view->customers));
        error_log('Assets count: ' . count($this->view->assets));
    }
    
    /**
     * Load đơn vay để edit
     */
    private function loadApplicationForEdit() {
        if (isset($_GET['id'])) {
            $this->view->application = $this->model->getApplicationById($_GET['id']);
        }
    }
    
    /**
     * Load chi tiết đơn vay
     */
    private function loadApplicationDetail() {
        if (isset($_GET['id'])) {
            $this->view->application = $this->model->getApplicationById($_GET['id']);
        }
    }
    
    /**
     * Render view
     */
    public function renderView() {
        $this->view->message = $this->message;
        $this->view->messageType = $this->messageType;
        $this->view->render();
    }
}
?> 