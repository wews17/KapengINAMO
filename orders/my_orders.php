<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireLogin();

$message = '';
$messageType = '';

// Check for success message from feedback submission
if (isset($_GET['success']) && $_GET['success'] === 'feedback') {
    $message = 'Thank you for your feedback!';
    $messageType = 'success';
}

// Check for success message from order creation
if (isset($_GET['success']) && $_GET['success'] === 'order_created') {
    $message = 'Order created successfully!';
    $messageType = 'success';
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Verify order belongs to user and can be cancelled
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();
        if (!$order) {
            $message = 'Order not found.';
            $messageType = 'error';
        } elseif ($order['status'] === 'completed' || $order['status'] === 'cancelled') {
            $message = 'Cannot cancel a ' . $order['status'] . ' order.';
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$order_id]);
            $message = 'Order cancelled successfully!';
            $messageType = 'success';
        }
    } catch(PDOException $e) {
        $message = 'Error cancelling order: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch user's orders with items and calculated total
try {
    $orders = $pdo->prepare("
        SELECT o.*,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
               COALESCE(
                   (SELECT SUM(subtotal) FROM order_items WHERE order_id = o.id),
                   o.total_amount,
                   0
               ) as calculated_total
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $orders->execute([$_SESSION['user_id']]);
    $orders = $orders->fetchAll();

    // Fetch user's total lifetime spending
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    $total_spent = floatval($stmt->fetch()['total_spent']);
} catch(PDOException $e) {
    $orders = [];
    $total_spent = 0;
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>My Orders</h2>
    <a href="/KapengINAMO/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <h3><?php echo count($orders); ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'pending' || $o['status'] === 'preparing' || $o['status'] === 'ready')); ?></h3>
            <p>Active Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <h3><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'completed')); ?></h3>
            <p>Completed Orders</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <h3>₱<?php echo number_format($total_spent, 2); ?></h3>
            <p>Total Spent</p>
        </div>
    </div>
</div>

<div class="dashboard-section">
    <h3>Order History</h3>
    <?php if (empty($orders)): ?>
        <p class="no-data">No orders found. <a href="/KapengINAMO/orders/create.php">Place your first order!</a></p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td>₱<?php echo number_format($order['calculated_total'], 2); ?></td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                            <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openFeedbackModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>')">Give Feedback</button>
                                <?php elseif ($order['status'] !== 'cancelled'): ?>
                                    <button class="btn btn-sm btn-delete" onclick="cancelOrder(<?php echo $order['id']; ?>)">Cancel Order</button>
                                <?php else: ?>
                                    <span class="text-muted">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Hidden form for cancellation -->
<form id="cancelForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="order_id" id="cancelOrderId">
</form>

<!-- Feedback Modal -->
<div id="feedbackModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Submit Feedback</h3>
            <span class="close" onclick="closeFeedbackModal()">&times;</span>
        </div>
        <form id="feedbackForm" method="POST" action="/KapengINAMO/orders/feedback.php">
            <input type="hidden" name="order_id" id="orderId">
            <p><strong>Order:</strong> <span id="orderNumber"></span></p>
            <div class="form-group">
                <label for="rating">Rating (1-5)</label>
                <select id="rating" name="rating" required>
                    <option value="">Select Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                    <option value="4">⭐⭐⭐⭐ - Very Good</option>
                    <option value="3">⭐⭐⭐ - Good</option>
                    <option value="2">⭐⭐ - Fair</option>
                    <option value="1">⭐ - Poor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="comment">Your Comment</label>
                <textarea id="comment" name="comment" rows="4" maxlength="50" placeholder="Share your experience with this order..." required></textarea>
                <small id="commentCount">0/50 characters. Emojis are not allowed.</small>
            </div>
            <button type="submit" class="btn btn-primary">Submit Feedback</button>
        </form>
    </div>
</div>

<style>
.text-muted {
    color: #7f8c8d;
}
</style>

<script>
function cancelOrder(orderId) {
    if (confirmAction('Are you sure you want to cancel this order? This action cannot be undone.')) {
        document.getElementById('cancelOrderId').value = orderId;
        document.getElementById('cancelForm').submit();
    }
}

function openFeedbackModal(orderId, orderNumber) {
    document.getElementById('orderId').value = orderId;
    document.getElementById('orderNumber').textContent = orderNumber;
    document.getElementById('feedbackModal').style.display = 'block';
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('feedbackModal');
    if (event.target === modal) {
        closeFeedbackModal();
    }
}

// Add form validation and emoji restriction
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedbackForm');
    preventDoubleSubmit(form);

    const ratingInput = document.getElementById('rating');
    const commentInput = document.getElementById('comment');

    addValidation(ratingInput, validateRating, 'Please select a rating between 1 and 5');

    addValidation(commentInput, function(value) {
        return value.trim().length > 0 && Array.from(value).length <= 50;
    }, 'Comment must be 50 characters or fewer');

    commentInput.addEventListener('input', function() {
        const emojiPattern = /[\p{Extended_Pictographic}\p{Emoji_Presentation}\uFE0F\u200D\u20E3]/gu;
        const withoutEmoji = this.value.replace(emojiPattern, '');
        if (this.value !== withoutEmoji) {
            this.value = withoutEmoji;
        }
        document.getElementById('commentCount').textContent = `${Array.from(this.value).length}/50 characters. Emojis are not allowed.`;
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
