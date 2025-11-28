<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php?error=Access denied.");
    exit;
}

require_once "../classes/Database.php";
$pdo = db(); 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$sql_orders = "
    SELECT 
        o.id AS order_id, 
        o.total_price, 
        o.order_date,
        o.status, 
        u.name AS user_name,
        u.email AS user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
";
$stmt_orders = $pdo->query($sql_orders);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

$status_options = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
$message = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View All Orders</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .order-details {
            border-left: 3px solid #0d6efd;
            padding-left: 10px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🧾 All Customer Orders</h2>

    <p>
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </p>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (count($orders) == 0): ?>
        <div class="alert alert-info">No orders have been placed yet.</div>
    <?php else: ?>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Info</th>
                <th>Order Details</th>
                <th>Total</th>
                <th>Status / Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($order['user_name']) ?></strong><br>
                        <small><?= htmlspecialchars($order['user_email']) ?></small