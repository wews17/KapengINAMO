<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireLogin();

// Only customers can create orders
if (!isCustomer()) {
    header('Location: /KapengINAMO/dashboard.php');
    exit();
}

$message = '';
$messageType = '';

// Fetch menu items by category
try {
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
} catch(PDOException $e) {
    $menu_items = [];
}

// Handle order creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $customer_name = trim($_POST['customer_name']);
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    $items = $_POST['items'];

    if (empty($items)) {
        $message = 'Please add at least one item to the order';
        $messageType = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            // Generate order number
            $order_number = 'ORD' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Calculate total
            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Insert order with customer name in notes
            $order_notes = "Customer: $customer_name";
            if ($notes) {
                $order_notes .= "\nNotes: $notes";
            }

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $order_number, $total_amount, $payment_method, $order_notes]);
            $order_id = $pdo->lastInsertId();

            // Insert order items
            foreach ($items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price'], $subtotal]);
            }

            $pdo->commit();
            $message = 'Order created successfully! Order #' . $order_number;
            $messageType = 'success';
            // Clear order items after successful submission
            $orderItems = [];
        } catch(PDOException $e) {
            $pdo->rollBack();
            $message = 'Error creating order: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>Create New Order</h2>
    <a href="/KapengINAMO/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="order-form-container">
    <form id="orderForm" method="POST" action="">
        <div class="form-section">
            <h3>Customer Information</h3>
            <div class="form-group">
                <label for="customer_name">Customer Name</label>
                <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                    <option value="gotyme">GoTyme</option>
                    <option value="maya">Maya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Special instructions..."></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>Select Products</h3>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Categories</button>
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
                        <div class="menu-selection-grid">
                            <?php foreach ($category_data['items'] as $item): ?>
                                <div class="product-item" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-price="<?php echo $item['price']; ?>">
                                    <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="product-price">₱<?php echo number_format($item['price'], 2); ?></span>
                                    <div class="product-quantity">
                                        <button type="button" class="btn btn-sm btn-secondary qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                        <input type="number" class="qty-input" id="qty-<?php echo $item['id']; ?>" value="0" min="0" data-item-id="<?php echo $item['id']; ?>" onchange="updateOrderItems()">
                                        <button type="button" class="btn btn-sm btn-secondary qty-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="form-section">
            <h3>Order Summary</h3>
            <div id="orderItems">
                <p class="no-data">No items selected yet.</p>
            </div>
            <div class="order-total">
                <h3>Total: ₱<span id="totalAmount">0.00</span></h3>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-large">Place Order</button>
    </form>
</div>

<script>
let orderItems = [];

function updateQuantity(itemId, change) {
    const input = document.getElementById('qty-' + itemId);
    let currentValue = parseInt(input.value) || 0;
    let newValue = currentValue + change;

    if (newValue < 0) newValue = 0;
    input.value = newValue;

    updateOrderItems();
}

function updateOrderItems() {
    orderItems = [];
    const inputs = document.querySelectorAll('.qty-input');

    inputs.forEach(input => {
        const qty = parseInt(input.value) || 0;
        if (qty > 0) {
            const productItem = input.closest('.product-item');
            orderItems.push({
                id: productItem.dataset.id,
                name: productItem.dataset.name,
                price: parseFloat(productItem.dataset.price),
                quantity: qty
            });
        }
    });

    renderOrderItems();
}

function renderOrderItems() {
    const container = document.getElementById('orderItems');
    const totalAmountEl = document.getElementById('totalAmount');

    if (orderItems.length === 0) {
        container.innerHTML = '<p class="no-data">No items selected yet.</p>';
        totalAmountEl.textContent = '0.00';
        return;
    }

    let html = '<div class="order-items-list">';
    let total = 0;

    orderItems.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        html += `
            <div class="order-item">
                <div class="item-info">
                    <strong>${item.name}</strong>
                    <span>₱${item.price.toFixed(2)} x ${item.quantity}</span>
                </div>
                <div class="item-actions">
                    <span class="item-subtotal">₱${subtotal.toFixed(2)}</span>
                    <button type="button" class="btn btn-sm btn-delete" onclick="removeItem(${index})">Remove</button>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
    totalAmountEl.textContent = total.toFixed(2);
}

function removeItem(index) {
    const item = orderItems[index];
    const input = document.getElementById('qty-' + item.id);
    input.value = 0;
    updateOrderItems();
}

document.getElementById('orderForm').addEventListener('submit', function(e) {
    if (orderItems.length === 0) {
        e.preventDefault();
        alert('Please select at least one item to order');
        return;
    }

    // Add items as hidden fields
    const itemsInput = document.createElement('input');
    itemsInput.type = 'hidden';
    itemsInput.name = 'items';
    itemsInput.value = JSON.stringify(orderItems);
    this.appendChild(itemsInput);
});

// Add form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('orderForm');
    preventDoubleSubmit(form);

    const customerNameInput = document.getElementById('customer_name');
    const paymentMethodInput = document.getElementById('payment_method');

    addValidation(customerNameInput, validateFullName, 'Customer name must be 2-50 characters (letters and spaces only)');

    paymentMethodInput.addEventListener('change', function() {
        if (!this.value) {
            showError(this, 'Please select a payment method');
        } else {
            clearError(this);
        }
    });

    // Menu filtering functionality
    const filterButtons = document.querySelectorAll('.filter-buttons .filter-btn');
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
<?php require_once '../includes/footer.php'; ?>
