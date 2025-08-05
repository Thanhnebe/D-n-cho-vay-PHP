<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Include CSS for this page
echo '<link rel="stylesheet" href="assets/css/waiver-tracking-comprehensive.css">';

// Trang báo cáo tổng hợp miễn giảm lãi
$db = getDB();

// Xử lý tham số tìm kiếm
$selected_month = $_GET['month'] ?? date('Y-m');
$selected_level = $_GET['level'] ?? '';

// Chuyển đổi tháng từ Y-m sang định dạng hiển thị
$display_month = date('m/Y', strtotime($selected_month . '-01'));

// Lấy danh sách các tháng có dữ liệu
$months = $db->fetchAll("
    SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month
    FROM interest_waiver_applications 
    ORDER BY month DESC
");

// Lấy danh sách các cấp phê duyệt
$approval_levels = $db->fetchAll("
    SELECT DISTINCT 
        current_approval_level,
        total_approval_levels,
        CONCAT('Cấp ', current_approval_level, ': ', 
            CASE 
                WHEN current_approval_level = 1 THEN 'TP Tổng đài DVKH'
                WHEN current_approval_level = 2 THEN 'Giám đốc DVKH'
                WHEN current_approval_level = 3 THEN 'Hội đồng XLRR'
                WHEN current_approval_level = 4 THEN 'Tổng Giám đốc'
                ELSE 'Cấp ' || current_approval_level
            END
        ) as level_name
    FROM interest_waiver_applications 
    WHERE status = 'approved'
    ORDER BY current_approval_level
");

// Xây dựng câu query cho báo cáo
$where_conditions = ["DATE_FORMAT(created_at, '%Y-%m') = ?"];
$params = [$selected_month];

if ($selected_level) {
    $where_conditions[] = "current_approval_level = ?";
    $params[] = $selected_level;
}

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// Lấy dữ liệu báo cáo
$report_data = $db->fetchAll("
    SELECT 
        current_approval_level,
        total_approval_levels,
        CONCAT('Cấp ', current_approval_level, ': ', 
            CASE 
                WHEN current_approval_level = 1 THEN 'TP Tổng đài DVKH'
                WHEN current_approval_level = 2 THEN 'Giám đốc DVKH'
                WHEN current_approval_level = 3 THEN 'Hội đồng XLRR'
                WHEN current_approval_level = 4 THEN 'Tổng Giám đốc'
                ELSE 'Cấp ' || current_approval_level
            END
        ) as level_name,
        SUM(approved_amount) as total_approved_amount,
        COUNT(DISTINCT customer_id) as unique_customers,
        COUNT(*) as total_applications,
        CASE 
            WHEN current_approval_level = 1 THEN 500000000
            WHEN current_approval_level = 2 THEN 1000000000
            WHEN current_approval_level = 3 THEN 2000000000
            WHEN current_approval_level = 4 THEN 5000000000
            ELSE 1000000000
        END as approval_limit
    FROM interest_waiver_applications 
    $where_sql
    AND status = 'approved'
    GROUP BY current_approval_level, total_approval_levels
    ORDER BY current_approval_level
", $params);

// Tính tổng thống kê
$total_stats = $db->fetchOne("
    SELECT 
        SUM(approved_amount) as total_amount,
        COUNT(DISTINCT customer_id) as total_customers,
        COUNT(*) as total_applications
    FROM interest_waiver_applications 
    $where_sql
    AND status = 'approved'
", $params);

function formatCurrencyVND($amount)
{
    return number_format($amount, 0, ',', '.') . ' VND';
}

function formatNumber($number)
{
    return number_format($number, 0, ',', '.');
}
?>

<div class="waiver-report-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="font-size:2rem;font-weight:700;">BÁO CÁO MGLP</h1>
        <div style="color:#666;">Báo cáo tổng hợp miễn giảm lãi theo tháng</div>
    </div>
    <div style="display:flex;gap:12px;">
        <button class="btn btn-primary" onclick="exportToExcel()" style="padding:10px 20px;font-size:1rem;">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </button>
    </div>
</div>

<!-- Filter Section -->
<div style="background:#fff;padding:24px;border-radius:12px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <form method="GET" action="" style="display:grid;grid-template-columns:1fr 1fr auto;gap:16px;align-items:end;">
        <input type="hidden" name="page" value="waiver-tracking-comprehensive">
        
        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Thời gian</label>
            <select name="month" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <?php foreach ($months as $month): ?>
                    <?php 
                    $month_display = date('m/Y', strtotime($month['month'] . '-01'));
                    $is_selected = $month['month'] === $selected_month;
                    ?>
                    <option value="<?php echo $month['month']; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                        Tháng <?php echo $month_display; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label style="display:block;margin-bottom:8px;font-weight:600;color:#333;">Cấp</label>
            <select name="level" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:1rem;">
                <option value="">Tất cả</option>
                <?php foreach ($approval_levels as $level): ?>
                    <option value="<?php echo $level['current_approval_level']; ?>" <?php echo $selected_level == $level['current_approval_level'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($level['level_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer;">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </form>
</div>

<!-- Summary Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:32px;">
    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng số tiền đã phê duyệt</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;color:#2563eb;">
            <?php echo formatCurrencyVND($total_stats['total_amount'] ?? 0); ?>
        </div>
        <div style="color:#666;font-size:0.95rem;">Tháng <?php echo $display_month; ?></div>
    </div>
    
    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Số khách hàng được miễn giảm</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;color:#10b981;">
            <?php echo formatNumber($total_stats['total_customers'] ?? 0); ?>
        </div>
        <div style="color:#666;font-size:0.95rem;">Khách hàng</div>
    </div>
    
    <div class="stat-card" style="padding:24px 18px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="font-size:1.1rem;color:#222;font-weight:600;">Tổng số đơn đã phê duyệt</div>
        <div style="font-size:2rem;font-weight:700;margin:8px 0;color:#f59e0b;">
            <?php echo formatNumber($total_stats['total_applications'] ?? 0); ?>
        </div>
        <div style="color:#666;font-size:0.95rem;">Đơn xin miễn giảm</div>
    </div>
</div>

<!-- Report Table -->
<div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <div style="padding:24px;border-bottom:1px solid #eee;">
        <h3 style="margin:0;font-size:1.25rem;font-weight:600;">Báo cáo chi tiết theo cấp phê duyệt</h3>
        <div style="color:#666;font-size:0.95rem;margin-top:4px;">Tháng <?php echo $display_month; ?></div>
    </div>
    
    <?php if (empty($report_data)): ?>
        <div style="padding:48px;text-align:center;color:#666;">
            <i class="fas fa-chart-bar" style="font-size:3rem;margin-bottom:16px;color:#ddd;"></i>
            <p>Không có dữ liệu báo cáo cho tháng này</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">STT</th>
                        <th style="padding:16px;text-align:left;font-weight:600;color:#333;border-bottom:1px solid #eee;">Cấp phê duyệt</th>
                        <th style="padding:16px;text-align:right;font-weight:600;color:#333;border-bottom:1px solid #eee;">Tổng số tiền đã phê duyệt</th>
                        <th style="padding:16px;text-align:right;font-weight:600;color:#333;border-bottom:1px solid #eee;">Số lượng HS đã duyệt</th>
                        <th style="padding:16px;text-align:right;font-weight:600;color:#333;border-bottom:1px solid #eee;">Hạn mức còn lại</th>
                        <th style="padding:16px;text-align:center;font-weight:600;color:#333;border-bottom:1px solid #eee;">Tỷ lệ sử dụng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $index => $row): ?>
                        <?php 
                        $remaining_limit = $row['approval_limit'] - $row['total_approved_amount'];
                        $usage_percentage = ($row['total_approved_amount'] / $row['approval_limit']) * 100;
                        $usage_color = $usage_percentage > 80 ? '#dc3545' : ($usage_percentage > 60 ? '#f59e0b' : '#10b981');
                        ?>
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:16px;font-weight:600;color:#333;">
                                <?php echo $index + 1; ?>
                            </td>
                            <td style="padding:16px;">
                                <div style="font-weight:600;color:#2563eb;"><?php echo htmlspecialchars($row['level_name']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">Cấp <?php echo $row['current_approval_level']; ?>/<?php echo $row['total_approval_levels']; ?></div>
                            </td>
                            <td style="padding:16px;text-align:right;">
                                <div style="font-weight:600;color:#dc3545;"><?php echo formatCurrencyVND($row['total_approved_amount']); ?></div>
                                <div style="font-size:0.875rem;color:#666;"><?php echo formatNumber($row['unique_customers']); ?> khách hàng</div>
                            </td>
                            <td style="padding:16px;text-align:right;">
                                <div style="font-weight:600;color:#10b981;"><?php echo formatNumber($row['total_applications']); ?></div>
                                <div style="font-size:0.875rem;color:#666;">Đơn xin miễn giảm</div>
                            </td>
                            <td style="padding:16px;text-align:right;">
                                <div style="font-weight:600;color:<?php echo $remaining_limit < 0 ? '#dc3545' : '#10b981'; ?>;">
                                    <?php echo formatCurrencyVND($remaining_limit); ?>
                                </div>
                                <div style="font-size:0.875rem;color:#666;">Hạn mức: <?php echo formatCurrencyVND($row['approval_limit']); ?></div>
                            </td>
                            <td style="padding:16px;text-align:center;">
                                <div style="font-weight:600;color:<?php echo $usage_color; ?>;">
                                    <?php echo number_format($usage_percentage, 1); ?>%
                                </div>
                                <div style="margin-top:4px;">
                                    <div style="width:100px;height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;margin:0 auto;">
                                        <div style="width:<?php echo min(100, $usage_percentage); ?>%;height:100%;background:<?php echo $usage_color; ?>;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Chart Section -->
<?php if (!empty($report_data)): ?>
<div style="background:#fff;border-radius:12px;padding:24px;margin-top:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
    <h3 style="margin:0 0 24px 0;font-size:1.25rem;font-weight:600;">Biểu đồ phân tích</h3>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <!-- Chart 1: Amount by Level -->
        <div>
            <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;">Số tiền phê duyệt theo cấp</h4>
            <canvas id="amountChart" width="400" height="200"></canvas>
        </div>
        
        <!-- Chart 2: Applications by Level -->
        <div>
            <h4 style="margin:0 0 16px 0;font-size:1.1rem;font-weight:600;color:#333;">Số lượng đơn theo cấp</h4>
            <canvas id="applicationsChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.badge {
    padding:4px 8px;border-radius:4px;font-size:0.875rem;font-weight:600;
}
.bg-primary {background:#dbeafe;color:#1e40af;}
.bg-success {background:#d1fae5;color:#065f46;}
.bg-info {background:#dbeafe;color:#1e40af;}
.bg-warning {background:#fef3c7;color:#92400e;}
.bg-danger {background:#fee2e2;color:#991b1b;}
.bg-secondary {background:#f3f4f6;color:#374151;}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function exportToExcel() {
    // Tạo dữ liệu cho Excel
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "Báo cáo MGLP - Tháng <?php echo $display_month; ?>\n";
    csvContent += "STT,Cấp phê duyệt,Tổng số tiền đã phê duyệt,Số lượng HS đã duyệt,Hạn mức còn lại,Tỷ lệ sử dụng\n";
    
    <?php foreach ($report_data as $index => $row): ?>
    <?php 
    $remaining_limit = $row['approval_limit'] - $row['total_approved_amount'];
    $usage_percentage = ($row['total_approved_amount'] / $row['approval_limit']) * 100;
    ?>
    csvContent += "<?php echo $index + 1; ?>,<?php echo $row['level_name']; ?>,<?php echo $row['total_approved_amount']; ?>,<?php echo $row['total_applications']; ?>,<?php echo $remaining_limit; ?>,<?php echo number_format($usage_percentage, 1); ?>%\n";
    <?php endforeach; ?>
    
    // Tạo link download
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "bao_cao_mglp_<?php echo $selected_month; ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

<?php if (!empty($report_data)): ?>
// Chart data
const chartData = {
    labels: [<?php echo implode(',', array_map(function($row) { return '"' . $row['level_name'] . '"'; }, $report_data)); ?>],
    amounts: [<?php echo implode(',', array_map(function($row) { return $row['total_approved_amount']; }, $report_data)); ?>],
    applications: [<?php echo implode(',', array_map(function($row) { return $row['total_applications']; }, $report_data)); ?>]
};

// Amount Chart
const amountCtx = document.getElementById('amountChart').getContext('2d');
new Chart(amountCtx, {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Số tiền phê duyệt (VND)',
            data: chartData.amounts,
            backgroundColor: '#2563eb',
            borderColor: '#1e40af',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VND';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Applications Chart
const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
new Chart(applicationsCtx, {
    type: 'doughnut',
    data: {
        labels: chartData.labels,
        datasets: [{
            data: chartData.applications,
            backgroundColor: [
                '#2563eb',
                '#10b981',
                '#f59e0b',
                '#dc3545',
                '#8b5cf6',
                '#06b6d4'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script> 