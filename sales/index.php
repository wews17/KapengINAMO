<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireAdmin();

// Handle export requests
if (isset($_GET['export'])) {
    $period = $_GET['export'];
    $filename = 'sales_' . $period . '_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Order Number', 'Amount', 'Date', 'Time']);

    try {
        if ($period === 'week') {
            $stmt = $pdo->query("SELECT s.*, o.order_number FROM sales s JOIN orders o ON s.order_id = o.id WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY s.sale_date DESC, s.sale_time DESC");
        } elseif ($period === 'month') {
            $stmt = $pdo->query("SELECT s.*, o.order_number FROM sales s JOIN orders o ON s.order_id = o.id WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY s.sale_date DESC, s.sale_time DESC");
        } elseif ($period === 'year') {
            $stmt = $pdo->query("SELECT s.*, o.order_number FROM sales s JOIN orders o ON s.order_id = o.id WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) ORDER BY s.sale_date DESC, s.sale_time DESC");
        } else {
            $stmt = $pdo->query("SELECT s.*, o.order_number FROM sales s JOIN orders o ON s.order_id = o.id ORDER BY s.sale_date DESC, s.sale_time DESC");
        }

        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['id'], $row['order_number'], $row['amount'], $row['sale_date'], $row['sale_time']]);
        }
    } catch(PDOException $e) {
        // Handle error
    }

    fclose($output);
    exit();
}

// Fetch sales data for charts
try {
    // Daily sales for the last 7 days
    $daily_sales = $pdo->query("
        SELECT sale_date, SUM(amount) as total
        FROM sales
        WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY sale_date
        ORDER BY sale_date ASC
    ")->fetchAll();

    // Monthly sales for the last 12 months
    $monthly_sales = $pdo->query("
        SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(amount) as total
        FROM sales
        WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
        ORDER BY month ASC
    ")->fetchAll();

    // Total sales
    $total_sales = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM sales")->fetchColumn();
    $today_sales = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM sales WHERE sale_date = CURDATE()")->fetchColumn();
    $week_sales = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
    $month_sales = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetchColumn();

    // Recent sales
    $recent_sales = $pdo->query("SELECT s.*, o.order_number FROM sales s JOIN orders o ON s.order_id = o.id ORDER BY s.sale_date DESC, s.sale_time DESC LIMIT 10")->fetchAll();
} catch(PDOException $e) {
    $daily_sales = [];
    $monthly_sales = [];
    $total_sales = 0;
    $today_sales = 0;
    $week_sales = 0;
    $month_sales = 0;
    $recent_sales = [];
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="dashboard">
    <div class="page-header">
        <h2>Sales Dashboard</h2>
        <?php if (isAdmin()): ?>
            <div class="export-buttons">
                <a href="?export=week" class="btn btn-secondary">Export Week</a>
                <a href="?export=month" class="btn btn-secondary">Export Month</a>
                <a href="?export=year" class="btn btn-secondary">Export Year</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($total_sales, 2); ?></h3>
                <p>Total Sales</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($today_sales, 2); ?></h3>
                <p>Today's Sales</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($week_sales, 2); ?></h3>
                <p>This Week</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($month_sales, 2); ?></h3>
                <p>This Month</p>
            </div>
        </div>
    </div>

    <div class="charts-section">
        <div class="chart-container">
            <h3>Daily Sales (Last 7 Days)</h3>
            <canvas id="dailyChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Monthly Sales (Last 12 Months)</h3>
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <div class="dashboard-section">
        <h3>Recent Sales</h3>
        <?php if (empty($recent_sales)): ?>
            <p class="no-data">No sales data available.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['order_number']); ?></td>
                                <td>₱<?php echo number_format($sale['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                                <td><?php echo date('H:i', strtotime($sale['sale_time'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Daily Sales Chart
const dailyLabels = <?php echo json_encode(array_column($daily_sales, 'sale_date')); ?>;
const dailyData = <?php echo json_encode(array_column($daily_sales, 'total')); ?>;

const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Sales (₱)',
            data: dailyData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Monthly Sales Chart
const monthlyLabels = <?php echo json_encode(array_column($monthly_sales, 'month')); ?>;
const monthlyData = <?php echo json_encode(array_column($monthly_sales, 'total')); ?>;

const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Sales (₱)',
            data: monthlyData,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgb(54, 162, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
<?php require_once '../includes/footer.php'; ?>
