<?php
require 'db_config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    // Default role for new users
    $role = 'team_manager';

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $role);
    if ($stmt->execute()) {
        $message = "<span class='success-message'>Registration successful! You can now <a href='login.php'>login</a>.</span>";
    } else {
        if ($conn->errno == 1062) {
            $message = "<span class='error-message'>This username or email is already taken.</span>";
        } else {
            $message = "<span class='error-message'>Error: " . $stmt->error . "</span>";
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h1 class="title">📝 User Registration</h1>
        <form method="post" class="login-form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <?php if (!empty($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <p class="register-link">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
