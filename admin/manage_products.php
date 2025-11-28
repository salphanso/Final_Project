<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php?error=Access denied. Administrator privileges required.");
    exit;
}

require_once "../classes/Database.php";
$pdo = db(); 

$message = $_GET['msg'] ?? '';

$sql = "SELECT id, name, price, stock, category, image FROM products ORDER BY id DESC";
$stmt = $pdo->query($sql); 
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <style>
        .styled-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .styled-table th, .styled-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .styled-table th { background-color: #f2f2f2; }
        .action-link { margin-right: 10px; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>📦 Manage Products</h2>
    
    <?php if ($message): ?>
        <p style="color:green; font-weight:bold;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <p>
        <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
        <a href="add_product.php" class="btn btn-primary" style="margin-left: 10px;">➕ Add New Product</a>
    </p>

    <?php if (count($products) == 0): ?>
        <div class="alert alert-info" role="alert">
            No products found in the database.
        </div>
    <?php else: ?>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td>
                        <img src="../images/<?= htmlspecialchars($product['image']) ?>" 
                             width="50" height="50" style="object-fit: cover; border-radius: 3px;">
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($product['category'])) ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                    <td style="color: <?= $product['stock'] < 5 ? 'red' : 'green' ?>; font-weight: bold;">
                        <?= $product['stock'] ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="action-link" style="color: blue;">Edit</a>
                        
                        <a href="delete_product.php?id=<?= $product['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this product: <?= addslashes($product['name']) ?>? This action is permanent.');"
                           class="action-link" style="color: red;">
                           Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>
</div>

</body>
</html>