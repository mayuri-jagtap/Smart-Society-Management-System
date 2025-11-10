<?php
require_once 'config.php';

if(isset($_POST['add_resident']))
{
    // Form data
    $name = $_POST['name'];
    $ssn = $_POST['ssn'];
    $house_number = $_POST['house_number'];
    $block_number = $_POST['block_number'];
    $street_name = $_POST['street_name'];
    $house_status = $_POST['status']; // available or occupied
    $password = $_POST['password'];
    $role = $_POST['role'];
    $allowed_roles = ['admin', 'user'];

    $errors = [];

    // Validation
    if (empty($name)) $errors[] = 'Please enter your name';
    if (empty($ssn)) $errors[] = 'SSN is required';
    elseif (strlen($ssn) != 9) $errors[] = 'SSN must be exactly 9 digits';
    else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM resident WHERE ssn = ?");
        $stmt->execute([$ssn]);
        if ($stmt->fetchColumn() > 0) $errors[] = 'SSN is already registered';
    }
    if (empty($house_number)) $errors[] = 'Please enter House Number';
    if (empty($block_number)) $errors[] = 'Please enter Block Number';
    if (empty($street_name)) $errors[] = 'Please enter Street Name';
    if (empty($password)) $errors[] = 'Please enter password';
    else $password = password_hash($password, PASSWORD_DEFAULT);
    if (empty($role)) $errors[] = 'Please select role';
    elseif (!in_array($role, $allowed_roles)) $errors[] = 'Invalid role';

    if (empty($errors)) {
        // Check if house exists
        $stmt = $pdo->prepare("SELECT id FROM house WHERE house_number = ? AND block_number = ? AND street_name = ?");
        $stmt->execute([$house_number, $block_number, $street_name]);
        $house = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($house) {
            $house_id = $house['id'];
            // Update house status
            $stmt = $pdo->prepare("UPDATE house SET status=? WHERE id=?");
            $stmt->execute([$house_status, $house_id]);
        } else {
            // Insert new house
            $stmt = $pdo->prepare("INSERT INTO house (house_number, block_number, street_name, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$house_number, $block_number, $street_name, $house_status]);
            $house_id = $pdo->lastInsertId();
        }

        // Insert resident
        $stmt = $pdo->prepare("INSERT INTO resident (name, ssn, house_id, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $ssn, $house_id, $password, $role]);

        $_SESSION['success'] = 'New resident added successfully';
        header('location:resident.php');
        exit();
    }
}

if (!isset($_SESSION['resident_id']) || $_SESSION['resident_role'] !== 'admin') {
    header('Location: logout.php');
    exit();
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add Resident</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="resident.php">Resident Management</a></li>
        <li class="breadcrumb-item active">Add Resident</li>
    </ol>

    <div class="col-md-4">
        <?php
        if(!empty($errors)) {
            foreach($errors as $error) {
                echo "<div class='alert alert-danger'>$error</div>";
            }
        }
        ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Add Resident</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="ssn">SSN</label>
                        <input type="text" class="form-control" id="ssn" name="ssn" placeholder="Enter SSN" value="<?php echo isset($_POST['ssn']) ? $_POST['ssn'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="house_number">House Number</label>
                        <input type="text" class="form-control" id="house_number" name="house_number" placeholder="Enter House Number" value="<?php echo isset($_POST['house_number']) ? $_POST['house_number'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="block_number">Block Number</label>
                        <input type="text" class="form-control" id="block_number" name="block_number" placeholder="Enter Block Number" value="<?php echo isset($_POST['block_number']) ? $_POST['block_number'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="street_name">Street Name</label>
                        <input type="text" class="form-control" id="street_name" name="street_name" placeholder="Enter Street Name" value="<?php echo isset($_POST['street_name']) ? $_POST['street_name'] : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status">House Status</label><br>
                        <input type="radio" id="available" name="status" value="available" checked>
                        <label for="available">Available</label>&nbsp;&nbsp;
                        <input type="radio" id="occupied" name="status" value="occupied">
                        <label for="occupied">Occupied</label>
                    </div>

                    <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                    </div>

                    <div class="mb-3">
                        <label for="role">Role</label><br>
                        <input type="radio" id="admin" name="role" value="admin" <?php echo (isset($_POST['role']) && $_POST['role']==='admin') ? 'checked' : 'checked'; ?>>
                        <label for="admin">Admin</label>&nbsp;&nbsp;
                        <input type="radio" id="user" name="role" value="user" <?php echo (isset($_POST['role']) && $_POST['role']==='user') ? 'checked' : ''; ?>>
                        <label for="user">User</label>
                    </div>

                    <button type="submit" name="add_resident" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
