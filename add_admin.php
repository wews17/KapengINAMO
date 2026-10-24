<?php
// Add New Admin Account Script
// Run this file to create a new admin account
// After running, delete this file for security

require_once 'includes/db.php';

// Configuration for new admin
$new_username = 'Admin2';
$new_email = 'admin2@kapenginamo.com';
$new_password = 'Admin123';
$new_full_name = 'Secondary Administrator';

try {
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$new_username, $new_email]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "❌ Error: Username or email already exists!<br>";
        echo "Please modify the script with different credentials.";
    } else {
        // Insert new admin
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute([$new_username, $new_email, $hashed_password, $new_full_name]);

        echo "✅ New admin account created successfully!<br><br>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "Username: " . htmlspecialchars($new_username) . "<br>";
        echo "Email: " . htmlspecialchars($new_email) . "<br>";
        echo "Password: " . htmlspecialchars($new_password) . "<br><br>";
        echo "<strong>⚠️ IMPORTANT: Delete this file (add_admin.php) after use for security!</strong>";
    }

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
