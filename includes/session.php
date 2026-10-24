<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is staff
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

// Check if user is customer
function isCustomer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /KapengINAMO/auth/login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /KapengINAMO/dashboard.php');
        exit();
    }
}

// Redirect if not admin or staff
function requireStaffOrAdmin() {
    requireLogin();
    if (!isAdmin() && !isStaff()) {
        header('Location: /KapengINAMO/dashboard.php');
        exit();
    }
}

// Redirect if not customer, staff, or admin
function requireCustomerOrStaffOrAdmin() {
    requireLogin();
    if (!isAdmin() && !isStaff() && !isCustomer()) {
        header('Location: /KapengINAMO/dashboard.php');
        exit();
    }
}
?>
