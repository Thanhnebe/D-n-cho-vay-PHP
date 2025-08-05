<?php
require_once '../../../config/config.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? $_POST['contract_action'] ?? '';

// Debug logging
error_log("Electronic Contracts API - Action: " . $action);
error_log("POST data: " . print_r($_POST, true));

function generateContractDetailHTML($contract)
{
    $statusClass = $contract['status'] === 'active' ? 'success' : ($contract['status'] === 'draft' ? 'secondary' : ($contract['status'] === 'completed' ? 'info' : ($contract['status'] === 'cancelled' ? 'danger' : 'warning')));

    $statusText = $contract['status'] === 'active' ? 'Hoạt động' : ($contract['status'] === 'draft' ? 'Nháp' : ($contract['status'] === 'completed' ? 'Hoàn thành' : ($contract['status'] === 'cancelled' ? 'Đã hủy' : 'Quá hạn')));

    $disbStatusClass = $contract['disbursement_status'] === 'disbursed' ? 'success' : ($contract['disbursement_status'] === 'pending' ? 'warning' : 'danger');

    $disbStatusText = $contract['disbursement_status'] === 'disbursed' ? 'Đã giải ngân' : ($contract['disbursement_status'] === 'pending' ? 'Chờ giải ngân' : 'Đã hủy');

    $html = '
    <div class="row">
        <div class="col-md-6">
            <h5>Thông tin hợp đồng</h5>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Mã hợp đồng:</strong></td>
                    <td>' . htmlspecialchars($contract['contract_code']) . '</td>
                </tr>
                <tr>
                    <td><strong>Khách hàng:</strong></td>
                    <td>' . htmlspecialchars($contract['customer_name']) . '</td>
                </tr>
                <tr>
                    <td><strong>Số điện thoại:</strong></td>
                    <td>' . htmlspecialchars($contract['customer_phone']) . '</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>' . htmlspecialchars($contract['customer_email'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Địa chỉ:</strong></td>
                    <td>' . htmlspecialchars($contract['customer_address'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Tài sản cầm cố:</strong></td>
                    <td>' . htmlspecialchars($contract['asset_name'] ?? 'Không có') . '</td>
                </tr>
                <tr>
                    <td><strong>Số tiền vay:</strong></td>
                    <td>' . format_currency($contract['loan_amount']) . '</td>
                </tr>
                <tr>
                    <td><strong>Số tiền được duyệt:</strong></td>
                    <td>' . format_currency($contract['approved_amount']) . '</td>
                </tr>
                <tr>
                    <td><strong>Lãi suất:</strong></td>
                    <td>' . $contract['monthly_rate'] . '%/tháng (' . $contract['daily_rate'] . '%/ngày)</td>
                </tr>
                <tr>
                    <td><strong>Thời hạn:</strong></td>
                    <td>' . $contract['loan_term_months'] . ' tháng</td>
                </tr>
                <tr>
                    <td><strong>Ngày bắt đầu:</strong></td>
                    <td>' . date('d/m/Y', strtotime($contract['start_date'])) . '</td>
                </tr>
                <tr>
                    <td><strong>Ngày kết thúc:</strong></td>
                    <td>' . date('d/m/Y', strtotime($contract['end_date'])) . '</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5>Thông tin giải ngân</h5>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Trạng thái:</strong></td>
                    <td><span class="badge badge-' . $statusClass . '">' . $statusText . '</span></td>
                </tr>
                <tr>
                    <td><strong>Trạng thái giải ngân:</strong></td>
                    <td><span class="badge badge-' . $disbStatusClass . '">' . $disbStatusText . '</span></td>
                </tr>';

    if ($contract['disbursed_amount']) {
        $html .= '
                <tr>
                    <td><strong>Số tiền giải ngân:</strong></td>
                    <td>' . format_currency($contract['disbursed_amount']) . '</td>
                </tr>
                <tr>
                    <td><strong>Ngày giải ngân:</strong></td>
                    <td>' . date('d/m/Y H:i', strtotime($contract['disbursed_date'])) . '</td>
                </tr>
                <tr>
                    <td><strong>Người giải ngân:</strong></td>
                    <td>' . htmlspecialchars($contract['disbursed_by_name'] ?? '-') . '</td>
                </tr>';
    }

    $html .= '
                <tr>
                    <td><strong>Số dư còn lại:</strong></td>
                    <td>' . ($contract['remaining_balance'] ? format_currency($contract['remaining_balance']) : '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Tổng đã trả:</strong></td>
                    <td>' . format_currency($contract['total_paid']) . '</td>
                </tr>
                <tr>
                    <td><strong>Ngày trả tiếp theo:</strong></td>
                    <td>' . ($contract['next_payment_date'] ? date('d/m/Y', strtotime($contract['next_payment_date'])) : '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Trả hàng tháng:</strong></td>
                    <td>' . ($contract['monthly_payment'] ? format_currency($contract['monthly_payment']) : '-') . '</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h5>Thông tin hệ thống</h5>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Người tạo:</strong></td>
                    <td>' . htmlspecialchars($contract['created_by_name'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Người duyệt:</strong></td>
                    <td>' . htmlspecialchars($contract['approved_by_name'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Ngày tạo:</strong></td>
                    <td>' . date('d/m/Y H:i', strtotime($contract['created_at'])) . '</td>
                </tr>
                <tr>
                    <td><strong>Cập nhật lần cuối:</strong></td>
                    <td>' . date('d/m/Y H:i', strtotime($contract['updated_at'])) . '</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5>Thông tin chữ ký</h5>
            <table class="table table-borderless">
                <tr>
                    <td><strong>Chữ ký khách hàng:</strong></td>
                    <td>' . htmlspecialchars($contract['customer_signature'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Chữ ký công ty:</strong></td>
                    <td>' . htmlspecialchars($contract['company_signature'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Ngày ký:</strong></td>
                    <td>' . ($contract['signed_date'] ? date('d/m/Y H:i', strtotime($contract['signed_date'])) : '-') . '</td>
                </tr>
            </table>
        </div>
    </div>';

    return $html;
}

try {
    switch ($action) {
        case 'add':
            $contractData = [
                'contract_code' => sanitize_input($_POST['contract_code']),
                'application_id' => $_POST['application_id'] ? intval($_POST['application_id']) : null,
                'customer_id' => intval($_POST['customer_id']),
                'asset_id' => $_POST['asset_id'] ? intval($_POST['asset_id']) : null,
                'loan_amount' => floatval(str_replace(',', '', $_POST['loan_amount'])),
                'approved_amount' => floatval(str_replace(',', '', $_POST['approved_amount'])),
                'interest_rate_id' => intval($_POST['interest_rate_id']),
                'monthly_rate' => floatval($_POST['monthly_rate']),
                'daily_rate' => floatval($_POST['daily_rate']),
                'loan_term_months' => intval($_POST['loan_term_months']),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'status' => $_POST['status'],
                'disbursement_status' => $_POST['disbursement_status'],
                'remaining_balance' => floatval(str_replace(',', '', $_POST['approved_amount'])),
                'total_paid' => 0.00,
                'created_by' => $_SESSION['user_id'] ?? 1
            ];

            $contractId = $db->insert('electronic_contracts', $contractData);
            if ($contractId) {
                echo json_encode(['success' => true, 'message' => 'Tạo hợp đồng điện tử thành công!', 'contract_id' => $contractId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo hợp đồng!']);
            }
            break;

        case 'edit':
            $contractData = [
                'contract_code' => sanitize_input($_POST['contract_code']),
                'application_id' => $_POST['application_id'] ? intval($_POST['application_id']) : null,
                'customer_id' => intval($_POST['customer_id']),
                'asset_id' => $_POST['asset_id'] ? intval($_POST['asset_id']) : null,
                'loan_amount' => floatval(str_replace(',', '', $_POST['loan_amount'])),
                'approved_amount' => floatval(str_replace(',', '', $_POST['approved_amount'])),
                'interest_rate_id' => intval($_POST['interest_rate_id']),
                'monthly_rate' => floatval($_POST['monthly_rate']),
                'daily_rate' => floatval($_POST['daily_rate']),
                'loan_term_months' => intval($_POST['loan_term_months']),
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'status' => $_POST['status'],
                'disbursement_status' => $_POST['disbursement_status']
            ];

            $contractId = $_POST['contract_id'];
            $result = $db->update('electronic_contracts', $contractData, 'id = :id', ['id' => $contractId]);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật hợp đồng thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật hợp đồng!']);
            }
            break;

        case 'get_contract':
            $contractId = $_GET['id'];
            $contract = $db->fetchOne("SELECT * FROM electronic_contracts WHERE id = ?", [$contractId]);

            if ($contract) {
                echo json_encode(['success' => true, 'data' => $contract]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy hợp đồng']);
            }
            break;

        case 'get_contract_detail':
            $contractId = $_GET['id'];
            $contract = $db->fetchOne("
                SELECT ec.*, 
                       c.name as customer_name, 
                       c.phone as customer_phone,
                       c.email as customer_email,
                       c.address as customer_address,
                       a.name as asset_name,
                       ir.description as rate_description,
                       u1.name as created_by_name,
                       u2.name as approved_by_name,
                       u3.name as disbursed_by_name
                FROM electronic_contracts ec
                LEFT JOIN customers c ON ec.customer_id = c.id
                LEFT JOIN assets a ON ec.asset_id = a.id
                LEFT JOIN interest_rates ir ON ec.interest_rate_id = ir.id
                LEFT JOIN users u1 ON ec.created_by = u1.id
                LEFT JOIN users u2 ON ec.approved_by = u2.id
                LEFT JOIN users u3 ON ec.disbursed_by = u3.id
                WHERE ec.id = ?
            ", [$contractId]);

            if ($contract) {
                $html = generateContractDetailHTML($contract);
                echo json_encode(['success' => true, 'html' => $html]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy hợp đồng']);
            }
            break;

        case 'test':
            echo json_encode(['success' => true, 'message' => 'API working correctly', 'timestamp' => date('Y-m-d H:i:s')]);
            break;

        case 'check_contract_status':
            $contractId = intval($_GET['contract_id']);
            
            $contract = $db->fetchOne("
                SELECT status FROM electronic_contracts WHERE id = ?
            ", [$contractId]);
            
            if ($contract) {
                echo json_encode([
                    'success' => true,
                    'status' => $contract['status']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy hợp đồng'
                ]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ: ' . $action, 'received_post' => $_POST]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
