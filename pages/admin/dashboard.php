<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Lấy thống kê từ database
$db = getDB();

// Tổng số khách hàng
$totalCustomers = $db->fetchOne("SELECT COUNT(*) as count FROM customers")['count'] ?? 0;

// Tổng số hợp đồng
$totalContracts = $db->fetchOne("SELECT COUNT(*) as count FROM contracts")['count'] ?? 0;

// Tổng số tài sản
$totalAssets = $db->fetchOne("SELECT COUNT(*) as count FROM assets")['count'] ?? 0;

// Tổng doanh thu tháng này
$currentMonth = date('Y-m');
$monthlyRevenue = $db->fetchOne("SELECT SUM(amount) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?", [$currentMonth])['total'] ?? 0;

// Hợp đồng sắp đến hạn (trong 7 ngày tới)
$upcomingDue = $db->fetchAll("SELECT c.*, cu.name as customer_name FROM contracts c JOIN customers cu ON c.customer_id = cu.id WHERE c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND c.status = 'active' LIMIT 5");

// Thanh toán gần đây
$recentPayments = $db->fetchAll("SELECT p.*, c.contract_code, cu.name as customer_name FROM payments p JOIN contracts c ON p.contract_id = c.id JOIN customers cu ON c.customer_id = cu.id ORDER BY p.payment_date DESC LIMIT 5");

// Thống kê theo trạng thái hợp đồng
$contractStats = $db->fetchAll("SELECT status, COUNT(*) as count FROM contracts GROUP BY status");

// Thống kê theo tháng
$monthlyStats = $db->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM contracts GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 12");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <p class="mb-4">Tổng quan hệ thống quản lý cho vay cầm cố</p>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stats-number"><?php echo number_format($totalCustomers); ?></div>
                        <div class="stats-label">Tổng khách hàng</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stats-number"><?php echo number_format($totalContracts); ?></div>
                        <div class="stats-label">Hợp đồng đang hoạt động</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stats-number"><?php echo number_format($totalAssets); ?></div>
                        <div class="stats-label">Tài sản cầm cố</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stats-number"><?php echo format_currency($monthlyRevenue); ?></div>
                        <div class="stats-label">Doanh thu tháng này</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và bảng -->
    <div class="row">
        <!-- Biểu đồ doanh thu -->
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Biểu đồ hợp đồng theo tháng</h6>
                </div>
                <div class="card-body">
                    <canvas id="contractChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Hợp đồng sắp đến hạn -->
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Hợp đồng sắp đến hạn</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingDue)): ?>
                        <p class="text-muted">Không có hợp đồng nào sắp đến hạn</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($upcomingDue as $contract): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($contract['contract_code']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($contract['customer_name']); ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            Đến hạn: <?php echo format_date($contract['end_date']); ?>
                                        </small>
                                    </div>
                                    <span class="badge badge-warning">
                                        <?php echo format_currency($contract['amount']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê trạng thái hợp đồng -->
    <div class="row mt-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê trạng thái hợp đồng</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Thanh toán gần đây -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Thanh toán gần đây</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPayments)): ?>
                        <p class="text-muted">Chưa có thanh toán nào</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th>Hợp đồng</th>
                                        <th>Số tiền</th>
                                        <th>Ngày</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['contract_code']); ?></td>
                                            <td><?php echo format_currency($payment['amount']); ?></td>
                                            <td><?php echo format_date($payment['payment_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ hợp đồng theo tháng
    const contractCtx = document.getElementById('contractChart').getContext('2d');
    new Chart(contractCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthlyStats, 'month')); ?>,
            datasets: [{
                label: 'Số hợp đồng',
                data: <?php echo json_encode(array_column($monthlyStats, 'count')); ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Biểu đồ hợp đồng theo tháng'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Biểu đồ trạng thái hợp đồng
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($contractStats, 'status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($contractStats, 'count')); ?>,
                backgroundColor: [
                    'rgb(75, 192, 192)',
                    'rgb(255, 205, 86)',
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Phân bố trạng thái hợp đồng'
                }
            }
        }
    });
</script>