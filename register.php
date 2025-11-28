<?php
session_start();
require_once "classes/Database.php"; 
$pdo = db(); 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "inc/header.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security error: Invalid request token. Please refresh the page.");
    }
    
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($name === "" || $email === "" || $password === "") {
        $message = "All fields are required.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $message = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password, is_admin)
                    VALUES (?, ?, ?, 0)";
            $insert = $pdo->prepare($sql);

            if ($insert->execute([$name, $email, $hashed])) {
                unset($_SESSION['csrf_token']); 
                header("Location: login.php?msg=Registration successful! Please login.");
                exit;
            } else {
                $message = "Registration failed. Try again.";
            }
        }
    }
}
?>

<h2>User Registration</h2>

<?php if ($message): ?>
    <p style="color:red;"><?php echo $message; ?></p>
<?php endif; ?>

<form action="" method="POST" style="max-width:400px;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <label>Name:</label><br>
    <input type="text" name="name" required style="width:100%; padding:8px;"><br><br>
    
    <label>Email:</label><br>
    <input type="email" name="email" required style="width:100%; padding:8px;"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required style="width:100%; padding:8px;"><br><br>

    <button type="submit"
            style="padding:10px 15px; background:#333; color:white; border:none; border-radius:5px;">
        Register
    </button>
</form>

<p>Already have an account? <a href="login.php">Login here</a>.</p>

<?php include "inc/footer.php"; ?>