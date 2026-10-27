<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireAdmin();

$message = '';
$messageType = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        // Validate phone number (required)
        if (!$phone || !preg_match('/^09\d{9}$/', $phone)) {
            $message = 'Phone number is required and must be 11 digits starting with 09 (e.g., 09123456789)';
            $messageType = 'error';
        } elseif (!$address) {
            $message = 'Address is required';
            $messageType = 'error';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $role, $phone, $address]);
                $message = 'User added successfully!';
                $messageType = 'success';
            } catch(PDOException $e) {
                $message = 'Error adding user: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'edit') {
        $user_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        // Validate phone number (required)
        if (!$phone || !preg_match('/^09\d{9}$/', $phone)) {
            $message = 'Phone number is required and must be 11 digits starting with 09 (e.g., 09123456789)';
            $messageType = 'error';
        } elseif (!$address) {
            $message = 'Address is required';
            $messageType = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$username, $email, $full_name, $role, $phone, $address, $user_id]);
                $message = 'User updated successfully!';
                $messageType = 'success';
            } catch(PDOException $e) {
                $message = 'Error updating user: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = 'User deleted successfully!';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'Error deleting user: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $users = [];
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>User Management</h2>
    <button class="btn btn-primary" onclick="openModal('add')">Add New User</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-edit" onclick="openModal('edit', <?php echo $user['id']; ?>)">Edit</button>
                        <button class="btn btn-sm btn-delete" onclick="confirmDelete(<?php echo $user['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New User</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="userForm" method="POST" action="">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="userId">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group" id="passwordGroup">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="customer">Customer</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Phone (Philippine format: 09123456789)</label>
                <input type="text" id="phone" name="phone" pattern="^09\d{9}$" placeholder="09123456789" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<script>
const users = <?php echo json_encode($users); ?>;

function openModal(mode, userId = null) {
    const modal = document.getElementById('userModal');
    const title = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const userIdField = document.getElementById('userId');
    const passwordGroup = document.getElementById('passwordGroup');

    modal.style.display = 'block';

    if (mode === 'edit' && userId) {
        const user = users.find(u => u.id === userId);
        if (user) {
            title.textContent = 'Edit User';
            formAction.value = 'edit';
            userIdField.value = userId;
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('full_name').value = user.full_name;
            document.getElementById('role').value = user.role;
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('address').value = user.address || '';
            passwordGroup.style.display = 'none';
        }
    } else {
        title.textContent = 'Add New User';
        formAction.value = 'add';
        userIdField.value = '';
        document.getElementById('userForm').reset();
        passwordGroup.style.display = 'block';
    }

    // Add validation when modal opens
    setTimeout(addUserFormValidation, 100);
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

function confirmDelete(userId) {
    if (confirmAction('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function addUserFormValidation() {
    const form = document.getElementById('userForm');
    preventDoubleSubmit(form);

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const fullNameInput = document.getElementById('full_name');
    const phoneInput = document.getElementById('phone');
    const addressInput = document.getElementById('address');
    const passwordInput = document.getElementById('password');

    addValidation(usernameInput, validateUsername, 'Username must be 3-20 characters (letters, numbers, underscore)');
    addValidation(emailInput, validateEmail, 'Please enter a valid email address');
    addValidation(fullNameInput, validateFullName, 'Full name must be 2-50 characters (letters and spaces only)');
    addValidation(phoneInput, validatePhone, 'Phone is required and must be 11 digits starting with 09 (e.g., 09123456789)');
    addValidation(addressInput, function(value) {
        return value.trim().length > 0;
    }, 'Address is required');

    if (passwordInput && passwordInput.offsetParent !== null) {
        addValidation(passwordInput, validatePassword, 'Password must be at least 8 characters');
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>
<?php require_once '../includes/footer.php'; ?>
