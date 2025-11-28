<?php
session_start();

require_once "classes/Database.php";
$pdo = db(); 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "inc/header.php";

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please <a href='login.php'>login</a> to view your cart.</p>";
    include "inc/footer.php";
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        cart.id AS cart_id, 
        cart.quantity, 
        products.name, 
        products.price, 
        products.image,
        products.stock
    FROM cart
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(); 
?>

<h2>Your Shopping Cart</h2>

<?php if (isset($_GET['msg'])): ?>
    <p style="color:green;"><?php echo htmlspecialchars($_GET['msg']); ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<?php if (count($items) == 0): ?>
    <p>Your cart is empty.</p>
    <a href="index.php" style="
        padding:10px 15px; 
        background:#333; 
        color:white; 
        text-decoration:none; 
        border-radius:5px;">
        Start Shopping
    </a>
<?php else: ?>

<table border="1" width="100%" cellpadding="10" style="border-collapse:collapse;">
    <thead>
        <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $total = 0;
    foreach ($items as $item):
       
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;

        $stock_alert = ($item['quantity'] > $item['stock']) ? 'style="color:red; font-weight:bold;"' : '';
    ?>
        <tr <?= $stock_alert ?>>
            <td><?php echo htmlspecialchars($item['name']); ?></td>

            <td>
                <img src="images/<?php echo htmlspecialchars($item['image']); ?>" 
                     width="90" height="70" style="object-fit:cover;">
                <?php if ($item['quantity'] > $item['stock']): ?>
                    <p style="color:red; font-size:0.8em;">⚠️ Out of Stock (Max: <?= $item['stock'] ?>)</p>
                <?php endif; ?>
            </td>

            <td>$<?php echo number_format($item['price'], 2); ?></td>

            <td>
                <form action="update_cart.php" method="POST" style="margin:0; display:flex; align-items:center;">
                    
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                    <input type="number" 
                           name="quantity" 
                           value="<?= $item['quantity'] ?>" 
                           min="1" 
                           max="<?= $item['stock'] ?>"
                           required 
                           style="width: 50px; padding: 5px;">
                    
                    <button type="submit" 
                            name="update"
                            style="padding: 5px; margin-left: 5px; background:#f0c14b; border: 1px solid #a88734; border-radius:3px; cursor:pointer;">
                        Update
                    </button>
                </form>
            </td>

            <td>$<?php echo number_format($subtotal, 2); ?></td>

            <td>
                <form action="remove_from_cart.php" method="POST" style="margin:0;">
                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <button type="submit" style="background:none; border:none; color:red; cursor:pointer; text-decoration:underline;">
                        Remove
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="4" align="right"><strong>Total:</strong></td>
        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
        <td></td>
    </tr>
    </tbody>
</table>

<br>

<a href="checkout.php" 
   style="padding:10px 15px; background:green; color:white; text-decoration:none; border-radius:5px;">
   Proceed to Checkout
</a>

<?php endif; ?>

<?php include "inc/footer.php"; ?>