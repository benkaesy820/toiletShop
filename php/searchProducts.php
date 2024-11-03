<?php
// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection
require_once 'db.php';

// Set JSON header
header('Content-Type: application/json');

try {
    if (isset($_GET['query']) && strlen($_GET['query']) > 2) {
        $query = '%' . $_GET['query'] . '%';
        
        // Use PDO instead of mysqli since your db.php uses PDO
        $stmt = $pdo->prepare("SELECT id, name FROM products WHERE name LIKE :query");
        $stmt->execute(['query' => $query]);
        
        // Fetch all matching products
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($products);
    } else {
        echo json_encode(['error' => 'Query not set or too short']);
    }
} catch (PDOException $e) {
    // Return error message as JSON
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
}
?>