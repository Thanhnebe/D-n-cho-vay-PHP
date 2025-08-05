<?php
// Cấu hình Email cho hệ thống VayCamCo

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');  // Hoặc smtp.office365.com, smtp.yandex.com
define('SMTP_PORT', 587);               // 587 cho TLS, 465 cho SSL
define('SMTP_SECURE', 'tls');           // tls hoặc ssl
define('SMTP_USERNAME', 'k40modgame@gmail.com');  // Email của bạn
define('SMTP_PASSWORD', 'wxwj xuuj mvga wgpl');     // Mật khẩu ứng dụng
define('SMTP_FROM_NAME', 'VayCamCo - Hệ thống quản lý cho vay');

// Email Templates
define('EMAIL_TEMPLATE_OTP', '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mã OTP - VayCamCo</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .otp-box { background: #fff; border: 2px solid #007bff; padding: 20px; text-align: center; margin: 20px 0; }
        .otp-code { font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; }
        .footer { background: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Mã OTP Xác Thực</h1>
            <p>VayCamCo - Hệ thống quản lý cho vay cầm cố</p>
        </div>
        
        <div class="content">
            <h2>Xin chào {CUSTOMER_NAME},</h2>
            <p>Bạn đã yêu cầu mã OTP để ký hợp đồng vay. Dưới đây là mã OTP của bạn:</p>
            
            <div class="otp-box">
                <h3>Mã OTP của bạn:</h3>
                <div class="otp-code">{OTP_CODE}</div>
                <p><strong>Mã này có hiệu lực trong 1 phút</strong></p>
            </div>
            
            <div class="warning">
                <h4>⚠️ Lưu ý bảo mật:</h4>
                <ul>
                    <li>Không chia sẻ mã OTP với bất kỳ ai</li>
                    <li>Mã OTP chỉ có hiệu lực trong 1 phút</li>
                    <li>Bạn chỉ có 3 lần nhập sai</li>
                    <li>Nếu không phải bạn yêu cầu, vui lòng liên hệ ngay với chúng tôi</li>
                </ul>
            </div>
            
            <p>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.</p>
            
            <p>Trân trọng,<br>
            <strong>Đội ngũ VayCamCo</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2024 VayCamCo. Tất cả quyền được bảo lưu.</p>
            <p>Hotline: 1900-xxxx | Email: support@vaycamco.com</p>
        </div>
    </div>
</body>
</html>
');

define('EMAIL_TEMPLATE_CONTRACT_READY', '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hợp đồng đã sẵn sàng - VayCamCo</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .info-box { background: #fff; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .footer { background: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Hợp đồng đã sẵn sàng</h1>
            <p>VayCamCo - Hệ thống quản lý cho vay cầm cố</p>
        </div>
        
        <div class="content">
            <h2>Xin chào {CUSTOMER_NAME},</h2>
            <p>Hợp đồng vay của bạn đã được phê duyệt và sẵn sàng để ký. Dưới đây là thông tin chi tiết:</p>
            
            <div class="info-box">
                <h4>📋 Thông tin hợp đồng:</h4>
                <p><strong>Mã hợp đồng:</strong> {CONTRACT_CODE}</p>
                <p><strong>Số tiền vay:</strong> {LOAN_AMOUNT}</p>
                <p><strong>Thời hạn:</strong> {LOAN_TERM}</p>
                <p><strong>Lãi suất:</strong> {INTEREST_RATE}</p>
            </div>
            
            <p>Để ký hợp đồng, bạn cần:</p>
            <ol>
                <li>Đăng nhập vào hệ thống</li>
                <li>Nhập mã OTP được gửi qua email/SMS</li>
                <li>Xác nhận thông tin và ký hợp đồng</li>
            </ol>
            
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
            
            <p>Trân trọng,<br>
            <strong>Đội ngũ VayCamCo</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2024 VayCamCo. Tất cả quyền được bảo lưu.</p>
            <p>Hotline: 1900-xxxx | Email: support@vaycamco.com</p>
        </div>
    </div>
</body>
</html>
');

// Email Functions
function sendEmail($to, $subject, $message, $isHTML = true)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML($isHTML);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendOTPEmail($email, $customerName, $otpCode)
{
    $subject = "Mã OTP xác thực - VayCamCo";
    $message = str_replace(
        ['{CUSTOMER_NAME}', '{OTP_CODE}'],
        [$customerName, $otpCode],
        EMAIL_TEMPLATE_OTP
    );

    return sendEmail($email, $subject, $message);
}

function sendContractReadyEmail($email, $customerName, $contractData)
{
    $subject = "Hợp đồng đã sẵn sàng - VayCamCo";
    $message = str_replace(
        ['{CUSTOMER_NAME}', '{CONTRACT_CODE}', '{LOAN_AMOUNT}', '{LOAN_TERM}', '{INTEREST_RATE}'],
        [$customerName, $contractData['contract_code'], $contractData['loan_amount'], $contractData['loan_term'], $contractData['interest_rate']],
        EMAIL_TEMPLATE_CONTRACT_READY
    );

    return sendEmail($email, $subject, $message);
}
