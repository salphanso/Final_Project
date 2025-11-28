<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php?error=Access denied.");
    exit;
}

require_once "../classes/Database.php";
$pdo = db(); 

$base_redirect = 'view_orders.php';
$status_options = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: {$base_redirect}");
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("CSRF attack detected on process_order.");
    header("Location: {$base_redirect}?error=" . urlencode("Security violation."));
    exit;
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$new_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$order_id || !in_array($new_status, $status_options)) {
    header("Location: {$base_redirect}?error=" . urlencode("Invalid order ID or status provided."));
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    
    if ($stmt->execute([$new_status, $order_id])) {
      
        if ($stmt->rowCount() > 0) {
            $message = "Order #{$order_id} status updated to '{$new_status}' successfully.";
        } else {
            $message = "Order #{$order_id} status was already '{$new_status}'.";
        }
        
        header("Location: {$base_redirect}?msg=" . urlencode($message));
        exit;
        
    } else {
        throw new Exception("Execution failed.");
    }
    
} catch (Exception $e) {
    error_log("Admin order update error: " . $e->getMessage());
    header("Location: {$base_redirect}?error=" . urlencode("Database error: Could not update status."));
    exit;
}