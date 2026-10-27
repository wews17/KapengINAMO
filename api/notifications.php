<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Fetch notifications for current user
        $stmt = $pdo->prepare("
            SELECT n.*, o.order_number
            FROM notifications n
            LEFT JOIN orders o ON n.order_id = o.id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll();

        // Count unread notifications
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
        $stmt->execute([$_SESSION['user_id']]);
        $unread_count = $stmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error fetching notifications: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        $notification_id = $_POST['notification_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $_SESSION['user_id']]);

            echo json_encode(['success' => true]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error marking notification as read: ' . $e->getMessage()
            ]);
        }
    } elseif ($action === 'mark_all_read') {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            echo json_encode(['success' => true]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error marking all notifications as read: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>
