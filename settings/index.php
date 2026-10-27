<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

requireLogin();

$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate phone number (required)
    if (!$phone || !preg_match('/^09\d{9}$/', $phone)) {
        $message = 'Phone number is required and must be 11 digits starting with 09 (e.g., 09123456789)';
        $messageType = 'error';
    } elseif (!$address) {
        $message = 'Address is required';
        $messageType = 'error';
    } else {
        try {
            // Verify current password if changing password
            if (!empty($new_password)) {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();

                if (!password_verify($current_password, $user['password'])) {
                    $message = 'Current password is incorrect';
                    $messageType = 'error';
                } elseif ($new_password !== $confirm_password) {
                    $message = 'New passwords do not match';
                    $messageType = 'error';
                } else {
                    // Update with new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                    $stmt->execute([$full_name, $email, $phone, $address, $hashed_password, $_SESSION['user_id']]);
                    $message = 'Profile updated successfully!';
                    $messageType = 'success';

                    // Update session
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['email'] = $email;
                }
            } else {
                // Update without password change
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $address, $_SESSION['user_id']]);
                $message = 'Profile updated successfully!';
                $messageType = 'success';

                // Update session
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
            }
        } catch(PDOException $e) {
            $message = 'Error updating profile: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $user = [];
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="page-header">
    <h2>Settings</h2>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="settings-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <span><?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 2)); ?></span>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h3>
                <p class="profile-role"><?php echo ucfirst($user['role'] ?? ''); ?></p>
                <p class="profile-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
            </div>
        </div>
    </div>

    <div class="settings-form-container">
        <h3>Edit Profile</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone (Philippine format: 09123456789)</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" pattern="^09\d{9}$" placeholder="09123456789" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-section-divider">
                <h4>Change Password (Optional)</h4>
            </div>

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    preventDoubleSubmit(form);

    const fullNameInput = document.getElementById('full_name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const addressInput = document.getElementById('address');
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    addValidation(fullNameInput, validateFullName, 'Full name must be 2-50 characters (letters and spaces only)');
    addValidation(emailInput, validateEmail, 'Please enter a valid email address');
    addValidation(phoneInput, validatePhone, 'Phone is required and must be 11 digits starting with 09 (e.g., 09123456789)');
    addValidation(addressInput, function(value) {
        return value.trim().length > 0;
    }, 'Address is required');

    // Password change validation
    newPasswordInput.addEventListener('input', function() {
        if (this.value) {
            if (!validatePassword(this.value)) {
                showError(this, 'Password must be at least 8 characters');
            } else {
                clearError(this);
            }
        }
    });

    confirmPasswordInput.addEventListener('blur', function() {
        if (newPasswordInput.value && this.value !== newPasswordInput.value) {
            showError(this, 'Passwords do not match');
        } else {
            clearError(this);
        }
    });

    confirmPasswordInput.addEventListener('input', function() {
        if (this.style.borderColor === 'rgb(139, 69, 19)') {
            if (this.value === newPasswordInput.value) {
                clearError(this);
            }
        }
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (newPasswordInput.value && !currentPasswordInput.value) {
            e.preventDefault();
            showError(currentPasswordInput, 'Current password is required to change password');
            return false;
        }

        if (newPasswordInput.value && newPasswordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            showError(confirmPasswordInput, 'Passwords do not match');
            return false;
        }
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
