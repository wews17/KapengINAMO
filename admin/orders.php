<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireStaffOrAdmin();

$message = '';
$messageType = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $order_id = $_POST['order_id'];

    try {
        if ($action === 'update_status') {
            $status = $_POST['status'];
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);

            // If order is completed, add to sales
            if ($status === 'completed') {
                $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ?");
                $stmt->execute([$order_id]);
                $order = $stmt->fetch();

                // Check if sales record already exists
                $stmt = $pdo->prepare("SELECT id FROM sales WHERE order_id = ?");
                $stmt->execute([$order_id]);
                if (!$stmt->fetch()) {
                    $stmt = $pdo->prepare("INSERT INTO sales (order_id, amount, sale_date, sale_time) VALUES (?, ?, CURDATE(), CURTIME())");
                    $stmt->execute([$order_id, $order['total_amount']]);
                }
            }

            $message = 'Order status updated successfully!';
            $messageType = 'success';
        } elseif ($action === 'cancel') {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$order_id]);
            $message = 'Order cancelled successfully!';
            $messageType = 'success';
        }
    } catch(PDOException $e) {
        $message = 'Error updating order: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch all orders with user details
try {
    $orders = $pdo->query("
        SELECT o.*, u.full_name, u.email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ")->fetchAll();
} catch(PDOException $e) {
    $orders = [];
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>Order Management</h2>
    <a href="/KapengINAMO/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending')); ?></h3>
            <p>Pending Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👨‍🍳</div>
        <div class="stat-info">
            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'preparing')); ?></h3>
            <p>Preparing Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'ready')); ?></h3>
            <p>Ready Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'completed')); ?></h3>
            <p>Completed Orders</p>
        </div>
    </div>
</div>

<div class="dashboard-section">
    <h3>All Orders</h3>
    <?php if (empty($orders)): ?>
        <p class="no-data">No orders found.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></strong><br>
                                <small><?php echo htmlspecialchars($order['email'] ?? ''); ?></small>
                            </td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                            <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'): ?>
                                    <select class="status-select" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                        <option value="">Change Status</option>
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                        <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <button class="btn btn-sm btn-delete" onclick="cancelOrder(<?php echo $order['id']; ?>)">Cancel</button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Hidden form for status updates -->
<form id="statusForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="order_id" id="statusOrderId">
    <input type="hidden" name="status" id="statusValue">
</form>

<!-- Hidden form for cancellation -->
<form id="cancelForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="action" value="cancel">
    <input type="hidden" name="order_id" id="cancelOrderId">
</form>

<style>
.status-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.85rem;
    margin-right: 0.5rem;
}

.text-muted {
    color: #7f8c8d;
}
</style>

<script>
function updateOrderStatus(orderId, status) {
    if (!status) return;

    if (confirmAction('Are you sure you want to change the order status to ' + status + '?')) {
        document.getElementById('statusOrderId').value = orderId;
        document.getElementById('statusValue').value = status;
        document.getElementById('statusForm').submit();
    }
}

function cancelOrder(orderId) {
    if (confirmAction('Are you sure you want to cancel this order? This action cannot be undone.')) {
        document.getElementById('cancelOrderId').value = orderId;
        document.getElementById('cancelForm').submit();
    }
}
</script>
<?php require_once '../includes/footer.php'; ?>
