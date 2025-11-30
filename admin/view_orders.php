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
                        <small><?= htmlspecialchars($order['user_email']) ?></small><br>
                        <small><?= date("Y-m-d H:i", strtotime($order['order_date'])) ?></small>
                    </td>
                    <td>
                        <div class="order-details">
                        <?php
                            $details_sql = "
                                SELECT od.quantity, od.price, p.name 
                                FROM order_details od
                                JOIN products p ON od.product_id = p.id
                                WHERE od.order_id = ?
                            ";
                            $details_stmt = $pdo->prepare($details_sql);
                            $details_stmt->execute([$order['order_id']]);
                            $details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($details as $item): 
                        ?>
                            <small>
                                <?= $item['quantity'] ?>x <?= htmlspecialchars($item['name']) ?> 
                                (@$<?= number_format($item['price'], 2) ?>)
                            </small><br>
                        <?php endforeach; ?>
                        </div>
                    </td>
                    <td>$<?= number_format($order['total_price'], 2) ?></td>
                    <td>
                        <form action="process_order.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            
                            <select name="status" class="form-select mb-2">
                                <?php foreach ($status_options as $status): ?>
                                    <option value="<?= $status ?>" 
                                            <?= ($order['status'] == $status) ? 'selected' : '' ?>>
                                        <?= $status ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <button type="submit" class="btn btn-sm btn-success w-100">Update Status</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
</body>
</html>