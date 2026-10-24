<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireLogin();

$message = '';
$messageType = '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    $commentLength = mb_strlen($comment, 'UTF-8');
    $hasEmoji = preg_match('/[\p{Extended_Pictographic}\p{Emoji_Presentation}\x{FE0F}\x{200D}\x{20E3}]/u', $comment);

    try {
        // Check if order belongs to user and is completed
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'completed'");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();

        if (!$order) {
            $message = 'Invalid order or order not completed yet.';
            $messageType = 'error';
        } elseif (!$rating || $rating < 1 || $rating > 5) {
            $message = 'Please select a rating from 1 to 5.';
            $messageType = 'error';
        } elseif ($comment === '' || $commentLength > 50) {
            $message = 'Your comment must be between 1 and 50 characters.';
            $messageType = 'error';
        } elseif ($hasEmoji) {
            $message = 'Emojis are not allowed in feedback comments.';
            $messageType = 'error';
        } else {
            // Check if feedback already exists
            $stmt = $pdo->prepare("SELECT * FROM order_feedback WHERE order_id = ? AND user_id = ?");
            $stmt->execute([$order_id, $user_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                $message = 'You have already submitted feedback for this order.';
                $messageType = 'error';
            } else {
                // Insert feedback
                $stmt = $pdo->prepare("INSERT INTO order_feedback (order_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $user_id, $rating, $comment]);
                $message = 'Thank you for your feedback!';
                $messageType = 'success';
            }
        }
    } catch(PDOException $e) {
        $message = 'Error submitting feedback: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch user's completed orders without feedback
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_feedback WHERE order_id = o.id AND user_id = ?) as has_feedback
        FROM orders o
        WHERE o.user_id = ? AND o.status = 'completed'
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    $orders = [];
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>Order Feedback</h2>
    <a href="/KapengINAMO/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="dashboard-section">
    <h3>Your Completed Orders</h3>
    <?php if (empty($orders)): ?>
        <p class="no-data">No completed orders found.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td>
                                <?php if ($order['has_feedback'] > 0): ?>
                                    <span class="badge badge-completed">Submitted</span>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary" onclick="openFeedbackModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>')">Give Feedback</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Submit Feedback</h3>
            <span class="close" onclick="closeFeedbackModal()">&times;</span>
        </div>
        <form id="feedbackForm" method="POST" action="">
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

<script>
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

// Add form validation
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
