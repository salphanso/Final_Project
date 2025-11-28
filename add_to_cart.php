<?php
session_start();

require_once "classes/Database.php";
$pdo = db(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first to add items.");
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("CSRF attack detected on add_to_cart. User ID: " . $_SESSION['user_id']);
    header("Location: index.php?error=Security violation. Please refresh the page.");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$qty = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$product_id || !$qty || $qty < 1) {
    header("Location: index.php?error=Invalid product data.");
    exit;
}

$stmt_product = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmt_product->execute([$product_id]);
$product = $stmt_product->fetch();

if (!$product) {
    header("Location: index.php?error=Product does not exist.");
    exit;
}

$max_stock = $product['stock'];

$stmt_existing = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt_existing->execute([$user_id, $product_id]);
$existing = $stmt_existing->fetch();

if ($existing) {
    $newQty = $existing['quantity'] + $qty;
    
    if ($newQty > $max_stock) {
        $newQty = $max_stock; 
        $message = "Only {$max_stock} available. Quantity adjusted.";
    } else {
        $message = "Item quantity updated.";
    }

    $stmt_update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt_update->execute([$newQty, $existing['id']]);

} else {
    if ($qty > $max_stock) {
        $qty = $max_stock;
        $message = "Only {$max_stock} available. Quantity adjusted.";
    } else {
        $message = "Item added to cart.";
    }

    $stmt_insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)");
    $stmt_insert->execute([$user_id, $product_id, $qty]);
}
header("Location: cart.php?msg=" . urlencode($message));
exit;