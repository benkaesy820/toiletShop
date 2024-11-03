<?php
// getProducts.php
include 'db.php'; // Ensure we include the db connection

function getProducts() {
    global $pdo; // Use the global PDO variable from db.php

    try {
        $stmt = $pdo->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all products as an associative array
    } catch (PDOException $e) {
        echo "Error retrieving products: " . $e->getMessage();
        return []; // Return an empty array in case of error
    }
}
?>
