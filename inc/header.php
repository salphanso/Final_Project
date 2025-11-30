<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Computer Store</title>
    <link rel="stylesheet" href="/online_computer_store/assets/css/styles.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<header class="bg-dark text-white p-3 mb-4 sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        
        <h1 class="h3 mb-0">
            <a href="index.php" class="text-white text-decoration-none">Computer Store</a>
        </h1>

        <nav class="d-flex align-items-center">
            <a href="index.php" class="nav-link text-white mx-2">Products</a>
            <a href="cart.php" class="btn btn-secondary btn-sm mx-2">Cart</a>
            
            <div class="ms-3 d-flex gap-2">
            <?php 
            if (isset($_SESSION['user_id'])): 
                
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): 
                ?>
                    <a href="admin/dashboard.php" class="btn btn-warning btn-sm">Admin</a>
                <?php 
                else: 
                ?>
                    <a href="user_orders.php" class="btn btn-info btn-sm">Order History</a>
                <?php endif; ?>
                
                <a href="logout.php" class="btn btn-danger btn-sm">
                    Logout (<?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>)
                </a>

            <?php 
            else: 
            ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">Login</a>
                <a href="register.php" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<div class="container mb-4">
    </div>