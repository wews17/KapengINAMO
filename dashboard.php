<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

requireLogin();

// Get statistics based on role
$stats = [];

try {
    if (isAdmin()) {
        // Admin stats
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stats['total_sales'] = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM sales")->fetchColumn();
        $stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $stats['menu_items'] = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

        // Recent orders with calculated total from order_items
        $recent_orders = $pdo->query("
            SELECT o.*, u.full_name,
               COALESCE(
                   (SELECT SUM(subtotal) FROM order_items WHERE order_id = o.id),
                   o.total_amount,
                   0
               ) as calculated_total
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ")->fetchAll();

        // Most favorite products (top ordered items)
        $favorite_products = $pdo->query("
            SELECT mi.id, mi.name, mi.price, mi.description, SUM(oi.quantity) as total_orders, COUNT(DISTINCT oi.order_id) as order_count
            FROM menu_items mi
            LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
            GROUP BY mi.id, mi.name, mi.price, mi.description
            HAVING total_orders > 0
            ORDER BY total_orders DESC
            LIMIT 4
        ")->fetchAll();
    } elseif (isStaff()) {
        // Staff stats
        $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $stats['preparing_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'preparing'")->fetchColumn();
        $stats['completed_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();

        // Recent orders with calculated total from order_items
        $recent_orders = $pdo->query("
            SELECT o.*, u.full_name,
               COALESCE(
                   (SELECT SUM(subtotal) FROM order_items WHERE order_id = o.id),
                   o.total_amount,
                   0
               ) as calculated_total
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ")->fetchAll();
    } else {
        // Customer stats
        $user_id = $_SESSION['user_id'];
        $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = $user_id")->fetchColumn();
        $stats['total_spent'] = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = $user_id")->fetchColumn();

        // Recent orders with calculated total from order_items
        $recent_orders = $pdo->query("
            SELECT o.*,
               COALESCE(
                   (SELECT SUM(subtotal) FROM order_items WHERE order_id = o.id),
                   o.total_amount,
                   0
               ) as calculated_total
            FROM orders o
            WHERE o.user_id = $user_id
            ORDER BY o.created_at DESC
            LIMIT 5
        ")->fetchAll();

        // Get menu items by category
        $menu_categories = $pdo->query("SELECT * FROM menu_categories ORDER BY name")->fetchAll();
        $menu_items = [];
        foreach ($menu_categories as $category) {
            $items = $pdo->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY name");
            $items->execute([$category['id']]);
            $menu_items[$category['id']] = [
                'category' => $category,
                'items' => $items->fetchAll()
            ];
        }
    }
} catch(PDOException $e) {
    $stats = [];
    $recent_orders = [];
}
?>
<?php require_once 'includes/header.php'; ?>
<div class="dashboard">
    <?php if (isAdmin()): ?>
        <div class="page-header">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! (Admin)</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_users'] ?? 0; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>₱<?php echo number_format($stats['total_sales'] ?? 0, 2); ?></h3>
                    <p>Total Sales</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">☕</div>
                <div class="stat-info">
                    <h3><?php echo $stats['menu_items'] ?? 0; ?></h3>
                    <p>Menu Items</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <h3>Most Favorite Products</h3>
            <?php if (empty($favorite_products)): ?>
                <p class="no-data">No product orders yet.</p>
            <?php else: ?>
                <div class="quick-actions">
                    <?php foreach ($favorite_products as $product): ?>
                        <div class="action-card">
                            <span class="action-icon">☕</span>
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p><?php echo htmlspecialchars($product['description'] ?? 'No description'); ?></p>
                            <p><strong>₱<?php echo number_format($product['price'], 2); ?></strong></p>
                            <p>Ordered <?php echo $product['total_orders']; ?> times in <?php echo $product['order_count']; ?> orders</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif (isStaff()): ?>
        <div class="page-header">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! (Staff)</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🍳</div>
                <div class="stat-info">
                    <h3><?php echo $stats['preparing_orders'] ?? 0; ?></h3>
                    <p>Preparing Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $stats['completed_orders'] ?? 0; ?></h3>
                    <p>Completed Orders</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <h3>Quick Actions</h3>
            <div class="quick-actions">

                <a href="/KapengINAMO/admin/orders.php" class="action-card">
                    <span class="action-icon">📋</span>
                    <h4>Manage Orders</h4>
                    <p>Process and update orders</p>
                </a>
                <a href="/KapengINAMO/menu/index.php" class="action-card">
                    <span class="action-icon">☕</span>
                    <h4>Manage Menu</h4>
                    <p>Add or update menu items</p>
                </a>
                <?php if (isAdmin()): ?>
                <a href="/KapengINAMO/sales/index.php" class="action-card">
                    <span class="action-icon">📊</span>
                    <h4>View Sales</h4>
                    <p>Analyze sales data</p>
                </a>
                <?php endif; ?>
                <a href="/KapengINAMO/settings/index.php" class="action-card">
                    <span class="action-icon">⚙️</span>
                    <h4>Settings</h4>
                    <p>Manage your profile and settings</p>
                </a>
            </div>
        </div>

        <div class="dashboard-section">
            <h3>Quick Actions</h3>
            <div class="quick-actions">

                <a href="/KapengINAMO/admin/orders.php" class="action-card">
                    <span class="action-icon">📋</span>
                    <h4>Manage Orders</h4>
                    <p>Process and update orders</p>
                </a>
                <a href="/KapengINAMO/menu/index.php" class="action-card">
                    <span class="action-icon">☕</span>
                    <h4>Manage Menu</h4>
                    <p>Add or update menu items</p>
                </a>
                <?php if (isAdmin()): ?>
                <a href="/KapengINAMO/sales/index.php" class="action-card">
                    <span class="action-icon">📊</span>
                    <h4>View Sales</h4>
                    <p>Analyze sales data</p>
                </a>
                <?php endif; ?>
                <a href="/KapengINAMO/settings/index.php" class="action-card">
                    <span class="action-icon">⚙️</span>
                    <h4>Settings</h4>
                    <p>Manage your profile and settings</p>
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="page-header">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! (Customer)</h2>
            <a href="/KapengINAMO/orders/feedback.php" class="btn btn-primary">Give Feedback</a>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>₱<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <h3>Our Menu</h3>
            <?php if (empty($menu_items)): ?>
                <p class="no-data">No menu items available.</p>
            <?php else: ?>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <?php foreach ($menu_items as $category_data): ?>
                        <?php if (!empty($category_data['items'])): ?>
                            <button class="filter-btn" data-filter="<?php echo $category_data['category']['id']; ?>">
                                <?php echo htmlspecialchars($category_data['category']['name']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($menu_items as $category_data): ?>
                    <?php if (!empty($category_data['items'])): ?>
                        <div class="menu-category-section" data-category="<?php echo $category_data['category']['id']; ?>">
                            <h4><?php echo htmlspecialchars($category_data['category']['name']); ?></h4>
                            <p class="menu-category-desc"><?php echo htmlspecialchars($category_data['category']['description'] ?? ''); ?></p>
                            <div class="menu-grid">
                                <?php foreach ($category_data['items'] as $item): ?>
                                    <a href="/KapengINAMO/orders/create.php" class="menu-card-link">
                                        <div class="menu-card">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="/KapengINAMO/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
                                            <?php endif; ?>
                                            <div class="menu-card-header">
                                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                            </div>
                                            <div class="menu-card-body">
                                                <p class="menu-description"><?php echo htmlspecialchars($item['description'] ?? 'No description'); ?></p>
                                            </div>
                                            <div class="menu-card-footer">
                                                <span class="menu-price">₱<?php echo number_format($item['price'], 2); ?></span>
                                                <span class="menu-hover-hint">Click to order</span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="dashboard-section">
            <h3>Quick Actions</h3>
            <div class="quick-actions">

                <a href="/KapengINAMO/orders/my_orders.php" class="action-card">
                    <span class="action-icon">📋</span>
                    <h4>My Orders</h4>
                    <p>View and manage your orders</p>
                </a>
                <a href="/KapengINAMO/orders/feedback.php" class="action-card">
                    <span class="action-icon">💬</span>
                    <h4>Give Feedback</h4>
                    <p>Rate your completed orders</p>
                </a>
                <a href="/KapengINAMO/settings/index.php" class="action-card">
                    <span class="action-icon">⚙️</span>
                    <h4>Settings</h4>
                    <p>Manage your profile</p>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="dashboard-section">
        <h3>Recent Orders</h3>
        <?php if (empty($recent_orders)): ?>
            <p class="no-data">No orders found.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <?php if (isAdmin() || isStaff()): ?>
                                <th>Customer</th>
                            <?php endif; ?>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <?php if (isAdmin() || isStaff()): ?>
                                    <td><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></td>
                                <?php endif; ?>
                                <td>₱<?php echo number_format($order['calculated_total'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (isAdmin() || isStaff()): ?>
                <div style="margin-top: 1rem; text-align: right;">
                    <a href="/KapengINAMO/admin/orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            <?php elseif (isCustomer()): ?>
                <div style="margin-top: 1rem; text-align: right;">
                    <a href="/KapengINAMO/orders/my_orders.php" class="btn btn-primary">View All Orders</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Menu filtering functionality for customer dashboard
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const categorySections = document.querySelectorAll('.menu-category-section');

    if (filterButtons.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');

                const filter = this.dataset.filter;

                // Show/hide category sections
                categorySections.forEach(section => {
                    if (filter === 'all' || section.dataset.category === filter) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>
