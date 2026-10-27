<?php
// Generate Password Hashes for Sample Users
// Run this file to generate proper bcrypt hashes for sample users
// This will update sample_data.sql with the correct hashes

require_once 'includes/db.php';

$passwords = [
    'Staff1' => 'Staff123',
    'customer1' => 'Customer123',
    'customer2' => 'Customer123',
    'customer3' => 'Customer123'
];

echo "Generating password hashes for sample users...\n\n";

foreach ($passwords as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "Hash: $hash\n\n";

    // Update the user in the database if it exists
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hash, $username]);
        echo "✅ Updated password for $username\n\n";
    } catch(PDOException $e) {
        echo "❌ Error updating $username: " . $e->getMessage() . "\n\n";
    }
}

echo "Password hashes generated and updated successfully!\n";
echo "⚠️ Delete this file (generate_sample_passwords.php) after use for security!\n";
?>
