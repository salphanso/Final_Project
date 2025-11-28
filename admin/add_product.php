\<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php?error=Access denied.");
    exit;
}
require_once "../classes/Database.php";
$pdo = db(); 
$message = "";
$name = $description = $price = $stock = $category = ""; 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Security error. Please try again.";
    } else {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

        if (empty($name) || empty($description) || $price === false || $stock === false) {
            $message = "All fields are required and must be valid numbers for price/stock.";
        } else {
            $image_filename = null;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
     
                    $uploadFileDir = __DIR__ . '/../images/'; 
                    $destPath = $uploadFileDir . $newFileName;

                    if(move_uploaded_file($fileTmpPath, $destPath)) {
                        $image_filename = $newFileName;
                    } else {
                        $message = "Error moving the uploaded file. Check folder permissions (online_computer_store/images).";
                    }
                } else {
                    $message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                }
            } else {
                $message = "Image upload failed or no image was provided.";
            }

            if ($image_filename && !$message) {
                $sql = "INSERT INTO products (name, description, price, stock, category, image) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$name, $description, $price, $stock, $category, $image_filename])) {
                    header("Location: manage_products.php?msg=Product '{$name}' added successfully!");
                    exit;
                } else {
                    $message = "Database error: Could not insert product.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>➕ Add New Product</h2>
    
    <p>
        <a href="manage_products.php" class="btn btn-secondary">← Back to Manage Products</a>
    </p>
    
    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select name="category" id="category" class="form-control" required>
                <option value="desktops" <?= ($category == 'desktops') ? 'selected' : '' ?>>Desktops</option>
                <option value="processors" <?= ($category == 'processors') ? 'selected' : '' ?>>Processors</option>
                <option value="graphic_cards" <?= ($category == 'graphic_cards') ? 'selected' : '' ?>>Graphic Cards</option>
                <option value="memories" <?= ($category == 'memories') ? 'selected' : '' ?>>Memories</option>
                <option value="laptops" <?= ($category == 'laptops') ? 'selected' : '' ?>>Laptops</option>
                <option value="accessories" <?= ($category == 'accessories') ? 'selected' : '' ?>>Accessories</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price ($)</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?= htmlspecialchars($price) ?>" required>
        </div>

        <div class="mb-3">
            <label for="stock" class="form-label">Stock Quantity</label>
            <input type="number" name="stock" id="stock" class="form-control" value="<?= htmlspecialchars($stock) ?>" required>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" name="image" id="image" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
</div>
</body>
</html>