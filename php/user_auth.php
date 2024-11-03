<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/toiletries-ecommerce/php/db.php';

/**
 * Register a new user
 * @param string $username
 * @param string $email
 * @param string $password
 * @return bool
 */
function registerUser($username, $email, $password) {
    global $pdo;
    
    try {
        // Validate input
        if (!validateUsername($username) || !validateEmail($email) || !validatePassword($password)) {
            return false;
        }
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return false; // User or email already exists
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$username, $email, $hashed_password]);
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Authenticate and log in a user
 * @param string $username
 * @param string $password
 * @return bool
 */
function loginUser($username, $password) {
    global $pdo;
    
    try {
        // Prepare statement
        $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Update last login timestamp
                updateLastLogin($user['id']);
                
                return true;
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log out the current user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Update user's last login timestamp
 * @param int $user_id
 * @return bool
 */
function updateLastLogin($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("Update last login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate username format
 * @param string $username
 * @return bool
 */
function validateUsername($username) {
    // Username should be 3-20 characters and contain only letters, numbers, and underscores
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password
 * @return bool
 */
function validatePassword($password) {
    // Password should be at least 8 characters long
    // and contain at least one uppercase letter, one lowercase letter, and one number
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

/**
 * Get user details by ID
 * @param int $user_id
 * @return array|null
 */
function getUserById($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user password
 * @param int $user_id
 * @param string $new_password
 * @return bool
 */
function updatePassword($user_id, $new_password) {
    global $pdo;
    
    try {
        if (!validatePassword($new_password)) {
            return false;
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        return $stmt->execute([$hashed_password, $user_id]);
    } catch (PDOException $e) {
        error_log("Update password error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user email
 * @param int $user_id
 * @param string $new_email
 * @return bool
 */
function updateEmail($user_id, $new_email) {
    global $pdo;
    
    try {
        if (!validateEmail($new_email)) {
            return false;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$new_email, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            return false; // Email already exists
        }
        
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        
        if ($stmt->execute([$new_email, $user_id])) {
            $_SESSION['email'] = $new_email;
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Update email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete user account
 * @param int $user_id
 * @return bool
 */
function deleteAccount($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        
        if ($stmt->execute([$user_id])) {
            logoutUser();
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Delete account error: " . $e->getMessage());
        return false;
    }
}