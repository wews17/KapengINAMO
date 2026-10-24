<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireLogin();

$message = '';
$messageType = '';

// Handle order cancellation and feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'cancel';
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    try {
        if ($action === 'feedback') {
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
            $comment = trim($_POST['comment'] ?? '');
            $commentLength = mb_strlen($comment, 'UTF-8');
            $hasEmoji = preg_match('/[\p{Extended_Pictographic}\p{Emoji_Presentation}\x{FE0F}\x{200D}\x{20E3}]/u', $comment);

            $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'completed'");
            $stmt->execute([$order_id, $user_id]);

            if (!$order_id || !$stmt->fetch()) {
                $message = 'Invalid order or order not completed yet.';
                $messageType = 'error';
            } elseif (!$rating || $rating < 1 || $rating > 5) {
                $message = 'Please select a star rating from 1 to 5.';
                $messageType = 'error';
            } elseif ($comment === '' || $commentLength > 50) {
                $message = 'Your comment must be between 1 and 50 characters.';
                $messageType = 'error';
            } elseif ($hasEmoji) {
                $message = 'Emojis are not allowed in feedback comments.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM order_feedback WHERE order_id = ? AND user_id = ?");
                $stmt->execute([$order_id, $user_id]);
                if ($stmt->fetch()) {
                    $message = 'You have already submitted feedback for this order.';
                    $messageType = 'error';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO order_feedback (order_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$order_id, $user_id, $rating, $comment]);
                    $message = 'Thank you for your feedback!';
                    $messageType = 'success';
                }
            }
        } else {
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
        }
    } catch(PDOException $e) {
        $message = 'Error cancelling order: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch user's orders with items
try {
    $orders = $pdo->prepare("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
               (SELECT COUNT(*) FROM order_feedback WHERE order_id = o.id AND user_id = ?) as has_feedback
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $orders->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $orders = $orders->fetchAll();
} catch(PDOException $e) {
    $orders = [];
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
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                            <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <?php if ($order['has_feedback'] > 0): ?>
                                        <span class="badge badge-completed">Submitted</span>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-primary" onclick='openFeedbackModal(<?php echo (int) $order['id']; ?>, <?php echo json_encode($order['order_number']); ?>)'>Give Feedback</button>
                                    <?php endif; ?>
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
    <input type="hidden" name="action" value="cancel">
    <input type="hidden" name="order_id" id="cancelOrderId">
</form>

<div id="feedbackModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="feedbackTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="feedbackTitle">Submit Feedback</h3>
            <button type="button" class="close" aria-label="Close feedback form" onclick="closeFeedbackModal()">&times;</button>
        </div>
        <form id="feedbackForm" method="POST" action="">
            <input type="hidden" name="action" value="feedback">
            <input type="hidden" name="order_id" id="feedbackOrderId">
            <p><strong>Order:</strong> <span id="feedbackOrderNumber"></span></p>
            <div class="form-group">
                <label>Star rating</label>
                <div class="star-rating" role="radiogroup" aria-label="Star rating">
                    <?php for ($star = 1; $star <= 5; $star++): ?>
                        <input type="radio" id="rating-<?php echo $star; ?>" name="rating" value="<?php echo $star; ?>" required>
                        <label for="rating-<?php echo $star; ?>" title="<?php echo $star; ?> star<?php echo $star > 1 ? 's' : ''; ?>">&#9733;</label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="feedbackComment">Your comment</label>
                <textarea id="feedbackComment" name="comment" rows="4" maxlength="50" placeholder="Share your experience with this order..." required></textarea>
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
.star-rating { display: inline-flex; flex-direction: row-reverse; gap: .2rem; }
.star-rating input { position: absolute; opacity: 0; }
.star-rating label { color: #c7c7c7; cursor: pointer; font-size: 2rem; line-height: 1; }
.star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #d4a017; }
#commentCount { color: #6c757d; display: block; margin-top: .35rem; }
</style>

<script>
function cancelOrder(orderId) {
    if (confirmAction('Are you sure you want to cancel this order? This action cannot be undone.')) {
        document.getElementById('cancelOrderId').value = orderId;
        document.getElementById('cancelForm').submit();
    }
}

function openFeedbackModal(orderId, orderNumber) {
    document.getElementById('feedbackOrderId').value = orderId;
    document.getElementById('feedbackOrderNumber').textContent = orderNumber;
    document.getElementById('feedbackModal').style.display = 'block';
    document.getElementById('feedbackComment').focus();
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
}

document.getElementById('feedbackComment').addEventListener('input', function () {
    const emojiPattern = /[\p{Extended_Pictographic}\p{Emoji_Presentation}\uFE0F\u200D\u20E3]/gu;
    const withoutEmoji = this.value.replace(emojiPattern, '');
    if (this.value !== withoutEmoji) {
        this.value = withoutEmoji;
    }
    document.getElementById('commentCount').textContent = `${Array.from(this.value).length}/50 characters. Emojis are not allowed.`;
});

window.addEventListener('click', function (event) {
    if (event.target === document.getElementById('feedbackModal')) {
        closeFeedbackModal();
    }
});

document.getElementById('feedbackForm').addEventListener('submit', function (event) {
    const comment = document.getElementById('feedbackComment').value.trim();
    if (!comment) {
        event.preventDefault();
        alert('Please enter a comment.');
    }
}
</script>
<?php require_once '../includes/footer.php'; ?>
