<?php
session_start();

require_once "classes/Database.php";
$pdo = db(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first.");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php?error=Invalid request method.");
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("CSRF attack detected on update_cart. User ID: " . $_SESSION['user_id']);
    header("Location: cart.php?error=Security violation. Please refresh the page.");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
$new_qty = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$cart_id || !$new_qty || $new_qty < 1) {
    header("Location: cart.php?error=Invalid quantity or item ID.");
    exit;
}

$stmt_check = $pdo->prepare("
    SELECT p.stock, c.product_id 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.id = ? AND c.user_id = ?
");
$stmt_check->execute([$cart_id, $user_id]);
$item_details = $stmt_check->fetch();

if (!$item_details) {
    header("Location: cart.php?error=Item not found in your cart.");
    exit;
}

$max_stock = $item_details['stock'];
$message = "Cart updated successfully.";

if ($new_qty > $max_stock) {
    $new_qty = $max_stock; 
    $message = "Quantity adjusted. Only {$max_stock} units available.";
}

$stmt_update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");

if ($stmt_update->execute([$new_qty, $cart_id, $user_id])) {
    if ($new_qty < 1) {
        $stmt_delete = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt_delete->execute([$cart_id, $user_id]);
        $message = "Item removed from cart.";
    }
} else {
    $message = "Error updating cart item.";
    error_log("Database error updating cart item: " . print_r($stmt_update->errorInfo(), true));
}

header("Location: cart.php?msg=" . urlencode($message));
exit;