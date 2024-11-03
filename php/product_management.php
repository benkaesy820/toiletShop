<?php
include 'db.php';

// Function to get category name
function getCategoryName($category_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['name'] : 'Unknown';
}

// Add a new product
function addProduct($name, $description, $price, $category_id, $image_url) {
    global $pdo;
    try {
        $sql = "INSERT INTO products (name, description, price, category_id, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$name, $description, $price, $category_id, $image_url]);
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

// Get all products
function getProducts() {
    global $pdo;
    try {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.name";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

// Get a specific product by ID
function getProductById($id) {
    global $pdo;
    try {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting product by ID: " . $e->getMessage());
        return false;
    }
}

// Update a product
function updateProduct($id, $name, $description, $price, $category_id, $image_url = null) {
    global $pdo;
    try {
        // If new image is provided, update it; otherwise, keep the existing one
        if ($image_url) {
            $sql = "UPDATE products 
                    SET name = ?, description = ?, price = ?, category_id = ?, image_url = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$name, $description, $price, $category_id, $image_url, $id]);
        } else {
            $sql = "UPDATE products 
                    SET name = ?, description = ?, price = ?, category_id = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$name, $description, $price, $category_id, $id]);
        }
    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}

// Delete a product
function deleteProduct($id) {
    global $pdo;
    try {
        // First get the image URL to delete the file
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the image file if it exists
        if ($product && $product['image_url']) {
            $image_path = '../' . $product['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete the product from database
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}

// Get all categories
function getCategories() {
    global $pdo;
    try {
        $sql = "SELECT * FROM categories ORDER BY name";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting categories: " . $e->getMessage());
        return [];
    }
}

// Add a new category
function addCategory($name) {
    global $pdo;
    try {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$name]);
    } catch (PDOException $e) {
        error_log("Error adding category: " . $e->getMessage());
        return false;
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_product':
            if (isset($_GET['id'])) {
                $product = getProductById($_GET['id']);
                echo json_encode($product);
            }
            break;
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'update_product':
            $image_url = null;
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
                $upload_dir = '../uploads/';
                
                // Create uploads directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
                $file_name = uniqid() . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                // Only allow certain file types
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                        $image_url = 'uploads/' . $file_name;
                    }
                }
            }
            
            $success = updateProduct(
                $_POST['id'],
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['category_id'],
                $image_url
            );
            
            echo json_encode(['success' => $success]);
            break;
            
        case 'delete_product':
            if (isset($_POST['id'])) {
                $success = deleteProduct($_POST['id']);
                echo json_encode(['success' => $success]);
            }
            break;
            
        case 'add_category':
            if (isset($_POST['name'])) {
                $success = addCategory($_POST['name']);
                echo json_encode(['success' => $success]);
            }
            break;
    }
    exit;
}

// Helper function to validate image
function isValidImage($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    return true;
}

// Helper function to generate safe filename
function generateSafeFileName($original_name) {
    $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
    return time() . '_' . $filename;
}
?>