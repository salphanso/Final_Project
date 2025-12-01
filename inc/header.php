<?php
// inc/header.php (THE DEFINITIVE FINAL CODE - All Visibility Fixes and Structure)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define the project folder for reliable linking (used for the Logout fix)
$project_folder = '/online_computer_store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Computer Store</title>
    <link rel="stylesheet" href="<?php echo $project_folder; ?>/assets/css/styles.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<header class="bg-dark text-white p-2 sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        
        <h1 class="h6 mb-0">
            <a href="index.php" class="text-black text-decoration-none">Online E-Commerce Store</a>
        </h1>
        
        <nav class="d-flex align-items-center gap-2">
            
            <a href="index.php" class="btn btn-light btn-sm">Products</a>
            
            <a href="cart.php" class="btn btn-light btn-sm">Cart</a>
            
            <?php 
            if (isset($_SESSION['user_id'])): 
                // Logged In Links
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): 
                ?>
                    <a href="admin/dashboard.php" class="btn btn-warning btn-sm">Admin</a>
                <?php 
                else: 
                ?>
                    <a href="user_orders.php" class="btn btn-primary btn-sm">Order History</a>
                <?php endif; ?>
                
                <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $project_folder . '/logout.php'; ?>" class="btn btn-danger btn-sm">
                    Logout (<?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>)
                </a>

            <?php 
            else: // User is NOT logged in
            ?>
                <a href="login.php" class="btn btn-light btn-sm">Login</a> 
                <a href="register.php" class="btn btn-light btn-sm">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
