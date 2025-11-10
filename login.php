<?php
session_start();
require_once 'config.php';

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM resident WHERE name = :username OR ssn = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user) {
        // Assuming password is stored in plain text (or use password_verify if hashed)
        if($password === $user['password']) { 
            $_SESSION['resident_id'] = $user['id'];
            $_SESSION['resident_name'] = $user['name'];
            $_SESSION['resident_role'] = $user['role']; // admin or user

            // Redirect based on role
            if($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: resident_dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Housify</title>
</head>
<body>
<h2>Login</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    <label>Username (Name or SSN):</label>
    <input type="text" name="username" required><br><br>
    <label>Password:</label>
    <input type="password" name="password" required><br><br>
    <input type="submit" name="login" value="Login">
</form>
</body>
</html>
