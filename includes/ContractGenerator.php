<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;

class ContractGenerator {
    private $db;
    private $templatePath;
    private $outputDir;
    
    public function __construct($database) {
        $this->db = $database;
        $this->templatePath = 'templates/contract_template.docx';
        $this->outputDir = 'contracts/generated/';
        
        // Tạo thư mục output nếu chưa có
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
        
        // Cấu hình PHPWord
        Settings::setTempDir('temp/');
    }
    
    /**
     * Tạo hợp đồng DOCX từ template
     */
    public function generateContract($contractId, $otpVerificationId) {
        try {
            // Lấy thông tin hợp đồng đầy đủ
            $contractData = $this->getContractData($contractId);
            if (!$contractData) {
                throw new Exception('Không tìm thấy thông tin hợp đồng');
            }
            
            // Kiểm tra template có tồn tại không
            if (!file_exists($this->templatePath)) {
                throw new Exception('Template hợp đồng không tồn tại');
            }
            
            // Tạo tên file output
            $fileName = 'HopDong_' . $contractData['contract_code'] . '_' . date('YmdHis') . '.docx';
            $outputPath = $this->outputDir . $fileName;
            
            // Xử lý template
            $templateProcessor = new TemplateProcessor($this->templatePath);
            
            // Thay thế các biến trong template
            $this->replaceTemplateVariables($templateProcessor, $contractData);
            
            // Lưu file
            $templateProcessor->saveAs($outputPath);
            
            // Lưu thông tin download vào database
            $downloadData = [
                'contract_id' => $contractId,
                'customer_id' => $contractData['customer_id'],
                'otp_verification_id' => $otpVerificationId,
                'file_name' => $fileName,
                'file_path' => $outputPath,
                'file_size' => filesize($outputPath),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            $downloadId = $this->db->insert('contract_downloads', $downloadData);
            
            return [
                'success' => true,
                'file_name' => $fileName,
                'file_path' => $outputPath,
                'file_size' => filesize($outputPath),
                'download_id' => $downloadId,
                'message' => 'Hợp đồng đã được tạo thành công'
            ];
            
        } catch (Exception $e) {
            error_log('Contract generation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi tạo hợp đồng: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lấy dữ liệu hợp đồng đầy đủ
     */
    private function getContractData($contractId) {
        return $this->db->fetchOne("
            SELECT 
                ec.*,
                c.name as customer_name,
                c.cmnd as customer_cmnd,
                c.address as customer_address,
                c.phone as customer_phone,
                c.birth_date as customer_birth_date,
                c.id_issued_place as customer_id_issued_place,
                c.id_issued_date as customer_id_issued_date,
                c.email as customer_email,
                c.job as customer_job,
                c.income as customer_income,
                c.company as customer_company,
                
                -- Thông tin tài sản
                a.name as asset_name,
                a.license_plate as asset_license_plate,
                a.frame_number as asset_frame_number,
                a.engine_number as asset_engine_number,
                a.registration_number as asset_registration_number,
                a.registration_date as asset_registration_date,
                a.value as asset_value,
                a.condition as asset_condition,
                a.brand as asset_brand,
                a.model as asset_model,
                a.year as asset_year,
                a.color as asset_color,
                a.cc as asset_cc,
                a.fuel_type as asset_fuel_type,
                a.description as asset_description,
                
                -- Thông tin lãi suất
                ir.description as rate_description,
                
                -- Thông tin người tạo và duyệt
                u1.name as created_by_name,
                u2.name as approved_by_name,
                
                -- Thông tin bảo hiểm (nếu có)
                ec.has_health_insurance,
                ec.has_life_insurance,
                ec.has_vehicle_insurance,
                
                -- Thông tin liên hệ khẩn cấp
                la.emergency_contact_name,
                la.emergency_contact_phone,
                la.emergency_contact_relationship,
                la.emergency_contact_address
                
            FROM electronic_contracts ec
            LEFT JOIN customers c ON ec.customer_id = c.id
            LEFT JOIN assets a ON ec.asset_id = a.id
            LEFT JOIN interest_rates ir ON ec.interest_rate_id = ir.id
            LEFT JOIN users u1 ON ec.created_by = u1.id
            LEFT JOIN users u2 ON ec.approved_by = u2.id
            LEFT JOIN loan_applications la ON ec.application_id = la.id
            WHERE ec.id = ?
        ", [$contractId]);
    }
    
    /**
     * Thay thế các biến trong template
     */
    private function replaceTemplateVariables($templateProcessor, $data) {
        // Thông tin cơ bản khách hàng
        $templateProcessor->setValue('TENNGUOIVAY', $data['customer_name'] ?? '');
        $templateProcessor->setValue('CCCD', $data['customer_cmnd'] ?? '');
        $templateProcessor->setValue('HOKHAU', $data['customer_address'] ?? '');
        $templateProcessor->setValue('DIENTHOAI', $data['customer_phone'] ?? '');
        $templateProcessor->setValue('SINHNANG', $data['customer_birth_date'] ? date('d/m/Y', strtotime($data['customer_birth_date'])) : '');
        $templateProcessor->setValue('NGAYCAP', $data['customer_id_issued_date'] ? date('d/m/Y', strtotime($data['customer_id_issued_date'])) : '');
        $templateProcessor->setValue('NOICAP', $data['customer_id_issued_place'] ?? '');
        $templateProcessor->setValue('EMAIL', $data['customer_email'] ?? '');
        $templateProcessor->setValue('NGHENGHIEP', $data['customer_job'] ?? '');
        $templateProcessor->setValue('THUNHAP', $data['customer_income'] ? number_format($data['customer_income'], 0, ',', '.') : '');
        $templateProcessor->setValue('CONGTY', $data['customer_company'] ?? '');
        
        // Thông tin hợp đồng
        $templateProcessor->setValue('MAHOPDONG', $data['contract_code'] ?? '');
        $templateProcessor->setValue('NGAYBATDAU', $data['start_date'] ? date('d/m/Y', strtotime($data['start_date'])) : '');
        $templateProcessor->setValue('NGAYKETTHUC', $data['end_date'] ? date('d/m/Y', strtotime($data['end_date'])) : '');
        $templateProcessor->setValue('SOTIENVAY', number_format($data['loan_amount'], 0, ',', '.'));
        $templateProcessor->setValue('SOTIENDUYET', number_format($data['approved_amount'], 0, ',', '.'));
        $templateProcessor->setValue('LAISUATTHANG', $data['monthly_rate'] . '%');
        $templateProcessor->setValue('LAISUATNGAY', $data['daily_rate'] . '%');
        $templateProcessor->setValue('THOIHAN', $data['loan_term_months'] . ' tháng');
        
        // Thông tin tài sản
        $templateProcessor->setValue('SOTAISAN', $data['asset_id'] ?? '');
        $templateProcessor->setValue('TENTAISAN', $data['asset_name'] ?? '');
        $templateProcessor->setValue('BIENKIEMSOAT', $data['asset_license_plate'] ?? '');
        $templateProcessor->setValue('SOKHUNG', $data['asset_frame_number'] ?? '');
        $templateProcessor->setValue('SOMAY', $data['asset_engine_number'] ?? '');
        $templateProcessor->setValue('GIAYTODANGKY', $data['asset_registration_number'] ?? '');
        $templateProcessor->setValue('NGAYCAPSOHIEU', $data['asset_registration_date'] ? date('d/m/Y', strtotime($data['asset_registration_date'])) : '');
        $templateProcessor->setValue('GIATRITAISAN', $data['asset_value'] ? number_format($data['asset_value'], 0, ',', '.') : '');
        $templateProcessor->setValue('HANGSANXUAT', $data['asset_brand'] ?? '');
        $templateProcessor->setValue('DONGXE', $data['asset_model'] ?? '');
        $templateProcessor->setValue('NAMSX', $data['asset_year'] ?? '');
        $templateProcessor->setValue('MAUSAC', $data['asset_color'] ?? '');
        $templateProcessor->setValue('DUNGTICHDONGCO', $data['asset_cc'] ?? '');
        $templateProcessor->setValue('LOAINHIENLIEU', $data['asset_fuel_type'] ?? '');
        
        // Thông tin bảo hiểm
        $healthInsuranceFee = $data['has_health_insurance'] ? 500000 : 0; // Giả định phí
        $lifeInsuranceFee = $data['has_life_insurance'] ? 300000 : 0;
        $vehicleInsuranceFee = $data['has_vehicle_insurance'] ? 1000000 : 0;
        $totalInsuranceFee = $healthInsuranceFee + $lifeInsuranceFee + $vehicleInsuranceFee;
        
        $templateProcessor->setValue('PHISUCKHOE', number_format($healthInsuranceFee, 0, ',', '.'));
        $templateProcessor->setValue('PHIBAOHIEMTOCAP', number_format($lifeInsuranceFee, 0, ',', '.'));
        $templateProcessor->setValue('PHIBAOHIEMXE', number_format($vehicleInsuranceFee, 0, ',', '.'));
        $templateProcessor->setValue('TONGPHIBAOHIEM', number_format($totalInsuranceFee, 0, ',', '.'));
        
        // Thông tin liên hệ khẩn cấp
        $templateProcessor->setValue('NGUOILIENHE', $data['emergency_contact_name'] ?? '');
        $templateProcessor->setValue('DTLIENHE', $data['emergency_contact_phone'] ?? '');
        $templateProcessor->setValue('QUANHELIENHE', $data['emergency_contact_relationship'] ?? '');
        $templateProcessor->setValue('DIACHILIENHE', $data['emergency_contact_address'] ?? '');
        
        // Thông tin hệ thống
        $templateProcessor->setValue('NGAYTAO', date('d/m/Y'));
        $templateProcessor->setValue('NGUOITAO', $data['created_by_name'] ?? '');
        $templateProcessor->setValue('NGUOIDUYET', $data['approved_by_name'] ?? '');
        
        // Thông tin tính toán
        $monthlyPayment = $this->calculateMonthlyPayment(
            $data['approved_amount'], 
            $data['monthly_rate'], 
            $data['loan_term_months']
        );
        $templateProcessor->setValue('TRAHANGTHANG', number_format($monthlyPayment, 0, ',', '.'));
        
        $totalPayment = $monthlyPayment * $data['loan_term_months'];
        $templateProcessor->setValue('TONGTIENTRA', number_format($totalPayment, 0, ',', '.'));
        
        $totalInterest = $totalPayment - $data['approved_amount'];
        $templateProcessor->setValue('TONGLAI', number_format($totalInterest, 0, ',', '.'));
    }
    
    /**
     * Tính toán số tiền trả hàng tháng
     */
    private function calculateMonthlyPayment($principal, $monthlyRate, $months) {
        $rate = $monthlyRate / 100;
        if ($rate == 0) {
            return $principal / $months;
        }
        
        return $principal * ($rate * pow(1 + $rate, $months)) / (pow(1 + $rate, $months) - 1);
    }
    
    /**
     * Tải xuống hợp đồng
     */
    public function downloadContract($downloadId, $contractId, $customerId) {
        try {
            // Kiểm tra quyền download
            $download = $this->db->fetchOne("
                SELECT cd.*, cov.status as otp_status 
                FROM contract_downloads cd
                JOIN contract_otp_verification cov ON cd.otp_verification_id = cov.id
                WHERE cd.id = ? AND cd.contract_id = ? AND cd.customer_id = ? 
                AND cov.status = 'verified'
            ", [$downloadId, $contractId, $customerId]);
            
            if (!$download) {
                throw new Exception('Không có quyền tải xuống hợp đồng này');
            }
            
            // Kiểm tra file có tồn tại không
            if (!file_exists($download['file_path'])) {
                throw new Exception('File hợp đồng không tồn tại');
            }
            
            // Cập nhật số lần download
            $this->db->update(
                'contract_downloads',
                [
                    'download_count' => $download['download_count'] + 1,
                    'last_downloaded_at' => date('Y-m-d H:i:s')
                ],
                'id = :id',
                ['id' => $downloadId]
            );
            
            // Chuẩn bị download
            $this->prepareFileDownload($download['file_path'], $download['file_name']);
            
        } catch (Exception $e) {
            error_log('Contract download error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Chuẩn bị file để download
     */
    private function prepareFileDownload($filePath, $fileName) {
        // Set headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Output file
        readfile($filePath);
        exit;
    }
    
    /**
     * Tạo template mẫu
     */
    public function createSampleTemplate() {
        try {
            $templatePath = 'templates/';
            if (!is_dir($templatePath)) {
                mkdir($templatePath, 0755, true);
            }
            
            // Tạo document mẫu
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            
            // Tiêu đề
            $section->addText(
                'HỢP ĐỒNG CHO VAY CẦM CỐ',
                ['name' => 'Arial', 'size' => 16, 'bold' => true],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
            
            $section->addText('Số: ${MAHOPDONG}', ['name' => 'Arial', 'size' => 12, 'bold' => true]);
            $section->addTextBreak();
            
            // Thông tin khách hàng
            $section->addText('I. THÔNG TIN KHÁCH HÀNG', ['name' => 'Arial', 'size' => 12, 'bold' => true]);
            $section->addText('Họ và tên: ${TENNGUOIVAY}');
            $section->addText('CCCD/CMND: ${CCCD}');
            $section->addText('Địa chỉ thường trú: ${HOKHAU}');
            $section->addText('Điện thoại: ${DIENTHOAI}');
            $section->addText('Email: ${EMAIL}');
            $section->addText('Sinh năm: ${SINHNANG}');
            $section->addText('Ngày cấp: ${NGAYCAP}');
            $section->addText('Nơi cấp: ${NOICAP}');
            $section->addTextBreak();
            
            // Thông tin hợp đồng
            $section->addText('II. THÔNG TIN HỢP ĐỒNG', ['name' => 'Arial', 'size' => 12, 'bold' => true]);
            $section->addText('Số tiền vay: ${SOTIENVAY} VND');
            $section->addText('Số tiền được duyệt: ${SOTIENDUYET} VND');
            $section->addText('Lãi suất: ${LAISUATTHANG}/tháng (${LAISUATNGAY}/ngày)');
            $section->addText('Thời hạn: ${THOIHAN}');
            $section->addText('Ngày bắt đầu: ${NGAYBATDAU}');
            $section->addText('Ngày kết thúc: ${NGAYKETTHUC}');
            $section->addText('Trả hàng tháng: ${TRAHANGTHANG} VND');
            $section->addTextBreak();
            
            // Thông tin tài sản thế chấp
            $section->addText('III. THÔNG TIN TÀI SẢN THẾ CHẤP', ['name' => 'Arial', 'size' => 12, 'bold' => true]);
            $section->addText('Tên tài sản: ${TENTAISAN}');
            $section->addText('Biển kiểm soát: ${BIENKIEMSOAT}');
            $section->addText('Số khung: ${SOKHUNG}');
            $section->addText('Số máy: ${SOMAY}');
            $section->addText('Giấy tờ đăng ký: ${GIAYTODANGKY}');
            $section->addText('Ngày cấp số hiệu: ${NGAYCAPSOHIEU}');
            $section->addText('Hãng sản xuất: ${HANGSANXUAT}');
            $section->addText('Dòng xe: ${DONGXE}');
            $section->addText('Năm sản xuất: ${NAMSX}');
            $section->addTextBreak();
            
            // Thông tin bảo hiểm
            $section->addText('IV. THÔNG TIN BẢO HIỂM', ['name' => 'Arial', 'size' => 12, 'bold' => true]);
            $section->addText('Phí sức khỏe: ${PHISUCKHOE} VND');
            $section->addText('Phí bảo hiểm tử cấp: ${PHIBAOHIEMTOCAP} VND');
            $section->addText('Phí bảo hiểm xe: ${PHIBAOHIEMXE} VND');
            $section->addText('Tổng phí bảo hiểm: ${TONGPHIBAOHIEM} VND');
            $section->addTextBreak();
            
            // Ký tên
            $table = $section->addTable();
            $table->addRow();
            $table->addCell(4000)->addText('KHÁCH HÀNG', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $table->addCell(4000)->addText('CÔNG TY', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            
            $table->addRow();
            $table->addCell(4000)->addText('(Ký và ghi rõ họ tên)', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $table->addCell(4000)->addText('(Ký và ghi rõ họ tên)', [], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            
            // Lưu template
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($this->templatePath);
            
            return [
                'success' => true,
                'message' => 'Template đã được tạo tại: ' . $this->templatePath
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi tạo template: ' . $e->getMessage()
            ];
        }
    }
}
?>