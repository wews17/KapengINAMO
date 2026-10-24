<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check if user is admin
            if ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: /KapengINAMO/dashboard.php');
                exit();
            } else {
                $error = 'Access denied. Admin access only.';
            }
        } else {
            $error = 'Invalid username or password';
        }
    } catch(PDOException $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - KapengINAMO</title>
    <link rel="stylesheet" href="/KapengINAMO/assets/css/style.css">
    <script src="/KapengINAMO/assets/js/validation.js"></script>
</head>
<body style="background: linear-gradient(135deg, #4a2c2a 0%, #6b4423 100%);">
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
            <h2>🔐 Admin Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-error shake"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
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
                <button type="submit" class="btn btn-primary btn-animate">Admin Login</button>
            </form>
            <p class="auth-link" style="margin-top: 1.5rem;">
                <a href="/KapengINAMO/auth/login.php" style="color: #d4a574;">← Back to Regular Login</a>
            </p>
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
            background: linear-gradient(135deg, #3d2514 0%, #4a2c2a 100%);
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
            background: linear-gradient(135deg, #2a1810 0%, #3d2514 100%);
            border-radius: 50%;
        }

        .cup-handle {
            position: absolute;
            right: -20px;
            top: 20px;
            width: 30px;
            height: 50px;
            border: 8px solid #4a2c2a;
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
            background: linear-gradient(135deg, #2a1810 0%, #3d2514 100%);
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
            background: #1a0f0a;
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
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
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
        const passwordInput = document.getElementById('password');

        addValidation(usernameInput, function(value) {
            return value.trim().length > 0;
        }, 'Username or email is required');

        addValidation(passwordInput, function(value) {
            return value.length >= 1;
        }, 'Password is required');
    });
    </script>
</body>
</html>
