<?php
session_start();

require_once "classes/Database.php";
$pdo = db(); 

include "inc/header.php";

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please <a href='login.php'>login</a> to checkout.</p>";
    include "inc/footer.php";
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$sql = "
    SELECT cart.id AS cart_id, cart.quantity, products.id AS product_id,
           products.name, products.price, products.stock
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($items) == 0) {
    echo "<h3>Your cart is empty.</h3>";
    include "inc/footer.php";
    exit;
}

$total = 0;
$has_stock_issue = false; 
foreach ($items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    if ($item['quantity'] > $item['stock']) {
        $has_stock_issue = true;
    }
}

if (isset($_POST['place_order'])) {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("CSRF attack detected on checkout. User ID: " . $user_id);
        $message = "Security violation. Please refresh the page.";
        $has_stock_issue = true; 
    }
    if ($has_stock_issue) {
        $message = "Cannot place order. Please review your cart; some items exceed available stock.";
    } else {
        
        $pdo->beginTransaction();
        $success = true;

        try {
            $sql = "INSERT INTO orders (user_id, total_price, order_date) VALUES (?, ?, NOW())";
            $insert = $pdo->prepare($sql);
            $insert->execute([$user_id, $total]);
            $order_id = $pdo->lastInsertId();

            $details_sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $details_stmt = $pdo->prepare($details_sql);

            $stock_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stock_stmt = $pdo->prepare($stock_sql);

            foreach ($items as $item) {
                $details_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                $stock_stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $cart_sql = "DELETE FROM cart WHERE user_id = ?";
            $clear = $pdo->prepare($cart_sql);
            $clear->execute([$user_id]);
            
            $pdo->commit();
            $message = "Order placed successfully! Your order ID is: <strong>$order_id</strong>";
            
        } catch (Exception $e) {
            $pdo->rollback();
            $message = "Order placement failed due to a system error. Please try again. (Ref: {$e->getMessage()})";
            error_log("Checkout Transaction Error: " . $e->getMessage());
        }
    }
}
?>

<h2>Checkout</h2>

<?php if (!empty($message)): ?>
    <p style="color:<?= (strpos($message, 'successfully') !== false) ? 'green' : 'red'; ?>;">
        <?php echo $message; ?>
    </p>
<?php endif; ?>

<?php if ($has_stock_issue): ?>
    <p style="color:red; font-weight:bold;">
        ⚠️ IMPORTANT: There is a stock issue with one or more items. Please return to your <a href="cart.php">cart</a> to adjust quantities.
    </p>
<?php endif; ?>

<p>Please review your order:</p>

<table border="1" width="100%" cellpadding="10" style="border-collapse:collapse;">
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Subtotal</th>
    </tr>

    <?php foreach ($items as $item): ?>
        <tr <?= ($item['quantity'] > $item['stock']) ? 'style="color:red;"' : ''; ?>>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td>$<?php echo number_format($item['price'], 2); ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="3" align="right"><strong>Total:</strong></td>
        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
    </tr>
</table>

<br><br>

<?php if (!$has_stock_issue): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        
        <button type="submit" name="place_order"
                style="padding:12px 20px; background:green; color:white; border:none; border-radius:5px; cursor:pointer;">
            Confirm & Place Order ($<?php echo number_format($total, 2); ?>)
        </button>
    </form>
<?php endif; ?>

<a href="cart.php" style="
    display:inline-block;
    margin-top:10px;
    padding:8px 15px;
    background:#333;
    color:white;
    text-decoration:none;
    border-radius:5px;
">← Back to Cart</a>

<?php include "inc/footer.php"; ?>