<?php
session_start();
include 'db/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Employee WHERE emp_id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $emp_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['emp_id'] = $user['emp_id'];
        $_SESSION['emp_name'] = $user['emp_name'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Store Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="gpg-bg">
    <div class="center-container">
        <div class="gpg-card">
            <h1 class="gpg-title">Welcome to GPG Store</h1>
            <h2 class="gpg-login-title">Login</h2>
            <form method="post" action="">
                <input type="text" name="emp_id" placeholder="Username" class="gpg-input" required><br>
                <input type="password" name="password" placeholder="Password" class="gpg-input" required><br>
                <button type="submit" class="gpg-btn">Login</button>
                <?php if(isset($error)) { echo "<p class='error'>$error</p>"; } ?>
            </form>
            <p class="gpg-register-text">
                Don't have an account? <a href="register.php" class="gpg-register-link">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>