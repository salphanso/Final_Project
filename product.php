<?php
session_start();

require_once "classes/Database.php";
$pdo = db(); 

include "inc/header.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo "<p>Invalid product request.</p>";
    include "inc/footer.php";
    exit;
}

$id = (int)$_GET['id']; 

$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$product = $stmt->fetch(); 

if (!$product) {
    echo "<p>Product not found.</p>";
    include "inc/footer.php";
    exit;
}

$stock_status = ($product['stock'] > 0) 
    ? "In Stock ({$product['stock']} available)" 
    : "Out of Stock";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<div style="max-width: 800px; margin: 20px auto; display: flex; gap: 40px; align-items: flex-start;">

    <div>
        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>" 
             style="width:300px; height:auto; object-fit:cover; border-radius:8px;">
    </div>

    <div>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>

        <p style="font-size: 1.5em; color: #b12704; font-weight: bold;">
            $<?php echo number_format($product['price'], 2); ?>
        </p>

        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        
        <p style="color: <?= ($product['stock'] > 0) ? 'green' : 'red'; ?>; font-weight: bold;">
            <?= $stock_status ?>
        </p>

        <?php if ($product['stock'] > 0): ?>
            <form action="add_to_cart.php" method="POST" style="margin-top: 20px;">
                
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>"> 

                <label for="quantity" style="font-weight: bold;">Quantity:</label>
                <input type="number" 
                       id="quantity" 
                       name="quantity" 
                       value="1" 
                       min="1" 
                       max="<?= $product['stock'] ?>"
                       required
                       style="width: 60px; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">

                <button type="submit"
                        style="padding:10px 15px; background:#f0c14b; color:#111; border:1px solid #a88734; border-radius:5px; cursor:pointer; font-weight:bold;">
                    🛒 Add to Cart
                </button>
            </form>
        <?php endif; ?>

        <a href="index.php" style="
            display:inline-block;
            margin-top: 20px;
            padding:10px 15px;
            background:#333;
            color:white;
            text-decoration:none;
            border-radius:5px;
        ">← Back to Home</a>
    </div>

</div>

<?php include "inc/footer.php"; ?>