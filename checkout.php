<?php
session_start();
include 'php/getProducts.php'; // Include product retrieval logic
include 'php/payment.php'; // Include payment processing logic

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentResponse = processPayment($_SESSION['cart']); // Call payment processing function
    // Handle payment response here (store order details, etc.)
    // For demonstration, assuming successful payment
    if ($paymentResponse['status'] === 'success') {
        // Display payment confirmation
        echo "<script>alert('Payment successful! Receipt: {$paymentResponse['receipt']}');</script>";
        // Clear cart
        unset($_SESSION['cart']);
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Checkout</h1>
        <form method="POST" action="">
            <h4>Please confirm your order:</h4>
            <ul class="list-group mb-4">
                <?php foreach ($_SESSION['cart'] as $id => $quantity): 
                    $product = getProductById($id); ?>
                    <li class="list-group-item">
                        <?= $product['name'] ?> - Quantity: <?= $quantity ?> - Price: $<?= $product['price'] ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit" class="btn btn-primary">Pay with Paystack</button>
        </form>
    </div>
</body>
</html>

