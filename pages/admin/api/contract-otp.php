<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/ContractOTP.php';
require_once '../../../includes/ContractGenerator.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$contractOTP = new ContractOTP($db);
$contractGenerator = new ContractGenerator($db);

try {
    switch ($action) {
        case 'generate_otp':
            if (!isset($_POST['contract_id']) || !isset($_POST['customer_id'])) {
                throw new Exception('Thiếu thông tin contract_id hoặc customer_id');
            }
            
            $contractId = intval($_POST['contract_id']);
            $customerId = intval($_POST['customer_id']);
            $sendMethod = $_POST['send_method'] ?? 'both'; // sms, email, both
            $expirySeconds = intval($_POST['expiry_seconds'] ?? 60); // Default 1 minute
            
            // Kiểm tra hợp đồng đã được phê duyệt chưa
            $contract = $db->fetchOne("
                SELECT ec.*, c.name as customer_name, c.phone, c.email 
                FROM electronic_contracts ec 
                JOIN customers c ON ec.customer_id = c.id 
                WHERE ec.id = ? AND ec.customer_id = ? AND ec.status = 'active'
            ", [$contractId, $customerId]);
            
            if (!$contract) {
                throw new Exception('Hợp đồng không tồn tại hoặc chưa được phê duyệt');
            }
            
            $result = $contractOTP->generateOTP($contractId, $customerId, $sendMethod, $expirySeconds);
            echo json_encode($result);
            break;
            
        case 'verify_otp':
            if (!isset($_POST['contract_id']) || !isset($_POST['customer_id']) || !isset($_POST['otp_code'])) {
                throw new Exception('Thiếu thông tin xác thực');
            }
            
            $contractId = intval($_POST['contract_id']);
            $customerId = intval($_POST['customer_id']);
            $otpCode = sanitize_input($_POST['otp_code']);
            
            $result = $contractOTP->verifyOTP($contractId, $customerId, $otpCode);
            
            if ($result['success']) {
                // Tạo hợp đồng DOCX sau khi xác thực thành công
                $generateResult = $contractGenerator->generateContract($contractId, $result['otp_id']);
                
                if ($generateResult['success']) {
                    $result['contract_file'] = [
                        'file_name' => $generateResult['file_name'],
                        'download_id' => $generateResult['download_id'],
                        'file_size' => $generateResult['file_size']
                    ];
                    $result['message'] .= '. Hợp đồng đã được tạo và sẵn sàng tải xuống.';
                } else {
                    $result['warning'] = 'Xác thực thành công nhưng có lỗi khi tạo hợp đồng: ' . $generateResult['message'];
                }
            }
            
            echo json_encode($result);
            break;
            
        case 'download_contract':
            if (!isset($_GET['download_id']) || !isset($_GET['contract_id']) || !isset($_GET['customer_id'])) {
                throw new Exception('Thiếu thông tin tải xuống');
            }
            
            $downloadId = intval($_GET['download_id']);
            $contractId = intval($_GET['contract_id']);
            $customerId = intval($_GET['customer_id']);
            
            // Kiểm tra OTP đã được xác thực
            if (!$contractOTP->isOTPVerified($contractId, $customerId)) {
                throw new Exception('Chưa xác thực OTP');
            }
            
            $contractGenerator->downloadContract($downloadId, $contractId, $customerId);
            break;
            
        case 'check_otp_status':
            if (!isset($_GET['contract_id']) || !isset($_GET['customer_id'])) {
                throw new Exception('Thiếu thông tin kiểm tra');
            }
            
            $contractId = intval($_GET['contract_id']);
            $customerId = intval($_GET['customer_id']);
            
            // Kiểm tra trạng thái OTP hiện tại
            $otpStatus = $db->fetchOne("
                SELECT id, status, expires_at, attempts, max_attempts, created_at 
                FROM contract_otp_verification 
                WHERE contract_id = ? AND customer_id = ? 
                ORDER BY created_at DESC LIMIT 1
            ", [$contractId, $customerId]);
            
            if ($otpStatus) {
                $isExpired = strtotime($otpStatus['expires_at']) < time();
                $canRetry = $otpStatus['attempts'] < $otpStatus['max_attempts'];
                
                echo json_encode([
                    'success' => true,
                    'has_otp' => true,
                    'status' => $otpStatus['status'],
                    'is_expired' => $isExpired,
                    'can_retry' => $canRetry,
                    'attempts' => $otpStatus['attempts'],
                    'max_attempts' => $otpStatus['max_attempts'],
                    'expires_at' => $otpStatus['expires_at'],
                    'time_remaining' => max(0, strtotime($otpStatus['expires_at']) - time())
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'has_otp' => false,
                    'message' => 'Chưa có OTP nào được tạo'
                ]);
            }
            break;
            
        case 'resend_otp':
            if (!isset($_POST['contract_id']) || !isset($_POST['customer_id'])) {
                throw new Exception('Thiếu thông tin gửi lại OTP');
            }
            
            $contractId = intval($_POST['contract_id']);
            $customerId = intval($_POST['customer_id']);
            $sendMethod = $_POST['send_method'] ?? 'both';
            
            // Kiểm tra có thể gửi lại không (giới hạn 3 lần/phút)
            $recentOTPs = $db->fetchAll("
                SELECT COUNT(*) as count 
                FROM contract_otp_verification 
                WHERE contract_id = ? AND customer_id = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            ", [$contractId, $customerId]);
            
            if ($recentOTPs[0]['count'] >= 3) {
                throw new Exception('Đã gửi quá nhiều OTP. Vui lòng thử lại sau 1 phút.');
            }
            
            $result = $contractOTP->generateOTP($contractId, $customerId, $sendMethod, 60);
            echo json_encode($result);
            break;
            
        case 'cleanup_expired':
            // Dọn dẹp OTP hết hạn (có thể chạy bằng cron job)
            $contractOTP->cleanupExpiredOTP();
            echo json_encode([
                'success' => true,
                'message' => 'Đã dọn dẹp OTP hết hạn'
            ]);
            break;
            
        case 'create_template':
            // Tạo template mẫu (chỉ dùng 1 lần để setup)
            $result = $contractGenerator->createSampleTemplate();
            echo json_encode($result);
            break;
            
        case 'get_download_history':
            if (!isset($_GET['contract_id'])) {
                throw new Exception('Thiếu contract_id');
            }
            
            $contractId = intval($_GET['contract_id']);
            
            $downloads = $db->fetchAll("
                SELECT cd.*, cov.otp_code, cov.verified_at 
                FROM contract_downloads cd
                JOIN contract_otp_verification cov ON cd.otp_verification_id = cov.id
                WHERE cd.contract_id = ?
                ORDER BY cd.created_at DESC
            ", [$contractId]);
            
            echo json_encode([
                'success' => true,
                'downloads' => $downloads
            ]);
            break;
            
        default:
            throw new Exception('Action không hợp lệ: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>