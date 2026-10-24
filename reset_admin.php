<?php
// Admin Reset Script
// Run this file to reset the admin account
// After running, delete this file for security

require_once 'includes/db.php';

try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' OR username = 'Admin'");
    $stmt->execute();
    $existing = $stmt->fetch();

    $new_password = password_hash('Admin123', PASSWORD_DEFAULT);

    if ($existing) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET username = 'Admin', password = ? WHERE id = ?");
        $stmt->execute([$new_password, $existing['id']]);
        echo "✅ Admin account updated successfully!<br>";
        echo "Username: Admin<br>";
        echo "Password: Admin123<br>";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['Admin', 'admin@kapenginamo.com', $new_password, 'System Administrator']);
        echo "✅ Admin account created successfully!<br>";
        echo "Username: Admin<br>";
        echo "Password: Admin123<br>";
    }

    echo "<br><strong>⚠️ IMPORTANT: Delete this file (reset_admin.php) after use for security!</strong>";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
