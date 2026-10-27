<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireLogin();

$message = '';
$messageType = '';

function uploadMenuImage(): array {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['size'] > 5 * 1024 * 1024) {
        return [null, 'Please upload an image smaller than 5 MB.'];
    }

    $imageInfo = @getimagesize($_FILES['image']['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if ($imageInfo === false || !isset($extensions[$imageInfo['mime']])) {
        return [null, 'Only JPG, PNG, and WebP images are allowed.'];
    }

    $uploadDirectory = __DIR__ . '/../uploads/menu';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
        return [null, 'The image upload folder could not be created.'];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$imageInfo['mime']];
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDirectory . '/' . $filename)) {
        return [null, 'The image could not be uploaded. Please try again.'];
    }

    return ['uploads/menu/' . $filename, null];
}

// Handle menu actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add' && isAdmin()) {
        $category_id = $_POST['category_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        [$image, $uploadError] = isAdmin() ? uploadMenuImage() : [null, null];

        try {
            if ($uploadError) {
                throw new RuntimeException($uploadError);
            }
            $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, image, is_available) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $description, $price, $image, $is_available]);
            $message = 'Menu item added successfully!';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'Error adding menu item: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'edit' && isAdmin()) {
        $item_id = $_POST['item_id'];
        $category_id = $_POST['category_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        [$image, $uploadError] = uploadMenuImage();

        try {
            if ($uploadError) {
                throw new RuntimeException($uploadError);
            }
            $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image = COALESCE(?, image), is_available = ? WHERE id = ?");
            $stmt->execute([$category_id, $name, $description, $price, $image, $is_available, $item_id]);
            $message = 'Menu item updated successfully!';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'Error updating menu item: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete' && isAdmin()) {
        $item_id = $_POST['item_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$item_id]);
            $message = 'Menu item deleted successfully!';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'Error deleting menu item: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch categories and menu items
try {
    $categories = $pdo->query("SELECT * FROM menu_categories ORDER BY name")->fetchAll();
    $menu_items = $pdo->query("SELECT mi.*, mc.name as category_name FROM menu_items mi LEFT JOIN menu_categories mc ON mi.category_id = mc.id ORDER BY mc.name, mi.name")->fetchAll();
} catch(PDOException $e) {
    $categories = [];
    $menu_items = [];
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>Menu Management</h2>
    <?php if (isAdmin()): ?>
        <button class="btn btn-primary" onclick="openModal('add')">Add New Item</button>
    <?php endif; ?>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="filter-buttons">
    <button class="filter-btn active" data-filter="all">All Items</button>
    <button class="filter-btn" data-filter="available">Available</button>
    <button class="filter-btn" data-filter="unavailable">Unavailable</button>
</div>

<div class="category-filter-buttons">
    <button class="filter-btn active" data-category="all">All Categories</button>
    <?php foreach ($categories as $cat): ?>
        <button class="filter-btn" data-category="<?php echo $cat['id']; ?>">
            <?php echo htmlspecialchars($cat['name']); ?>
        </button>
    <?php endforeach; ?>
</div>

<div class="menu-grid">
    <?php foreach ($menu_items as $item): ?>
        <div class="menu-card" data-availability="<?php echo $item['is_available'] ? 'available' : 'unavailable'; ?>" data-category="<?php echo $item['category_id']; ?>">
            <?php if (!empty($item['image'])): ?>
                <img src="/KapengINAMO/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-image">
            <?php endif; ?>
            <div class="menu-card-header">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <span class="badge badge-<?php echo $item['is_available'] ? 'available' : 'unavailable'; ?>">
                    <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                </span>
            </div>
            <p class="menu-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
            <p class="menu-description"><?php echo htmlspecialchars($item['description']); ?></p>
            <div class="menu-card-footer">
                <span class="menu-price">₱<?php echo number_format($item['price'], 2); ?></span>
                <?php if (isAdmin()): ?>
                    <div class="menu-actions">
                        <button class="btn btn-sm btn-edit" onclick="openModal('edit', <?php echo $item['id']; ?>)">Edit</button>
                        <button class="btn btn-sm btn-delete" onclick="confirmDelete(<?php echo $item['id']; ?>)">Delete</button>
                    </div>
                <?php elseif (isCustomer()): ?>
                    <a href="/KapengINAMO/orders/create.php" class="btn btn-primary btn-sm">Order Now</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Menu Item Modal -->
<div id="menuModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Menu Item</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="menuForm" method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="item_id" id="itemId">
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (₱)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            <?php if (isAdmin()): ?>
                <div class="form-group">
                    <label for="image">Menu Image</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <small>JPG, PNG, or WebP; maximum 5 MB.</small>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_available" checked> Available
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<script>
const menuItems = <?php echo json_encode($menu_items); ?>;

function openModal(mode, itemId = null) {
    const modal = document.getElementById('menuModal');
    const title = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const itemIdField = document.getElementById('itemId');

    modal.style.display = 'block';

    if (mode === 'edit' && itemId) {
        const item = menuItems.find(i => i.id === itemId);
        if (item) {
            title.textContent = 'Edit Menu Item';
            formAction.value = 'edit';
            itemIdField.value = itemId;
            document.getElementById('category_id').value = item.category_id;
            document.getElementById('name').value = item.name;
            document.getElementById('description').value = item.description;
            document.getElementById('price').value = item.price;
            document.querySelector('input[name="is_available"]').checked = item.is_available;
        }
    } else {
        title.textContent = 'Add New Menu Item';
        formAction.value = 'add';
        itemIdField.value = '';
        document.getElementById('menuForm').reset();
    }

    // Add validation when modal opens
    setTimeout(addMenuFormValidation, 100);
}

function closeModal() {
    document.getElementById('menuModal').style.display = 'none';
}

function confirmDelete(itemId) {
    if (confirmAction('Are you sure you want to delete this menu item? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="item_id" value="${itemId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function addMenuFormValidation() {
    const form = document.getElementById('menuForm');
    preventDoubleSubmit(form);

    const nameInput = document.getElementById('name');
    const priceInput = document.getElementById('price');

    addValidation(nameInput, function(value) {
        return value.trim().length >= 2;
    }, 'Item name must be at least 2 characters');

    addValidation(priceInput, validatePrice, 'Price must be a positive number');
}

// Menu filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    // Availability filter
    const availabilityButtons = document.querySelectorAll('.filter-buttons .filter-btn');
    const categoryButtons = document.querySelectorAll('.category-filter-buttons .filter-btn');
    const menuCards = document.querySelectorAll('.menu-card');

    let currentAvailabilityFilter = 'all';
    let currentCategoryFilter = 'all';

    // Availability filter buttons
    availabilityButtons.forEach(button => {
        button.addEventListener('click', function() {
            availabilityButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentAvailabilityFilter = this.dataset.filter;
            applyFilters();
        });
    });

    // Category filter buttons
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentCategoryFilter = this.dataset.category;
            applyFilters();
        });
    });

    function applyFilters() {
        menuCards.forEach(card => {
            const availability = card.dataset.availability;
            const category = card.dataset.category;

            const availabilityMatch = currentAvailabilityFilter === 'all' || availability === currentAvailabilityFilter;
            const categoryMatch = currentCategoryFilter === 'all' || category === currentCategoryFilter;

            if (availabilityMatch && categoryMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
});

window.onclick = function(event) {
    const modal = document.getElementById('menuModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>
<?php require_once '../includes/footer.php'; ?>
