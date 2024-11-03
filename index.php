<?php
session_start();
include 'php/db.php';
include 'php/getProducts.php';

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $user_logged_in ? $_SESSION['username'] : null;

// Get all products
$products = getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toiletries E-Commerce</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom CSS */
        .navbar { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .welcome-msg { font-size: 1.1rem; }
        .avatar { border-radius: 50%; width: 40px; height: 40px; margin-left: 10px; }
        .card { box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: 0.3s; }
        .card:hover { transform: scale(1.05); }
        .card-img-top { height: 200px; object-fit: cover; }
        .card-body { text-align: center; }
    </style>
</head>
<body>
   <!-- Navbar -->

   <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <a class="navbar-brand font-weight-bold" href="#">Toiletries Shop</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
            <?php if ($user_logged_in): ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <?php endif; ?>
            <?php if ($user_logged_in && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
            <?php endif; ?>
        </ul>

        <!-- Search Form -->
        <form class="form-inline my-2 my-lg-0" onsubmit="return false;">
            <input class="form-control mr-sm-2" type="search" id="searchInput" placeholder="Search products" aria-label="Search">
            <div id="searchResults" class="dropdown-menu" style="display: none; position: absolute; z-index: 1000;"></div>
        </form>

        <!-- User Greeting with Random Avatar -->
        <?php if ($user_logged_in): ?>
            <span class="navbar-text d-flex align-items-center ml-3">
                <strong class="mr-2">Welcome, <?= htmlspecialchars($user_name); ?></strong>
                <img src="https://i.pravatar.cc/40?u=<?= urlencode($user_name); ?>" class="rounded-circle" alt="User Avatar" width="40" height="40">
            </span>
        <?php endif; ?>
    </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <h1 class="text-center mb-4">Explore Our Toiletries</h1>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']); ?></p>
                            <p class="card-text"><strong>Price: $<?= number_format($product['price'], 2); ?></strong></p>
                            <a href="cart.php?action=add&id=<?= urlencode($product['id']); ?>" class="btn btn-primary">Add to Cart</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script> <!-- External JavaScript file for search functionality -->
</body>
</html>
