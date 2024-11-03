<?php
session_start();
include 'php/db.php'; // Include database connection
include 'php/user_auth.php'; // Include user authentication functions

// Initialize variables
$error = '';
$debug_message = '';
$isDebugMode = true; // Set to false in production

// Generate a CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed");
    }
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Debug information
    if ($isDebugMode) {
        $debug_message .= "Attempting login with username: " . htmlspecialchars($username) . "<br>";
    }
    
    try {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if ($isDebugMode) {
                $debug_message .= "User found in database<br>";
                $debug_message .= "Stored hashed password: " . $user['password'] . "<br>";
            }
            
            if (password_verify($password, $user['password'])) {
                if ($isDebugMode) {
                    $debug_message .= "Password verified successfully<br>";
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to index page
                header("Location: index.php");
                exit();
            } else {
                if ($isDebugMode) {
                    $debug_message .= "Password verification failed<br>";
                }
                $error = "Invalid username or password!";
            }
        } else {
            if ($isDebugMode) {
                $debug_message .= "User not found in database<br>";
            }
            $error = "Invalid username or password!";
        }
    } catch (PDOException $e) {
        $error = "Database error occurred";
        if ($isDebugMode) {
            $debug_message .= "Database error: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($isDebugMode && !empty($debug_message)): ?>
            <div class="alert alert-info">
                <h4>Debug Information:</h4>
                <?= $debug_message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       name="username" 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                       required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Don't have an account? <a href=".\php\register.php">Register here</a></p>
    </div>

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
