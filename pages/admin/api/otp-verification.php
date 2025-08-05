<?php
// Use absolute paths for includes
$rootPath = dirname(dirname(dirname(__DIR__)));
require_once $rootPath . '/config/config.php';
require_once $rootPath . '/config/database.php';
require_once $rootPath . '/config/email.php';
require_once $rootPath . '/config/sms.php';
require_once $rootPath . '/includes/auth.php';
require_once $rootPath . '/includes/functions.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'generate_otp':
            $contractId = intval($_POST['contract_id']);
            $sendMethod = $_POST['send_method'] ?? 'email'; // email, sms, both

            // Lấy thông tin hợp đồng và khách hàng từ loan_applications
            $contract = $db->fetchOne("
                SELECT ec.*, la.customer_name as name, la.customer_phone_main as phone, 
                       la.customer_email as email, la.customer_cmnd as id_number, la.customer_address as address
                FROM electronic_contracts ec
                LEFT JOIN loan_applications la ON ec.application_id = la.id
                WHERE ec.id = ?
            ", [$contractId]);

            if (!$contract) {
                throw new Exception('Không tìm thấy hợp đồng');
            }

            if ($contract['status'] !== 'active') {
                throw new Exception('Hợp đồng chưa được phê duyệt hoàn thành');
            }

            // Kiểm tra OTP còn hiệu lực không
            $existingOTP = $db->fetchOne("
                SELECT * FROM otp_verification 
                WHERE contract_id = ? AND status = 'sent' AND expires_at > NOW()
                ORDER BY created_at DESC LIMIT 1
            ", [$contractId]);

            if ($existingOTP) {
                $remainingTime = strtotime($existingOTP['expires_at']) - time();
                throw new Exception('OTP vẫn còn hiệu lực. Vui lòng chờ ' . $remainingTime . ' giây trước khi gửi lại.');
            }

            // Generate OTP code - Sử dụng mt_rand thay vì rand để an toàn hơn
            $otpCode = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', time() + 300); // Tăng lên 5 phút thay vì 1 phút

            // Lưu OTP vào database
            $otpData = [
                'contract_id' => $contractId,
                'customer_id' => $contract['customer_id'],
                'otp_code' => $otpCode,
                'phone_number' => $contract['phone'],
                'email' => $contract['email'],
                'send_method' => $sendMethod,
                'status' => 'sent',
                'expires_at' => $expiresAt,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];

            $otpId = $db->insert('otp_verification', $otpData);

            if (!$otpId) {
                throw new Exception('Lỗi khi tạo mã OTP');
            }

            // Gửi OTP qua email/SMS
            $sendResult = sendOTP($contract, $otpCode, $sendMethod);

            if ($sendResult['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Mã OTP đã được gửi đến ' . ($sendMethod === 'email' ? 'email' : 'số điện thoại') . ' của khách hàng',
                    'otp_id' => $otpId,
                    'expires_in' => 300 // 5 phút
                ], JSON_UNESCAPED_UNICODE);
            } else {
                // Update OTP status to failed
                $db->update('otp_verification', ['status' => 'failed'], 'id = :id', ['id' => $otpId]);
                throw new Exception('Lỗi khi gửi OTP: ' . $sendResult['message']);
            }
            break;

        case 'verify_otp':
            // Xử lý OTP code mà không dùng sanitize_input để tránh làm mất ký tự
            $otpCode = trim($_POST['otp_code'] ?? '');
            $contractId = intval($_POST['contract_id']);

            // Debug log chi tiết hơn
            error_log("OTP Verification Debug - Raw Input: '" . $otpCode . "', Length: " . strlen($otpCode) . ", Contract ID: " . $contractId);

            // Kiểm tra OTP có rỗng không
            if (empty($otpCode)) {
                throw new Exception('Vui lòng nhập mã OTP');
            }

            // Kiểm tra độ dài OTP
            if (strlen($otpCode) !== 6) {
                error_log("OTP Length validation failed: " . strlen($otpCode) . " != 6");
                throw new Exception('Vui lòng nhập đầy đủ 6 số mã OTP');
            }

            // Kiểm tra OTP chỉ chứa số
            if (!ctype_digit($otpCode)) {
                error_log("OTP Digit validation failed: contains non-digits");
                throw new Exception('Mã OTP chỉ được chứa số');
            }

            // Tìm OTP hợp lệ - Thêm debug log
            $otp = $db->fetchOne("
                SELECT * FROM otp_verification 
                WHERE contract_id = ? AND otp_code = ? AND status = 'sent' AND expires_at > NOW()
                ORDER BY created_at DESC LIMIT 1
            ", [$contractId, $otpCode]);

            error_log("OTP Query Result: " . ($otp ? "Found OTP ID: " . $otp['id'] : "No OTP found"));

            if (!$otp) {
                // Tăng số lần thử
                $db->query("
                    UPDATE otp_verification 
                    SET attempts = attempts + 1 
                    WHERE contract_id = ? AND status = 'sent'
                ", [$contractId]);

                // Log thêm thông tin debug
                $allOtps = $db->fetchAll("
                    SELECT id, otp_code, status, expires_at, attempts 
                    FROM otp_verification 
                    WHERE contract_id = ? 
                    ORDER BY created_at DESC LIMIT 5
                ", [$contractId]);

                error_log("All OTPs for contract " . $contractId . ": " . json_encode($allOtps));

                throw new Exception('Mã OTP không đúng hoặc đã hết hạn');
            }

            // Kiểm tra số lần thử
            if ($otp['attempts'] >= $otp['max_attempts']) {
                $db->update('otp_verification', ['status' => 'expired'], 'id = :id', ['id' => $otp['id']]);
                throw new Exception('Đã vượt quá số lần nhập cho phép');
            }

            // Verify thành công
            $db->update('otp_verification', [
                'status' => 'verified',
                'verified_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $otp['id']]);

            // Cập nhật trạng thái hợp đồng thành "completed" (hoàn thành)
            $db->update('electronic_contracts', [
                'status' => 'completed',
                'signed_date' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $contractId]);

            // Generate contract document
            $documentPath = generateContractDocument($contractId, $otp['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Xác thực OTP thành công! Hợp đồng đã được cập nhật trạng thái hoàn thành.',
                'download_url' => $documentPath,
                'otp_id' => $otp['id'],
                'contract_status' => 'completed'
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'download_contract':
            $otpId = intval($_GET['otp_id']);
            $contractId = intval($_GET['contract_id']);

            // Kiểm tra OTP đã được verify
            $otp = $db->fetchOne("
                SELECT * FROM otp_verification 
                WHERE id = ? AND contract_id = ? AND status = 'verified'
            ", [$otpId, $contractId]);

            if (!$otp) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Không có quyền tải xuống']);
                exit;
            }

            // Generate và download file
            $filePath = generateContractDocument($contractId, $otpId);

            if (file_exists($filePath)) {
                // Log download
                $db->insert('document_downloads', [
                    'contract_id' => $contractId,
                    'customer_id' => $otp['customer_id'],
                    'otp_verification_id' => $otpId,
                    'file_name' => basename($filePath),
                    'file_path' => $filePath,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);

                // Force download
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'File không tồn tại']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendOTP($contract, $otpCode, $method)
{
    $customerName = $contract['name'];
    $contractCode = $contract['contract_code'];
    $success = false;
    $errorMsg = '';

    try {
        if ($method === 'email' || $method === 'both') {
            if (!empty($contract['email'])) {
                // Sử dụng PHPMailer để gửi email
                $success = sendOTPEmail($contract['email'], $customerName, $otpCode);

                if (!$success) {
                    $errorMsg .= "Lỗi gửi email. ";
                } else {
                    error_log("Email OTP sent to {$contract['email']}: {$otpCode}");
                }
            }
        }

        if ($method === 'sms' || $method === 'both') {
            if (!empty($contract['phone'])) {
                // Sử dụng SMS function
                $smsResult = sendOTPSMS($contract['phone'], $otpCode);
                $success = $smsResult['success'];

                if (!$success) {
                    $errorMsg .= "Lỗi gửi SMS: " . ($smsResult['error'] ?? 'Unknown error') . ". ";
                } else {
                    error_log("SMS OTP sent to {$contract['phone']}: {$otpCode}");
                }
            }
        }

        return ['success' => $success, 'message' => $success ? 'Gửi thành công' : $errorMsg];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function generateContractDocument($contractId, $otpId)
{
    global $db;

    // Lấy đầy đủ thông tin hợp đồng từ loan_applications
    $contract = $db->fetchOne("
        SELECT ec.*, 
               la.customer_name, la.customer_cmnd as id_number, la.customer_address as address, 
               la.customer_phone_main as phone, la.customer_email as email, la.customer_birth_date as date_of_birth,
               la.customer_job as occupation, la.customer_income as monthly_income, la.customer_company,
               la.asset_name, la.asset_license_plate as license_plate, la.asset_frame_number as frame_number, 
               la.asset_engine_number as engine_number, la.asset_registration_number as registration_number, 
               la.asset_registration_date as registration_date, la.asset_brand as brand, la.asset_model as model, 
               la.asset_year as year, la.asset_value, la.customer_id_issued_date, la.customer_id_issued_place,
               ir.description as rate_description
        FROM electronic_contracts ec
        LEFT JOIN loan_applications la ON ec.application_id = la.id
        LEFT JOIN interest_rates ir ON ec.interest_rate_id = ir.id
        WHERE ec.id = ?
    ", [$contractId]);

    if (!$contract) {
        throw new Exception('Không tìm thấy hợp đồng');
    }

    // Template file path - Sử dụng đường dẫn tương đối từ thư mục gốc
    $templatePath = '../../../templates/contract_template.docx';

    if (!file_exists($templatePath)) {
        throw new Exception('Template hợp đồng không tồn tại tại đường dẫn: ' . $templatePath);
    }

    // Tạo thư mục output nếu chưa có
    $outputDir = 'temp/contracts/';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // Tên file output
    $fileName = 'HopDong_' . $contract['contract_code'] . '_' . date('YmdHis') . '.docx';
    $outputPath = $outputDir . $fileName;

    // Sử dụng PHPWord để xử lý template
    $autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new Exception('PHPWord không được cài đặt. Vui lòng chạy "composer install" để cài đặt dependencies.');
    }

    require_once $autoloadPath;

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

    // Thay thế các biến trong template
    $variables = [
        'TENNGUOIVAY' => $contract['customer_name'] ?? '',
        'CCCD' => $contract['id_number'] ?? '',
        'HOKHAU' => $contract['address'] ?? '',
        'DIENTHOAI' => $contract['phone'] ?? '',
        'SINHNAM' => $contract['date_of_birth'] ? date('d/m/Y', strtotime($contract['date_of_birth'])) : '',
        'NGAYCAP' => $contract['customer_id_issued_date'] ? date('d/m/Y', strtotime($contract['customer_id_issued_date'])) : '',
        'EMAIL' => $contract['email'] ?? '',
        'SOTAISAN' => $contract['asset_id'] ?? '',
        'TENTAISAN' => $contract['asset_name'] ?? '',
        'BIENKIEMSOAT' => $contract['license_plate'] ?? '',
        'SOKHUNG' => $contract['frame_number'] ?? '',
        'SOMAY' => $contract['engine_number'] ?? '',
        'GIAYTODANGKY' => $contract['registration_number'] ?? '',
        'NGAYCAPSOHIEU' => $contract['registration_date'] ? date('d/m/Y', strtotime($contract['registration_date'])) : '',
        'NGAYBATDAU' => date('d/m/Y', strtotime($contract['start_date'])),
        'NGAYKETTHUC' => date('d/m/Y', strtotime($contract['end_date'])),
        'SOTIENVAY' => number_format($contract['loan_amount'], 0, ',', '.') . ' VND',
        'SOTIENDUYET' => number_format($contract['approved_amount'], 0, ',', '.') . ' VND',
        'LAISUAT' => $contract['monthly_rate'] . '%/tháng',
        'THOIHAN' => $contract['loan_term_months'] . ' tháng',
        'MAHOPDONG' => $contract['contract_code'],
        'NGAYTAO' => date('d/m/Y'),
        'PHISUCKHOE' => '0 VND', // TODO: Lấy từ database nếu có
        'PHIBAOHIEMTOICAP' => '0 VND', // TODO: Lấy từ database nếu có
        'PHIBAOHIEMXE' => '0 VND', // TODO: Lấy từ database nếu có
        'TONGPHIBAOHIEM' => '0 VND' // TODO: Tính tổng
    ];

    foreach ($variables as $key => $value) {
        $templateProcessor->setValue($key, $value);
    }

    // Lưu file
    $templateProcessor->saveAs($outputPath);

    return $outputPath;
}
