<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

$error = '';
$success = '';

// Generate math problem
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$answer = $num1 + $num2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $math_answer = $_POST['math_answer'];
    $expected_answer = $_POST['expected_answer'];

    // Verify math answer
    if ($math_answer != $expected_answer) {
        $error = 'Incorrect security answer. Please solve the math problem.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif ($phone && !preg_match('/^09\d{9}$/', $phone)) {
        $error = 'Phone number must be 11 digits starting with 09 (e.g., 09123456789)';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = 'Username or email already exists';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address]);

                // Redirect to login page after successful registration
                header('Location: /KapengINAMO/auth/login.php');
                exit();
            }
        } catch(PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<?php require_once '../includes/header.php'; ?>
<div class="auth-container">
    <!-- Coffee Background Animation -->
    <div class="coffee-background">
        <div class="coffee-cup">
            <div class="cup-body"></div>
            <div class="cup-handle"></div>
            <div class="steam steam-1"></div>
            <div class="steam steam-2"></div>
            <div class="steam steam-3"></div>
        </div>
        <div class="coffee-beans">
            <div class="bean bean-1"></div>
            <div class="bean bean-2"></div>
            <div class="bean bean-3"></div>
            <div class="bean bean-4"></div>
            <div class="bean bean-5"></div>
        </div>
    </div>

    <div class="auth-box fade-in">
        <h2>☕ Sign Up</h2>
        <?php if ($error): ?>
            <div class="alert alert-error shake"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success fade-in"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone (Philippine format: 09123456789)</label>
                <input type="text" id="phone" name="phone" pattern="^09\d{9}$" placeholder="09123456789">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </div>
            <div class="form-group math-captcha">
                <label for="math_answer">Security Question: <?php echo $num1; ?> + <?php echo $num2; ?> = ?</label>
                <input type="number" id="math_answer" name="math_answer" required placeholder="Enter answer">
                <input type="hidden" name="expected_answer" value="<?php echo $answer; ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-animate">Sign Up</button>
        </form>
        <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
<style>
    /* Coffee Background Animation */
    .coffee-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        overflow: hidden;
    }

    .coffee-cup {
        position: absolute;
        right: 10%;
        top: 20%;
        width: 120px;
        height: 100px;
        animation: floatCup 4s ease-in-out infinite;
    }

    @keyframes floatCup {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(5deg);
        }
    }

    .cup-body {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 80px;
        height: 100px;
        background: linear-gradient(135deg, #4a2c2a 0%, #6b4423 100%);
        border-radius: 0 0 40px 40px;
        box-shadow: inset 0 -20px 30px rgba(0,0,0,0.2);
    }

    .cup-body::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        height: 15px;
        background: linear-gradient(135deg, #3d2514 0%, #4a2c2a 100%);
        border-radius: 50%;
    }

    .cup-handle {
        position: absolute;
        right: -20px;
        top: 20px;
        width: 30px;
        height: 50px;
        border: 8px solid #6b4423;
        border-left: none;
        border-radius: 0 25px 25px 0;
    }

    .steam {
        position: absolute;
        top: -30px;
        width: 8px;
        height: 40px;
        background: linear-gradient(to top, rgba(255,255,255,0.6), transparent);
        border-radius: 4px;
        animation: steam 2s ease-in-out infinite;
    }

    .steam-1 {
        left: 20px;
        animation-delay: 0s;
    }

    .steam-2 {
        left: 40px;
        animation-delay: 0.5s;
    }

    .steam-3 {
        left: 60px;
        animation-delay: 1s;
    }

    @keyframes steam {
        0% {
            opacity: 0;
            transform: translateY(0) scaleX(1);
        }
        50% {
            opacity: 0.8;
            transform: translateY(-20px) scaleX(1.5);
        }
        100% {
            opacity: 0;
            transform: translateY(-40px) scaleX(2);
        }
    }

    .coffee-beans {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .bean {
        position: absolute;
        width: 20px;
        height: 30px;
        background: linear-gradient(135deg, #3d2514 0%, #4a2c2a 100%);
        border-radius: 50%;
        opacity: 0.3;
        animation: floatBean 6s ease-in-out infinite;
    }

    .bean::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 2px;
        height: 20px;
        background: #2a1810;
        border-radius: 1px;
    }

    .bean-1 {
        left: 10%;
        top: 30%;
        animation-delay: 0s;
    }

    .bean-2 {
        left: 20%;
        top: 60%;
        animation-delay: 1s;
    }

    .bean-3 {
        left: 80%;
        top: 40%;
        animation-delay: 2s;
    }

    .bean-4 {
        left: 70%;
        top: 70%;
        animation-delay: 3s;
    }

    .bean-5 {
        left: 15%;
        top: 80%;
        animation-delay: 4s;
    }

    @keyframes floatBean {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-30px) rotate(180deg);
        }
    }

    /* Fade In Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-out;
    }

    /* Shake Animation for Errors */
    @keyframes shake {
        0%, 100% {
            transform: translateX(0);
        }
        10%, 30%, 50%, 70%, 90% {
            transform: translateX(-10px);
        }
        20%, 40%, 60%, 80% {
            transform: translateX(10px);
        }
    }

    .shake {
        animation: shake 0.5s ease-in-out;
    }

    /* Button Hover Animation */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .btn-animate:hover {
        animation: pulse 0.3s ease-in-out;
    }

    /* Math Captcha Styling */
    .math-captcha {
        background: linear-gradient(135deg, #6b4423 0%, #8b5a2b 100%);
        padding: 1rem;
        border-radius: 8px;
    }

    .math-captcha label {
        color: white;
        font-weight: 600;
    }

    .math-captcha input {
        background-color: white;
        border: none;
    }

    /* Input Focus Animation */
    .form-group input:focus,
    .form-group textarea:focus {
        animation: inputFocus 0.3s ease-out;
    }

    @keyframes inputFocus {
        0% {
            box-shadow: 0 0 0 0 rgba(107, 68, 35, 0.4);
        }
        100% {
            box-shadow: 0 0 0 3px rgba(107, 68, 35, 0.2);
        }
    }

    /* Password Toggle */
    .password-wrapper {
        position: relative;
    }

    .password-wrapper input {
        padding-right: 50px;
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        font-size: 1.2rem;
        transition: transform 0.2s ease;
    }

    .toggle-password:hover {
        transform: translateY(-50%) scale(1.2);
    }

    .eye-icon {
        display: block;
    }

    /* Auth Box Hover Effect */
    .auth-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(74, 44, 42, 0.3);
        transition: all 0.3s ease;
    }
</style>
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const eyeIcon = button.querySelector('.eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.textContent = '🙈';
    } else {
        input.type = 'password';
        eyeIcon.textContent = '👁️';
    }
}

// Add form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    preventDoubleSubmit(form);

    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const fullNameInput = document.getElementById('full_name');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const mathAnswerInput = document.getElementById('math_answer');

    addValidation(usernameInput, validateUsername, 'Username must be 3-20 characters (letters, numbers, underscore)');
    addValidation(emailInput, validateEmail, 'Please enter a valid email address');
    addValidation(fullNameInput, validateFullName, 'Full name must be 2-50 characters (letters and spaces only)');
    addValidation(phoneInput, validatePhone, 'Phone must be 11 digits starting with 09 (e.g., 09123456789)');
    addValidation(passwordInput, validatePassword, 'Password must be at least 8 characters');
    addValidation(mathAnswerInput, function(value) {
        return !isNaN(value) && value.trim() !== '';
    }, 'Please answer the security question');

    // Confirm password validation
    confirmPasswordInput.addEventListener('blur', function() {
        if (this.value !== passwordInput.value) {
            showError(this, 'Passwords do not match');
        } else {
            clearError(this);
        }
    });

    confirmPasswordInput.addEventListener('input', function() {
        if (this.style.borderColor === 'rgb(139, 69, 19)') {
            if (this.value === passwordInput.value) {
                clearError(this);
            }
        }
    });

    // Additional form validation on submit
    form.addEventListener('submit', function(e) {
        if (passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            showError(confirmPasswordInput, 'Passwords do not match');
            return false;
        }
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
