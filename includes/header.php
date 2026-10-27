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
                <div class="top-bar-right">
                    <div class="notification-container">
                        <button class="notification-btn" onclick="toggleNotificationDropdown()">
                            <span class="notification-icon">🔔</span>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4>Notifications</h4>
                                <button class="mark-all-read-btn" onclick="markAllAsRead()">Mark all as read</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <p class="no-notifications">No notifications</p>
                            </div>
                        </div>
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
                    <?php if (isAdmin()): ?>
                        <a href="/KapengINAMO/menu/index.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'menu') !== false ? 'active' : ''; ?>">
                            <span class="sidebar-icon">☕</span>
                            <span>Menu</span>
                        </a>
                    <?php endif; ?>
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

<script>
// Notification functionality
function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');

    // Load notifications when opening
    if (dropdown.classList.contains('show')) {
        loadNotifications();
    }
}

function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdowns when clicking outside
window.onclick = function(event) {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    const notificationBtn = document.querySelector('.notification-btn');
    const profileBtn = document.querySelector('.profile-btn');
    const topBarRight = document.querySelector('.top-bar-right');

    if (!event.target.closest('.notification-container') && !event.target.closest(notificationBtn)) {
        notificationDropdown.classList.remove('show');
    }

    if (!event.target.closest('.profile-dropdown') && !event.target.closest(profileBtn)) {
        profileDropdown.classList.remove('show');
    }
}

function loadNotifications() {
    fetch('/KapengINAMO/api/notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading notifications:', data.error);
                return;
            }

            const notificationList = document.getElementById('notificationList');
            const notificationBadge = document.getElementById('notificationBadge');

            // Update badge
            if (data.unread_count > 0) {
                notificationBadge.textContent = data.unread_count;
                notificationBadge.style.display = 'flex';
            } else {
                notificationBadge.style.display = 'none';
            }

            // Update notification list
            if (data.notifications.length === 0) {
                notificationList.innerHTML = '<p class="no-notifications">No notifications</p>';
            } else {
                let html = '';
                data.notifications.forEach(notification => {
                    html += `
                        <div class="notification-item ${notification.is_read ? '' : 'unread'}" onclick="markAsRead(${notification.id})">
                            <div class="notification-message">${escapeHtml(notification.message)}</div>
                            <div class="notification-time">${formatNotificationTime(notification.created_at)}</div>
                        </div>
                    `;
                });
                notificationList.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function markAsRead(notificationId) {
    fetch('/KapengINAMO/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('/KapengINAMO/api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatNotificationTime(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = now - date;

    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return minutes + ' min ago';
    if (hours < 24) return hours + 'h ago';
    if (days < 7) return days + 'd ago';

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
}

// Auto-refresh notifications every 30 seconds
setInterval(() => {
    const dropdown = document.getElementById('notificationDropdown');
    if (!dropdown.classList.contains('show')) {
        fetch('/KapengINAMO/api/notifications.php')
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    const notificationBadge = document.getElementById('notificationBadge');
                    if (data.unread_count > 0) {
                        notificationBadge.textContent = data.unread_count;
                        notificationBadge.style.display = 'flex';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error refreshing notifications:', error);
            });
    }
}, 30000);
</script>
