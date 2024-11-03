<?php
session_start();
include 'db.php'; // Include database connection

// Check if admin exists
$check = "SELECT * FROM users WHERE username = 'admin'";
$stmt = $pdo->prepare($check);
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    // Update existing admin
    $sql = "UPDATE users SET password = ? WHERE username = ?";
} else {
    // Create new admin user
    $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
}

$username = 'admin';
$password = 'admin';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare($sql);
    if ($user) {
        $stmt->execute([$hashedPassword, $username]);
        echo "Admin password updated successfully";
    } else {
        $stmt->execute([$username, $hashedPassword, 'admin@example.com', 'admin']);
        echo "Admin user created successfully";
    }
    
    // Verify the admin user was created/updated correctly
    $verify = "SELECT * FROM users WHERE username = 'admin'";
    $stmt = $pdo->prepare($verify);
    $stmt->execute();
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    echo "\nAdmin user details:\n";
    echo "Username: " . htmlspecialchars($adminUser['username']) . "\n";
    echo "Role: " . htmlspecialchars($adminUser['role']) . "\n";
    echo "Password hash: " . $adminUser['password'] . "\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>
