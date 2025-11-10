<?php
require_once 'config.php';

// Start session safely (prevents "session already active" warning)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admin can access this page
if (!isset($_SESSION['resident_id']) || $_SESSION['resident_role'] !== 'admin') {
    header('Location: logout.php');
    exit();
}

$errors = [];

if (isset($_POST['edit_resident'])) {
    $name = $_POST['name'];
    $house_id = $_POST['house_id'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $allowed_roles = ['admin', 'user'];
    $id = $_GET['id'];

    // Validation
    if (empty($name)) {
        $errors[] = 'Please enter the name.';
    }

    if (empty($house_id)) {
        $errors[] = 'Please enter a valid House ID.';
    }

    if (empty($role) || !in_array($role, $allowed_roles)) {
        $errors[] = 'Invalid role selected.';
    }

    if (empty($errors)) {
        if (!empty($password)) {
            // Update all fields with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE resident SET name = ?, house_id = ?, password = ?, role = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $house_id, $hashedPassword, $role, $id]);
        } else {
            // Keep the old password if no new one entered
            $sql = "UPDATE resident SET name = ?, house_id = ?, role = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $house_id, $role, $id]);
        }

        $_SESSION['success'] = 'Resident data updated successfully.';
        header('Location: resident.php');
        exit();
    }
}

// Fetch resident data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM resident WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Resident</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="resident.php">Resident Management</a></li>
        <li class="breadcrumb-item active">Edit Resident Data</li>
    </ol>

    <div class="col-md-4">
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Edit Resident Details</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?php echo htmlspecialchars($resident['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="house-id">House ID</label>
                        <input type="number" class="form-control" id="house-id" name="house_id"
                               value="<?php echo htmlspecialchars($resident['house_id']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter new password">
                    </div>

                    <div class="mb-3">
                        <label for="role">Role</label><br>
                        <input type="radio" id="admin" name="role" value="admin"
                            <?php echo ($resident['role'] === 'admin') ? 'checked' : ''; ?>>
                        <label for="admin">Admin</label><br>

                        <input type="radio" id="user" name="role" value="user"
                            <?php echo ($resident['role'] === 'user') ? 'checked' : ''; ?>>
                        <label for="user">User</label>
                    </div>

                    <button type="submit" name="edit_resident" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
