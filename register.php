<?php
session_start();
// Include your database connection file. Ensure this file connects to your database correctly.
include 'db/connection.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_name = trim($_POST['emp_name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $tel = trim($_POST['tel']);
    $address = trim($_POST['address']);
    $role = 'employee'; // Default role for new registrations

    // Validate inputs
    if (empty($emp_name) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Basic email validation
        $error = 'Invalid email format';
    } else {
        // Hash the password for security
        // IMPORTANT: Always hash passwords before storing them in the database!
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT emp_id FROM Employee WHERE emp_name = ? OR email = ?");
        // Check for prepare statement error
        if ($stmt === false) {
            $error = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param("ss", $emp_name, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Username or email already exists';
            } else {
                // Insert new user, including the 'role'
                $stmt_insert = $conn->prepare("INSERT INTO Employee (emp_name, password, email, tel, address, role) VALUES (?, ?, ?, ?, ?, ?)");
                // Check for prepare statement error
                if ($stmt_insert === false) {
                    $error = 'Database error: ' . $conn->error;
                } else {
                    $stmt_insert->bind_param("ssssss", $emp_name, $hashed_password, $email, $tel, $address, $role);
                    
                    if ($stmt_insert->execute()) {
                        $success = 'Registration successful! You can now <a href="login.php" class="gpg-login-link">login</a>.';
                        // Clear form fields after successful registration
                        $emp_name = $email = $tel = $address = '';
                    } else {
                        $error = 'Registration failed. Please try again. Error: ' . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Store Management</title>
    <!-- Assuming assets/css/style.css exists, otherwise you might want to remove this line -->
    <link rel="stylesheet" href="assets/css/style.css"> 
    <style>
        /* General body styling, assuming gpg-bg would come from style.css or elsewhere */
        body {
            font-family: 'Inter', sans-serif; /* Using Inter font as per instructions */
            margin: 0;
            padding: 0;
        }
        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .gpg-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box; /* Ensures padding is included in the width */
        }
        .gpg-title {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            border-radius: 4px; /* Rounded corners */
        }
        .gpg-login-title {
            text-align: center;
            color: #444;
            margin-bottom: 1.5rem;
            border-radius: 4px; /* Rounded corners */
        }
        .gpg-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box; /* Ensures padding is included in the width */
        }
        .gpg-btn {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease; /* Smooth transition on hover */
        }
        .gpg-btn:hover {
            background-color: #45a049;
        }
        .error {
            color: #f44336;
            margin: 10px 0;
            text-align: center;
            padding: 8px;
            background-color: #ffe0e0;
            border-radius: 4px;
        }
        .success {
            color: #4CAF50;
            margin: 10px 0;
            text-align: center;
            padding: 8px;
            background-color: #e0ffe0;
            border-radius: 4px;
        }
        .gpg-login-text {
            text-align: center;
            margin-top: 1rem;
        }
        .gpg-login-link {
            color: #4CAF50;
            text-decoration: none;
        }
        .gpg-login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="gpg-bg">
    <div class="center-container">
        <div class="gpg-card">
            <h1 class="gpg-title">GPG Store</h1>
            <h2 class="gpg-login-title">Create New Account</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <input type="text" name="emp_name" placeholder="Username" class="gpg-input" value="<?php echo isset($emp_name) ? htmlspecialchars($emp_name) : ''; ?>" required>
                <input type="email" name="email" placeholder="Email" class="gpg-input" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <input type="password" name="password" placeholder="Password" class="gpg-input" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" class="gpg-input" required>
                <input type="tel" name="tel" placeholder="Phone Number (Optional)" class="gpg-input" value="<?php echo isset($tel) ? htmlspecialchars($tel) : ''; ?>">
                <input type="text" name="address" placeholder="Address (Optional)" class="gpg-input" value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                
                <button type="submit" class="gpg-btn">Register</button>
            </form>
            
            <p class="gpg-login-text">
                Already have an account? <a href="login.php" class="gpg-login-link">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
