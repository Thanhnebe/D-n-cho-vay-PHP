<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Kiểm tra quyền truy cập
if (!isLoggedIn()) {
    header('Location: ?page=login');
    exit;
}

$db = getDB();
$currentUser = $_SESSION['user_id'] ?? 0;
$userRole = $_SESSION['user_role'] ?? '';
$userDepartment = $_SESSION['department_id'] ?? 0;

// Lấy ID đơn miễn giảm
$applicationId = intval($_GET['id'] ?? 0);

if (!$applicationId) {
    header('Location: ?page=waiver-tracking-comprehensive');
    exit;
}

// Lấy thông tin đơn miễn giảm
$application = $db->fetchOne("
    SELECT 
        iwa.*,
        c.contract_code,
        c.amount as disbursed_amount,
        c.remaining_balance,
        c.start_date as disbursement_date,
        c.end_date,
        c.status as contract_status,
        cu.name as customer_name,
        cu.phone as customer_phone,
        cu.id_number as customer_id_number,
        cu.date_of_birth as customer_birth_date,
        cu.address as customer_address,
        cu.gender as customer_gender,
        cu.email as customer_email,
        cu.job as customer_job,
        cu.company as customer_company,
        cu.income as customer_income,
        d.name as department_name,
        u.name as created_by_name,
        u.id as created_by_id
    FROM interest_waiver_applications iwa
    JOIN contracts c ON iwa.contract_id = c.id
    JOIN customers cu ON iwa.customer_id = cu.id
    LEFT JOIN departments d ON iwa.department_id = d.id
    LEFT JOIN users u ON iwa.created_by = u.id
    WHERE iwa.id = ?
", [$applicationId]);

if (!$application) {
    header('Location: ?page=waiver-tracking-comprehensive');
    exit;
}

// Kiểm tra phân quyền
if ($userRole === 'department_staff' && $application['department_id'] != $userDepartment) {
    header('Location: ?page=waiver-tracking-comprehensive');
    exit;
}

// Tính toán các giá trị
$daysOverdue = max(0, (strtotime('now') - strtotime($application['end_date'])) / (24 * 60 * 60));
$bucketGroup = getBucketGroup($daysOverdue);
$dpdStatus = getDPDStatus($daysOverdue);

// Tính toán cấu phần khoản vay
$totalObligation = $application['original_amount'];
$principalAmount = $totalObligation * 0.6; // Giả sử gốc chiếm 60%
$interestAmount = $totalObligation * 0.25; // Giả sử lãi chiếm 25%
$feeAmount = $totalObligation * 0.15; // Giả sử phí chiếm 15%

$expectedCollection = $application['expected_collection_amount'];
$waiverAmount = $application['waiver_amount'];
$remainingAfterWaiver = $application['remaining_amount_after_waiver'];

// Tính toán theo loại miễn giảm
$waiverBreakdown = calculateWaiverBreakdown($application);

// Lấy lịch sử phê duyệt
$approvalHistory = $db->fetchAll("
    SELECT 
        iwa.*,
        u.name as approver_name,
        u.role as approver_role
    FROM interest_waiver_approvals iwa
    LEFT JOIN users u ON iwa.approver_id = u.id
    WHERE iwa.application_id = ?
    ORDER BY iwa.approval_level ASC, iwa.created_at ASC
", [$applicationId]);

// Lấy tài liệu đính kèm
$documents = $db->fetchAll("
    SELECT * FROM waiver_documents 
    WHERE application_id = ? 
    ORDER BY created_at DESC
", [$applicationId]);

// Hàm helper
function formatCurrencyVND($amount)
{
    return number_format($amount, 0, ',', '.') . ' VND';
}

function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime)
{
    return date('d/m/Y H:i', strtotime($datetime));
}

function getBucketGroup($daysOverdue)
{
    if ($daysOverdue <= 30) return 'Bucket 1-30';
    if ($daysOverdue <= 60) return 'Bucket 31-60';
    if ($daysOverdue <= 90) return 'Bucket 61-90';
    if ($daysOverdue <= 180) return 'Bucket 91-180';
    if ($daysOverdue <= 365) return 'Bucket 181-365';
    return 'Bucket 365+';
}

function getDPDStatus($daysOverdue)
{
    if ($daysOverdue <= 30) return 'DPD 1-30';
    if ($daysOverdue <= 60) return 'DPD 31-60';
    if ($daysOverdue <= 90) return 'DPD 61-90';
    if ($daysOverdue <= 180) return 'DPD 91-180';
    if ($daysOverdue <= 365) return 'DPD 181-365';
    return 'DPD 365+';
}

function calculateWaiverBreakdown($application)
{
    $waiverType = $application['waiver_type'];
    $totalObligation = $application['original_amount'];
    $waiverAmount = $application['waiver_amount'];

    switch ($waiverType) {
        case 'MGLP toàn bộ HĐ':
            return [
                'principal' => $totalObligation * 0.6,
                'interest' => $totalObligation * 0.25,
                'service_fee' => $totalObligation * 0.1,
                'consulting_fee' => $totalObligation * 0.05,
                'early_settlement_penalty' => 0,
                'late_payment_penalty' => 0
            ];

        case 'MGLP một phần HĐ':
            $halfAmount = $waiverAmount / 2;
            return [
                'principal' => $halfAmount * 0.6,
                'interest' => $halfAmount * 0.25,
                'service_fee' => $halfAmount * 0.1,
                'consulting_fee' => $halfAmount * 0.05,
                'early_settlement_penalty' => 0,
                'late_payment_penalty' => 0
            ];

        case 'MG lãi suất':
            return [
                'principal' => 0,
                'interest' => $waiverAmount,
                'service_fee' => 0,
                'consulting_fee' => 0,
                'early_settlement_penalty' => 0,
                'late_payment_penalty' => 0
            ];

        case 'MG phí dịch vụ':
            return [
                'principal' => 0,
                'interest' => 0,
                'service_fee' => $waiverAmount * 0.67,
                'consulting_fee' => $waiverAmount * 0.33,
                'early_settlement_penalty' => 0,
                'late_payment_penalty' => 0
            ];

        case 'MG phí phạt':
            return [
                'principal' => 0,
                'interest' => 0,
                'service_fee' => 0,
                'consulting_fee' => 0,
                'early_settlement_penalty' => 0,
                'late_payment_penalty' => $waiverAmount
            ];

        default:
            return [
                'principal' => 0,
                'interest' => $waiverAmount * 0.5,
                'service_fee' => 0,
                'consulting_fee' => $waiverAmount * 0.3,
                'early_settlement_penalty' => 0,
                'late_payment_penalty' => $waiverAmount * 0.2
            ];
    }
}

function getStatusLabel($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Chờ phê duyệt</span>';
        case 'approved':
            return '<span class="badge bg-success">Đã phê duyệt</span>';
        case 'rejected':
            return '<span class="badge bg-danger">Từ chối</span>';
        case 'cancelled':
            return '<span class="badge bg-secondary">Đã hủy</span>';
        default:
            return '<span class="badge bg-secondary">Khác</span>';
    }
}

function getActionLabel($action)
{
    switch ($action) {
        case 'approve':
            return '<span class="badge bg-success">Phê duyệt</span>';
        case 'reject':
            return '<span class="badge bg-danger">Từ chối</span>';
        default:
            return '<span class="badge bg-secondary">Khác</span>';
    }
}
?>

<!-- CSS cho trang -->
<link rel="stylesheet" href="assets/css/waiver-application-detail.css">

<div class="waiver-detail-container">
    <!-- Header -->
    <div class="waiver-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0;">Thông tin hồ sơ MGP</h1>
                <p style="color: #666; margin: 8px 0 0 0;">Cấp phê duyệt cao nhất: Cấp 4: Tổng Giám đốc</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn btn-secondary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </button>
                <button class="btn btn-primary" onclick="printApplication()">
                    <i class="fas fa-print"></i> In mẫu
                </button>
            </div>
        </div>
    </div>

    <!-- Nội dung chính -->
    <div class="waiver-content">
        <!-- Section I: Thông tin chung về khoản vay -->
        <div class="section">
            <h2 class="section-title">I. Thông tin chung về khoản vay tính đến ngày <?php echo formatDate($application['data_date'] ?? date('Y-m-d')); ?></h2>

            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Tên KH:</span>
                    <span class="value"><?php echo htmlspecialchars($application['customer_name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Mã Lender:</span>
                    <span class="value">TIMA</span>
                </div>
                <div class="info-item">
                    <span class="label">Mã TC:</span>
                    <span class="value"><?php echo htmlspecialchars($application['contract_code']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Ngày giải ngân:</span>
                    <span class="value"><?php echo formatDate($application['disbursement_date']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Nhóm Bucket BOM:</span>
                    <span class="value"><?php echo $bucketGroup; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Ngày kết thúc:</span>
                    <span class="value"><?php echo formatDate($application['end_date']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">DPD max CIF BOM:</span>
                    <span class="value"><?php echo intval($daysOverdue); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Nhóm sản phẩm:</span>
                    <span class="value">XM1</span>
                </div>
                <div class="info-item">
                    <span class="label">Số tiền giải ngân:</span>
                    <span class="value"><?php echo formatCurrencyVND($application['disbursed_amount']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Số tiền đã thu:</span>
                    <span class="value"><?php echo formatCurrencyVND($application['disbursed_amount'] - $application['remaining_balance']); ?></span>
                </div>
            </div>

            <!-- Bảng cấu phần khoản vay -->
            <div class="loan-breakdown-table">
                <h3>Cấu phần khoản vay</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Tổng nghĩa vụ phải trả</th>
                                <th>Số tiền thu dự kiến</th>
                                <th>Số tiền đề xuất miễn giảm</th>
                                <th>Tỷ lệ miễn giảm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Tổng</strong></td>
                                <td><?php echo formatCurrencyVND($totalObligation); ?></td>
                                <td><?php echo formatCurrencyVND($expectedCollection); ?></td>
                                <td class="waiver-amount"><?php echo formatCurrencyVND($waiverAmount); ?></td>
                                <td><?php echo $application['waiver_percentage']; ?>%</td>
                            </tr>
                            <tr>
                                <td>Gốc</td>
                                <td><?php echo formatCurrencyVND($principalAmount); ?></td>
                                <td><?php echo formatCurrencyVND($principalAmount); ?></td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['principal']); ?></td>
                                <td><?php echo $waiverBreakdown['principal'] > 0 ? round(($waiverBreakdown['principal'] / $principalAmount) * 100, 1) : 0; ?>%</td>
                            </tr>
                            <tr>
                                <td>Lãi</td>
                                <td><?php echo formatCurrencyVND($interestAmount); ?></td>
                                <td><?php echo formatCurrencyVND($interestAmount * 0.5); ?></td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['interest']); ?></td>
                                <td><?php echo $waiverBreakdown['interest'] > 0 ? round(($waiverBreakdown['interest'] / $interestAmount) * 100, 1) : 0; ?>%</td>
                            </tr>
                            <tr>
                                <td>Tổng phí</td>
                                <td><?php echo formatCurrencyVND($feeAmount); ?></td>
                                <td>0</td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['service_fee'] + $waiverBreakdown['consulting_fee'] + $waiverBreakdown['early_settlement_penalty'] + $waiverBreakdown['late_payment_penalty']); ?></td>
                                <td><?php echo $feeAmount > 0 ? round((($waiverBreakdown['service_fee'] + $waiverBreakdown['consulting_fee'] + $waiverBreakdown['early_settlement_penalty'] + $waiverBreakdown['late_payment_penalty']) / $feeAmount) * 100, 1) : 0; ?>%</td>
                            </tr>
                            <tr class="sub-item">
                                <td>-Phí dịch vụ</td>
                                <td>0</td>
                                <td>0</td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['service_fee']); ?></td>
                                <td>0%</td>
                            </tr>
                            <tr class="sub-item">
                                <td>-Phí tư vấn</td>
                                <td><?php echo formatCurrencyVND($feeAmount * 0.67); ?></td>
                                <td>0</td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['consulting_fee']); ?></td>
                                <td>100%</td>
                            </tr>
                            <tr class="sub-item">
                                <td>-Phí phạt tất toán trước hạn</td>
                                <td>0</td>
                                <td>0</td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['early_settlement_penalty']); ?></td>
                                <td>0%</td>
                            </tr>
                            <tr class="sub-item">
                                <td>-Phí phạt chậm trả</td>
                                <td><?php echo formatCurrencyVND($feeAmount * 0.33); ?></td>
                                <td>0</td>
                                <td><?php echo formatCurrencyVND($waiverBreakdown['late_payment_penalty']); ?></td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section II: Quá trình tác nghiệp, đánh giá thực trạng -->
        <div class="section">
            <h2 class="section-title">II. Quá trình tác nghiệp, đánh giá thực trạng</h2>

            <div class="assessment-content">
                <ul>
                    <li>Số tiền vay ban đầu là <?php echo formatCurrencyVND($application['disbursed_amount']); ?>, quá hạn <?php echo intval($daysOverdue); ?> ngày, đã trả cho công ty <?php echo formatCurrencyVND($application['disbursed_amount'] - $application['remaining_balance']); ?>.</li>
                    <li>Lý do xin miễn giảm: <?php echo htmlspecialchars($application['reason']); ?></li>
                    <li>Tham chiếu hợp đồng <?php echo htmlspecialchars($application['contract_code']); ?> (<?php echo htmlspecialchars($application['customer_name']); ?>, ID <?php echo htmlspecialchars($application['customer_id_number']); ?>), DPD <?php echo intval($daysOverdue); ?> (<?php echo $bucketGroup; ?>). Đã trả <?php echo formatCurrencyVND($application['disbursed_amount'] - $application['remaining_balance']); ?>. Khách hàng mất việc làm và không có thu nhập. Khách hàng cần hỗ trợ gia đình để thanh toán. Khách hàng yêu cầu công ty miễn giảm lãi và phí để thanh toán khoản vay.</li>
                    <li><?php echo htmlspecialchars($application['contract_code']); ?>: <?php echo formatCurrencyVND($waiverAmount); ?></li>
                    <li>(Lưu ý: Khách hàng từ xa không yêu cầu chữ ký từ case manager, hỗ trợ cho các mẫu đơn thanh toán khoản vay trực tuyến).</li>
                    <li>Khách hàng đã trả <?php echo formatCurrencyVND($expectedCollection); ?> và đề xuất lãnh đạo Tima miễn giảm một phần phí để có thể thanh toán đầy đủ hợp đồng.</li>
                </ul>
            </div>
        </div>

        <!-- Section III: Đề xuất phương án miễn giảm phí -->
        <div class="section">
            <h2 class="section-title">III. Đề xuất phương án miễn giảm phí</h2>

            <div class="proposal-content">
                <ul>
                    <li>Dựa trên tình hình hiện tại của khách hàng, để tạo điều kiện thanh toán, Call Legal 01 đề xuất miễn giảm các khoản tiền như đã nêu trên và miễn giảm phí phát sinh đến ngày hạch toán, theo đúng kế hoạch đã được phê duyệt.</li>
                    <li>Nếu được phê duyệt, thời gian thực hiện kế hoạch sẽ đến ngày <?php echo formatDate($application['effective_expiry_date']); ?>.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom navigation -->
    <div class="bottom-navigation">
        <div class="nav-left">
            <a href="#" class="nav-link" onclick="viewDocuments()">
                <i class="fas fa-file-alt"></i> Đơn đề nghị MGP
            </a>
            <a href="#" class="nav-link" onclick="viewAttachments()">
                <i class="fas fa-paperclip"></i> Giấy tờ đi kèm
            </a>
            <a href="#" class="nav-link" onclick="viewVideo()">
                <i class="fas fa-video"></i> Video
            </a>
            <a href="#" class="nav-link" onclick="viewAudio()">
                <i class="fas fa-music"></i> File MP3
            </a>
            <a href="#" class="nav-link" onclick="viewHistory()">
                <i class="fas fa-clock"></i> Lịch sử
            </a>
        </div>

        <div class="nav-right">
            <button class="btn btn-danger" onclick="rejectApplication()">
                <i class="fas fa-times"></i> Hủy
            </button>
            <button class="btn btn-primary" onclick="approveApplication()">
                <i class="fas fa-check"></i> Duyệt
            </button>
        </div>
    </div>
</div>

<script>
    function printApplication() {
        window.print();
    }

    function viewDocuments() {
        // Mở modal xem tài liệu
        alert('Chức năng xem tài liệu sẽ được phát triển');
    }

    function viewAttachments() {
        // Mở modal xem giấy tờ đính kèm
        alert('Chức năng xem giấy tờ đính kèm sẽ được phát triển');
    }

    function viewVideo() {
        // Mở modal xem video
        alert('Chức năng xem video sẽ được phát triển');
    }

    function viewAudio() {
        // Mở modal xem file MP3
        alert('Chức năng xem file MP3 sẽ được phát triển');
    }

    function viewHistory() {
        // Mở modal xem lịch sử
        alert('Chức năng xem lịch sử sẽ được phát triển');
    }

    function approveApplication() {
        if (confirm('Bạn có chắc chắn muốn phê duyệt đơn miễn giảm này?')) {
            const comments = prompt('Nhập ghi chú phê duyệt (tùy chọn):') || 'Phê duyệt theo đề xuất';

            // Gọi API phê duyệt mới
            fetch('pages/admin/api/waiver-notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=approval_workflow&application_id=<?php echo $applicationId; ?>&action=approve&comments=' + encodeURIComponent(comments)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Phê duyệt thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.error);
                    }
                });
        }
    }

    function rejectApplication() {
        const reason = prompt('Vui lòng nhập lý do từ chối:');
        if (reason) {
            // Gọi API từ chối mới
            fetch('pages/admin/api/waiver-notifications.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=approval_workflow&application_id=<?php echo $applicationId; ?>&action=reject&comments=' + encodeURIComponent(reason)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Từ chối thành công!');
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.error);
                    }
                });
        }
    }
</script>