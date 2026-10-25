<?php
require_once '../../includes/db.php';
require_once '../../includes/session.php';

requireStaffOrAdmin();

header('Content-Type: application/json');

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    echo json_encode(['error' => 'Invalid order ID']);
    exit;
}

try {
    // Fetch order details with calculated total
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name, u.email,
               COALESCE(
                   (SELECT SUM(subtotal) FROM order_items WHERE order_id = o.id),
                   o.total_amount,
                   0
               ) as calculated_total
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Fetch order items
    $stmt = $pdo->prepare("
        SELECT oi.*, mi.name
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Fetch customer's total lifetime spending
    $customer_total = 0;
    if ($order['user_id']) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE user_id = ? AND status = 'completed'");
        $stmt->execute([$order['user_id']]);
        $result = $stmt->fetch();
        $customer_total = $result['total_spent'];
    }

    echo json_encode([
        'order' => $order,
        'items' => $items,
        'customer_total_spent' => $customer_total
    ]);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error fetching order details: ' . $e->getMessage()]);
}
