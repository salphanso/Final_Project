<?php
session_start();

require_once "classes/Database.php";
$pdo = db(); 

include "inc/header.php";

$message = "";

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin'] == 1) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT id, name, password, is_admin FROM users WHERE email = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true); 

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = $user['is_admin'];

        if ($user['is_admin'] == 1) {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;

    } else {
        $message = "Invalid email or password.";
    }
}
?>

<h2>Login</h2>

<?php if ($message): ?>
    <p style="color:red;"><?php echo $message; ?></p>
<?php endif; ?>

<form action="" method="POST" style="max-width:400px;">
    <label>Email:</label><br>
    <input type="email" name="email" required style="width:100%; padding:8px;"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required style="width:100%; padding:8px;"><br><br>

    <button type="submit"
            style="padding:10px 15px; background:#333; color:white; border:none; border-radius:5px;">
        Login
    </button>
</form>

<p>Don't have an account? <a href="register.php">Create one</a>.</p>

<?php include "inc/footer.php"; ?>