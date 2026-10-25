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

// Fetch all orders with user details and calculated total
try {
    $orders = $pdo->query("
        SELECT o.*, u.full_name, u.email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
               COALESCE(
                   (SELECT SUM(subtotal) FROM order_items WHERE order_id = o.id),
                   o.total_amount,
                   0
               ) as calculated_total,
               (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = o.user_id AND status = 'completed') as customer_total_spent
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ")->fetchAll();

    // Calculate total revenue from completed orders
    $total_revenue = 0;
    foreach ($orders as $order) {
        if ($order['status'] === 'completed') {
            $total_revenue += floatval($order['total_amount']);
        }
    }
} catch(PDOException $e) {
    $orders = [];
    $total_revenue = 0;
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
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <h3>₱<?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Revenue</p>
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
                        <th>Order Total</th>
                        <th>Customer Total</th>
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
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                    <?php echo $order['item_count']; ?> items
                                </button>
                            </td>
                            <td>₱<?php echo number_format($order['calculated_total'], 2); ?></td>
                            <td>₱<?php echo number_format(floatval($order['customer_total_spent']), 2); ?></td>
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

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Order Details</h3>
            <span class="close" onclick="closeOrderDetailsModal()">&times;</span>
        </div>
        <div id="orderDetailsContent">
            <p>Loading...</p>
        </div>
    </div>
</div>

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

.order-details-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.order-details-table th,
.order-details-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.order-details-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.order-details-table tr:last-child td {
    border-bottom: none;
}

.order-summary {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #ddd;
}

.order-summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.order-summary-row.total {
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 1rem;
    padding-top: 0.5rem;
    border-top: 1px solid #ddd;
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

function viewOrderDetails(orderId) {
    document.getElementById('orderDetailsModal').style.display = 'block';
    document.getElementById('orderDetailsContent').innerHTML = '<p>Loading...</p>';

    // Fetch order details via AJAX
    fetch('/KapengINAMO/admin/api/order_details.php?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('orderDetailsContent').innerHTML = '<p class="text-muted">' + data.error + '</p>';
                return;
            }

            let itemsHtml = '<table class="order-details-table">' +
                '<thead>' +
                '<tr>' +
                '<th>Item</th>' +
                '<th>Quantity</th>' +
                '<th>Price</th>' +
                '<th>Subtotal</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';

            data.items.forEach(item => {
                itemsHtml += '<tr>' +
                    '<td>' + htmlspecialchars(item.name) + '</td>' +
                    '<td>' + item.quantity + '</td>' +
                    '<td>₱' + numberFormat(item.price, 2) + '</td>' +
                    '<td>₱' + numberFormat(item.subtotal, 2) + '</td>' +
                    '</tr>';
            });

            itemsHtml += '</tbody></table>';

            const summaryHtml = '<div class="order-summary">' +
                '<div class="order-summary-row">' +
                '<span>Order Number:</span>' +
                '<strong>' + htmlspecialchars(data.order.order_number) + '</strong>' +
                '</div>' +
                '<div class="order-summary-row">' +
                '<span>Customer:</span>' +
                '<strong>' + htmlspecialchars(data.order.full_name || 'Guest') + '</strong>' +
                '</div>' +
                '<div class="order-summary-row">' +
                '<span>Date:</span>' +
                '<span>' + formatDate(data.order.created_at) + '</span>' +
                '</div>' +
                '<div class="order-summary-row">' +
                '<span>Payment Method:</span>' +
                '<span>' + htmlspecialchars(data.order.payment_method) + '</span>' +
                '</div>' +
                '<div class="order-summary-row">' +
                '<span>Status:</span>' +
                '<span class="badge badge-' + data.order.status + '">' + ucfirst(data.order.status) + '</span>' +
                '</div>' +
                (data.order.notes ? '<div class="order-summary-row"><span>Notes:</span><span>' + htmlspecialchars(data.order.notes) + '</span></div>' : '') +
                '<div class="order-summary-row">' +
                '<span>Customer Total Spent:</span>' +
                '<strong>₱' + numberFormat(data.customer_total_spent, 2) + '</strong>' +
                '</div>' +
                '<div class="order-summary-row total">' +
                '<span>Order Total:</span>' +
                '<span>₱' + numberFormat(data.order.calculated_total, 2) + '</span>' +
                '</div>' +
                '</div>';

            document.getElementById('orderDetailsContent').innerHTML = itemsHtml + summaryHtml;
        })
        .catch(error => {
            document.getElementById('orderDetailsContent').innerHTML = '<p class="text-muted">Error loading order details: ' + error + '</p>';
        });
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('orderDetailsModal');
    if (event.target === modal) {
        closeOrderDetailsModal();
    }
}

// Helper functions
function htmlspecialchars(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function numberFormat(num, decimals) {
    const number = parseFloat(num);
    if (isNaN(number)) return '0.00';
    return number.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>
<?php require_once '../includes/footer.php'; ?>
