<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php?error=Access denied.");
    exit;
}
require_once "../classes/Database.php";
$pdo = db(); 

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    header("Location: manage_products.php?error=Invalid product ID for deletion.");
    exit;
}

$stmt_fetch = $pdo->prepare("SELECT name, image FROM products WHERE id = ?");
$stmt_fetch->execute([$product_id]);
$product = $stmt_fetch->fetch(PDO::FETCH_ASSOC);
$product_name = $product['name'] ?? 'Unknown Product';
$image_to_delete = $product['image'] ?? null;

$uploadFileDir = __DIR__ . '/../images/'; 

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$product_id])) {
    if ($image_to_delete) {
        $filePath = $uploadFileDir . $image_to_delete; 
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    header("Location: manage_products.php?msg=Product '{$product_name}' deleted successfully.");
    exit;
} else {
    $message = "Database error: Could not delete product.";
    header("Location: manage_products.php?error=" . urlencode($message));
    exit;
}
?>