<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php?msg=Access denied");
    exit;
}

require_once "../classes/Database.php";
$pdo = db();


try {
    $stmt_products = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt_products->fetchColumn();

    $stmt_orders = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt_orders->fetchColumn();

    $stmt_revenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'Cancelled'");
    $total_revenue = $stmt_revenue->fetchColumn() ?? 0;

    $stmt_pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
    $pending_orders = $stmt_pending->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $stats_error = "Could not load dashboard statistics.";
}

include "../inc/header.php";
?>

<div class="container">
    <h2>⚙️ Admin Dashboard</h2>

    <p>Welcome, **<?php echo htmlspecialchars($_SESSION['user_name']); ?>**! Here is an overview of your store:</p>

    <?php if (isset($stats_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($stats_error) ?></div>
    <?php endif; ?>

    <hr>
    
    <div class="row g-4 mb-5">
        
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text fs-3">$<?= number_format($total_revenue, 2) ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text fs-3"><?= $total_orders ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Orders</h5>
                    <p class="card-text fs-3"><?= $pending_orders ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text fs-3"><?= $total_products ?></p>
                </div>
            </div>
        </div>

    </div>

    <h3>Quick Navigation</h3>
    <div style="display:flex; gap:20px; margin-top:20px;">
        <a href="add_product.php" 
           class="btn btn-primary btn-lg">
            ➕ Add New Product
        </a>

        <a href="manage_products.php" 
           class="btn btn-dark btn-lg">
            📦 Manage Products
        </a>

        <a href="view_orders.php" 
           class="btn btn-info btn-lg">
            📑 View Orders (<?= $pending_orders ?> Pending)
        </a>

    </div>

</div>

<?php include "../inc/footer.php"; ?>