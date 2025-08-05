<?php
// Cấu hình SMS cho hệ thống VayCamCo

// SMS Gateway Configuration
define('SMS_PROVIDER', 'twilio'); // twilio, nexmo, viettel, mobifone, etc.
define('SMS_API_KEY', 'your-api-key');
define('SMS_API_SECRET', 'your-api-secret');
define('SMS_FROM_NUMBER', '+84901234567'); // Số điện thoại gửi SMS

// Twilio Configuration (nếu sử dụng Twilio)
define('TWILIO_ACCOUNT_SID', 'your-account-sid');
define('TWILIO_AUTH_TOKEN', 'your-auth-token');
define('TWILIO_FROM_NUMBER', '+1234567890');

// Viettel SMS Configuration
define('VIETTEL_USERNAME', 'your-username');
define('VIETTEL_PASSWORD', 'your-password');
define('VIETTEL_CP_CODE', 'your-cp-code');
define('VIETTEL_REQUEST_ID', 'your-request-id');

// Mobifone SMS Configuration
define('MOBIFONE_USERNAME', 'your-username');
define('MOBIFONE_PASSWORD', 'your-password');
define('MOBIFONE_CP_CODE', 'your-cp-code');

// SMS Templates
define('SMS_TEMPLATE_OTP', 'VayCamCo: Ma OTP cua ban la {OTP_CODE}. Co hieu luc trong 1 phut. Khong chia se ma nay voi ai.');
define('SMS_TEMPLATE_CONTRACT_READY', 'VayCamCo: Hop dong {CONTRACT_CODE} da san sang ky. Vui long dang nhap he thong de xac nhan.');

// SMS Functions
function sendSMS($phoneNumber, $message)
{
    switch (SMS_PROVIDER) {
        case 'twilio':
            return sendSMSTwilio($phoneNumber, $message);
        case 'viettel':
            return sendSMSViettel($phoneNumber, $message);
        case 'mobifone':
            return sendSMSMobifone($phoneNumber, $message);
        default:
            return sendSMSTwilio($phoneNumber, $message);
    }
}

function sendSMSTwilio($phoneNumber, $message)
{
    // Cần cài đặt Twilio SDK: composer require twilio/sdk
    try {
        require_once 'vendor/autoload.php';

        $client = new Twilio\Rest\Client(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);

        $message = $client->messages->create(
            $phoneNumber,
            [
                'from' => TWILIO_FROM_NUMBER,
                'body' => $message
            ]
        );

        return ['success' => true, 'message_id' => $message->sid];
    } catch (Exception $e) {
        error_log("Twilio SMS failed: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function sendSMSViettel($phoneNumber, $message)
{
    $url = 'https://api.viettel.com.vn/sms/send';

    $data = [
        'username' => VIETTEL_USERNAME,
        'password' => VIETTEL_PASSWORD,
        'cp_code' => VIETTEL_CP_CODE,
        'request_id' => VIETTEL_REQUEST_ID,
        'phone' => $phoneNumber,
        'message' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        return ['success' => true, 'response' => $result];
    } else {
        return ['success' => false, 'error' => 'HTTP Error: ' . $httpCode];
    }
}

function sendSMSMobifone($phoneNumber, $message)
{
    $url = 'https://api.mobifone.com.vn/sms/send';

    $data = [
        'username' => MOBIFONE_USERNAME,
        'password' => MOBIFONE_PASSWORD,
        'cp_code' => MOBIFONE_CP_CODE,
        'phone' => $phoneNumber,
        'message' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        return ['success' => true, 'response' => $result];
    } else {
        return ['success' => false, 'error' => 'HTTP Error: ' . $httpCode];
    }
}

function sendOTPSMS($phoneNumber, $otpCode)
{
    $message = str_replace('{OTP_CODE}', $otpCode, SMS_TEMPLATE_OTP);
    return sendSMS($phoneNumber, $message);
}

function sendContractReadySMS($phoneNumber, $contractCode)
{
    $message = str_replace('{CONTRACT_CODE}', $contractCode, SMS_TEMPLATE_CONTRACT_READY);
    return sendSMS($phoneNumber, $message);
}

// Test SMS function
function testSMS($phoneNumber)
{
    $testMessage = "VayCamCo: Day la tin nhan test. Neu ban nhan duoc tin nhan nay, he thong SMS da hoat dong binh thuong.";
    return sendSMS($phoneNumber, $testMessage);
}
