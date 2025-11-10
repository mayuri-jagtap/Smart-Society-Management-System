<?php
session_start();
session_unset();  // Remove all session variables
session_destroy(); // Destroy the session completely



require_once 'config.php'; // your DB connection

// Redirect already logged-in users
if (isset($_SESSION['resident_id'])) {
    if ($_SESSION['resident_role'] == 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: maintenance.php');
    }
    exit();
}

$errors = [];

if (isset($_POST['btn_login'])) {
    $ssn = trim($_POST['ssn']);
    $password = trim($_POST['password']);

    if (empty($ssn)) $errors[] = "Please enter SSN.";
    if (empty($password)) $errors[] = "Please enter password.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM resident WHERE ssn = ?");
        $stmt->execute([$ssn]);
        $resident = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resident) {
            $passwordHash = $resident['password']; // stored hash from DB

            if (password_verify($password, $passwordHash)) {
                // Login successful
                $_SESSION['resident_id'] = $resident['id'];
                $_SESSION['resident_name'] = $resident['name'];
                $_SESSION['resident_role'] = $resident['role'];

                if ($resident['role'] == 'user') {
                    header("Location: maintenance.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $errors[] = "Invalid password!";
            }
        } else {
            $errors[] = "SSN not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Housify Login</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/lux/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body id="login">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-6 col-lg-4">
            <h1 class="text-center mb-4">Housify</h1>

            <?php
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            }
            ?>

            <div class="card">
                <div class="card-header text-center">
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="ssn" class="form-label">SSN</label>
                            <input type="text" name="ssn" id="ssn" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" name="btn_login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>

            
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
