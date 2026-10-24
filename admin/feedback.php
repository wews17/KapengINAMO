<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireStaffOrAdmin();

// Fetch all feedback with order and user details
try {
    $feedback = $pdo->query("
        SELECT of.*, o.order_number, o.total_amount, u.full_name, u.email
        FROM order_feedback of
        JOIN orders o ON of.order_id = o.id
        JOIN users u ON of.user_id = u.id
        ORDER BY of.created_at DESC
    ")->fetchAll();
} catch(PDOException $e) {
    $feedback = [];
}

// Calculate average rating
try {
    $avg_rating = $pdo->query("SELECT AVG(rating) as avg_rating FROM order_feedback")->fetch()['avg_rating'] ?? 0;
    $total_feedback = $pdo->query("SELECT COUNT(*) FROM order_feedback")->fetchColumn();
} catch(PDOException $e) {
    $avg_rating = 0;
    $total_feedback = 0;
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>Customer Feedback</h2>
    <a href="/KapengINAMO/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⭐</div>
        <div class="stat-info">
            <h3><?php echo number_format($avg_rating, 1); ?>/5</h3>
            <p>Average Rating</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💬</div>
        <div class="stat-info">
            <h3><?php echo $total_feedback; ?></h3>
            <p>Total Feedback</p>
        </div>
    </div>
</div>

<div class="dashboard-section">
    <h3>All Feedback</h3>
    <?php if (empty($feedback)): ?>
        <p class="no-data">No feedback received yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedback as $fb): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fb['order_number']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($fb['full_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($fb['email']); ?></small>
                            </td>
                            <td>
                                <span class="rating-stars">
                                    <?php echo str_repeat('⭐', $fb['rating']); ?>
                                    <?php echo str_repeat('☆', 5 - $fb['rating']); ?>
                                </span>
                                <span class="rating-number"><?php echo $fb['rating']; ?>/5</span>
                            </td>
                            <td><?php echo htmlspecialchars($fb['comment'] ?? 'No comment'); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($fb['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
