<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KapengINAMO - Coffee Shop Management</title>
    <link rel="stylesheet" href="/KapengINAMO/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/KapengINAMO/assets/js/validation.js"></script>
</head>
<body>
    <?php if (isLoggedIn()): ?>
        <!-- Top Bar -->
        <nav class="top-bar">
            <div class="top-bar-container">
                <div class="top-bar-brand">
                    <img src="/KapengINAMO/img/Qm20b.jpg" alt="KapengINAMO Logo" class="logo">
                </div>
                <div class="profile-dropdown">
                    <button class="profile-btn" onclick="toggleProfileDropdown()">
                        <span class="profile-avatar-small">
                            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                        </span>
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="/KapengINAMO/settings/index.php" class="dropdown-item">
                            <span>👤</span> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="/KapengINAMO/auth/logout.php" class="dropdown-item logout-item">
                            <span>🚪</span> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar and Main Content -->
        <div class="dashboard-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <nav class="sidebar-nav">
                    <a href="/KapengINAMO/dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="sidebar-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="/KapengINAMO/admin/users.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <span class="sidebar-icon">👥</span>
                            <span>Users</span>
                        </a>
                        <a href="/KapengINAMO/admin/feedback.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
                            <span class="sidebar-icon">💬</span>
                            <span>Feedback</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isAdmin() || isStaff()): ?>
                        <a href="/KapengINAMO/admin/orders.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                            <span class="sidebar-icon">📋</span>
                            <span>Manage Orders</span>
                        </a>
                    <?php endif; ?>
                    <a href="/KapengINAMO/menu/index.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'menu') !== false ? 'active' : ''; ?>">
                        <span class="sidebar-icon">☕</span>
                        <span>Menu</span>
                    </a>
                    <?php if (isCustomer()): ?>
                        <a href="/KapengINAMO/orders/my_orders.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_orders.php' ? 'active' : ''; ?>">
                            <span class="sidebar-icon">📋</span>
                            <span>My Orders</span>
                        </a>
                        <a href="/KapengINAMO/orders/feedback.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
                            <span class="sidebar-icon">💬</span>
                            <span>Feedback</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <a href="/KapengINAMO/sales/index.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'sales') !== false ? 'active' : ''; ?>">
                            <span class="sidebar-icon">💰</span>
                            <span>Sales</span>
                        </a>
                    <?php endif; ?>
                    <?php if (isAdmin() || isStaff()): ?>
                        <a href="/KapengINAMO/admin/feedback.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
                            <span class="sidebar-icon">💬</span>
                            <span>Feedback</span>
                        </a>
                    <?php endif; ?>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="main-content-with-sidebar">
    <?php else: ?>
        <!-- Regular Navigation for non-logged-in users -->
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <img src="/KapengINAMO/img/Qm20b.jpg" alt="KapengINAMO Logo" class="logo">
                </div>
                <ul class="nav-menu">
                    <li><a href="/KapengINAMO/auth/login.php">Login</a></li>
                    <li><a href="/KapengINAMO/auth/signup.php">Sign Up</a></li>
                </ul>
            </div>
        </nav>
        
        <main class="main-content">
    <?php endif; ?>
