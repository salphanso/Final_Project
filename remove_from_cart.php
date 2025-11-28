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
    error_log("CSRF attack detected on remove_from_cart. User ID: " . $_SESSION['user_id']);
    header("Location: cart.php?error=Security violation. Please refresh the page.");
    exit;
}

$cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$cart_id) {
    header("Location: cart.php?error=Invalid item ID.");
    exit;
}

$sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$cart_id, $user_id])) {
    if ($stmt->rowCount() > 0) {
        $message = "Item removed from cart.";
    } else {
        $message = "Item not found in your cart.";
    }
} else {
    $message = "Database error occurred.";
    error_log("Database error removing cart item: " . print_r($stmt->errorInfo(), true));
}

header("Location: cart.php?msg=" . urlencode($message));
exit;