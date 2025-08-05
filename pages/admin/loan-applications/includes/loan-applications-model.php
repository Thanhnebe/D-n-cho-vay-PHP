<?php

/**
 * Loan Applications Model
 * Xử lý tất cả logic liên quan đến dữ liệu đơn vay
 */
class LoanApplicationsModel
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Lấy danh sách tất cả đơn vay
     */
    public function getAllApplications()
    {
        try {
            $result = $this->db->fetchAll("
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
                ORDER BY la.created_at DESC
            ");

            error_log('getAllApplications result count: ' . count($result));
            return $result;
        } catch (Exception $e) {
            error_log('Error in getAllApplications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy thông tin đơn vay theo ID
     */
    public function getApplicationById($id)
    {
        return $this->db->fetchOne("
            SELECT la.*, 
                   c.name as customer_name, 
                   c.phone as customer_phone,
                   c.email as customer_email,
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
        ", [$id]);
    }

    /**
     * Tạo đơn vay mới
     */
    public function createApplication($data)
    {
        // Tạo mã đơn vay tự động
        if (empty($data['application_code'])) {
            $data['application_code'] = 'LA' . date('Ymd') . rand(1000, 9999);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->insert('loan_applications', $data);
    }

    /**
     * Cập nhật đơn vay
     */
    public function updateApplication($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('loan_applications', $data, 'id = ?', ['id' => $id]);
    }

    /**
     * Xóa đơn vay
     */
    public function deleteApplication($id)
    {
        return $this->db->delete('loan_applications', 'id = ?', ['id' => $id]);
    }

    /**
     * Phê duyệt đơn vay
     */
    public function approveApplication($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Cập nhật trạng thái đơn vay
            $updateData = [
                'status' => 'approved',
                'approved_amount' => $data['approved_amount'],
                'decision_notes' => $data['comments'],
                'current_approval_level' => $data['approval_level'],
                'highest_approval_level' => $data['approval_level'],
                'final_decision' => 'approved',
                'decision_date' => date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->update('loan_applications', $updateData, 'id = ?', ['id' => $id]);

            if (!$result) {
                throw new Exception('Có lỗi xảy ra khi cập nhật đơn vay');
            }

            // Ghi log phê duyệt
            $approvalData = [
                'application_id' => $id,
                'approver_id' => $data['approver_id'],
                'approval_level' => $data['approval_level'],
                'action' => 'approve',
                'approved_amount' => $data['approved_amount'],
                'comments' => $data['comments'],
                'approval_date' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('loan_approvals', $approvalData);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Từ chối đơn vay
     */
    public function rejectApplication($id, $data)
    {
        $this->db->beginTransaction();

        try {
            // Cập nhật trạng thái đơn vay
            $updateData = [
                'status' => 'rejected',
                'final_decision' => 'rejected',
                'decision_date' => date('Y-m-d'),
                'decision_notes' => $data['reject_comments'],
                'current_approval_level' => $data['reject_level'],
                'highest_approval_level' => $data['reject_level'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->update('loan_applications', $updateData, 'id = ?', ['id' => $id]);

            if (!$result) {
                throw new Exception('Có lỗi xảy ra khi cập nhật đơn vay');
            }

            // Ghi log từ chối
            $rejectionData = [
                'application_id' => $id,
                'approver_id' => $data['approver_id'],
                'approval_level' => $data['reject_level'],
                'action' => 'reject',
                'comments' => $data['reject_comments'],
                'approval_date' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('loan_approvals', $rejectionData);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Lấy danh sách khách hàng
     */
    public function getCustomers()
    {
        return $this->db->fetchAll("SELECT id, name, phone FROM customers ORDER BY name");
    }

    /**
     * Lấy danh sách tài sản
     */
    public function getAssets()
    {
        return $this->db->fetchAll("SELECT id, name FROM assets WHERE status = 'available' ORDER BY name");
    }

    /**
     * Lấy danh sách lãi suất
     */
    public function getInterestRates()
    {
        return $this->db->fetchAll("SELECT id, description, monthly_rate, daily_rate FROM interest_rates WHERE status = 'active' ORDER BY description");
    }

    /**
     * Lấy danh sách người dùng
     */
    public function getUsers()
    {
        return $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");
    }

    /**
     * Lấy danh sách phòng ban
     */
    public function getDepartments()
    {
        return $this->db->fetchAll("SELECT id, name FROM departments WHERE status = 'active' ORDER BY name");
    }

    /**
     * Sanitize input data
     */
    public function sanitizeData($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = sanitize_input($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Format currency
     */
    public function formatCurrency($amount)
    {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get status label
     */
    public function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Nháp',
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
            'disbursed' => 'Đã giải ngân',
            'cancelled' => 'Đã hủy'
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass($status)
    {
        $classes = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'disbursed' => 'info',
            'cancelled' => 'dark'
        ];

        return $classes[$status] ?? 'dark';
    }
}
