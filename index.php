<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "classes/Database.php";
$pdo = db(); 
include "inc/header.php";

global $base_path; 

$categories = [
    'desktops', 
    'processors', 
    'graphic_cards', 
    'memories', 
    'laptops', 
    'accessories'
];

$selected_category = $_GET['category'] ?? '';
if (!empty($selected_category) && !in_array($selected_category, $categories)) {
    $selected_category = ''; 
}

$products = [];

$sql = "SELECT * FROM products";
$params = [];

if (!empty($selected_category)) {
    $sql .= " WHERE category = ?";
    $params[] = $selected_category;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="GET" action="index.php" class="mb-4 d-flex align-items-center">
    <label for="category_filter" class="form-label me-2 mb-0">Browse by Category:</label>
    <select name="category" id="category_filter" class="form-select" style="width: 250px;" onchange="this.form.submit()">
        <option value="">-- All Products --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" 
                    <?= ($selected_category == $cat) ? 'selected' : '' ?>>
                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $cat))) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<h2>Latest Products</h2>

<?php if (count($products) == 0): ?>
    <div class="alert alert-info" role="alert">No products are currently available in this category.</div>
<?php else: ?>

<div style="display:flex; flex-wrap:wrap; gap:20px;">
<?php foreach ($products as $product): ?>
    <div style="width:220px; border:1px solid #ccc; padding:10px; border-radius:8px; text-align:center; background:white;">
        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>"
             style="width:100%; height:140px; object-fit:cover; border-radius:5px;">
        
        <h3 style="font-size:18px;"><?php echo htmlspecialchars($product['name']); ?></h3>
        <p><strong>$<?php echo number_format($product['price'], 2); ?></strong></p>

        <a href="<?= $base_path ?>product.php?id=<?php echo htmlspecialchars($product['id']); ?>" 
           style="display:inline-block; padding:8px 12px; background:#333; color:white; border-radius:5px; text-decoration:none;">
           View Details
        </a>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php include "inc/footer.php"; ?>