<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=Please log in to view your orders.");
    exit;
}

require_once "classes/Database.php";
$pdo = db(); 

include "inc/header.php";

$user_id = $_SESSION['user_id'];

$sql_orders = "
    SELECT id, total_price, order_date, status
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
";
$stmt_orders = $pdo->prepare($sql_orders);
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>🛍️ Your Order History</h2>

    <?php if (count($orders) == 0): ?>
        <div class="alert alert-info" role="alert">
            You have not placed any orders yet. 
            <a href="index.php">Start shopping now!</a>
        </div>
    <?php else: ?>
        <p>Review the status of your past purchases below.</p>

        <?php foreach ($orders as $order): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= $order['id'] ?></strong> | 
                        Placed: <?= date("M d, Y", strtotime($order['order_date'])) ?>
                    </div>
                    <div>
                        Total: <strong>$<?= number_format($order['total_price'], 2) ?></strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        Status: 
                        <span class="badge 
                            <?php 
                                if ($order['status'] == 'Completed') echo 'bg-success';
                                elseif ($order['status'] == 'Cancelled') echo 'bg-danger';
                                else echo 'bg-warning text-dark';
                            ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>

                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price per Item</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $details_sql = "
                                SELECT od.quantity, od.price, p.name 
                                FROM order_details od
                                JOIN products p ON od.product_id = p.id
                                WHERE od.order_id = ?
                            ";
                            $details_stmt = $pdo->prepare($details_sql);
                            $details_stmt->execute([$order['id']]);
                            $details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($details as $item):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="index.php" class="btn btn-secondary">← Continue Shopping</a></p>
</div>

<?php include "inc/footer.php"; ?>