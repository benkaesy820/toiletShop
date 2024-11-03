<?php
session_start();
include 'php/getProducts.php'; // Include product retrieval logic

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $productId = $_GET['id'];
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1; // Increment product quantity
}

// Remove product from cart
if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $productId = $_GET['id'];
    unset($_SESSION['cart'][$productId]); // Remove product from cart
}

// Get cart items with product details
$cartItems = [];
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $quantity) {
        $product = getProductById($id);
        $cartItems[] = ['product' => $product, 'quantity' => $quantity];
        $totalPrice += $product['price'] * $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Your Shopping Cart</h1>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?= $item['product']['name'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= $item['product']['price'] ?></td>
                            <td>$<?= $item['product']['price'] * $item['quantity'] ?></td>
                            <td><a href="cart.php?action=remove&id=<?= $item['product']['id'] ?>" class="btn btn-danger">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4>Total Price: $<?= $totalPrice ?></h4>
            <a href="checkout.php" class="btn btn-success">Checkout</a>
        <?php endif; ?>
    </div>
</body>
</html>
