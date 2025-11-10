<?php
require_once 'config.php';

if (!isset($_SESSION['resident_id']) || $_SESSION['resident_role'] !== 'admin') {
    header('Location: logout.php');
    exit();
}

$errors = [];

// Fetch house data if ID is set
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM house WHERE id = ?");
    $stmt->execute([$id]);
    $house = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$house) {
        $_SESSION['error'] = "House not found!";
        header('Location: house.php');
        exit();
    }
}

// Handle form submission
if(isset($_POST['edit_house'])) {
    $house_number = $_POST['house_number'];
    $street_name = $_POST['street_name'];
    $block_number = $_POST['block_number'];
    $status = $_POST['status'];
    $id = $_POST['id'];

    if (empty($house_number)) $errors[] = 'House Number is required';
    if (empty($street_name)) $errors[] = 'Street Name is required';
    if (empty($block_number)) $errors[] = 'Block Number is required';

    if (empty($errors)) {
        $sql = "UPDATE house SET house_number = ?, street_name = ?, block_number = ?, status = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$house_number, $street_name, $block_number, $status, $id]);

        $_SESSION['success'] = 'House Data Updated Successfully';
        header('Location: house.php');
        exit();
    }
}

include('header.php');
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit House</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="house.php">House Management</a></li>
        <li class="breadcrumb-item active">Edit House</li>
    </ol>

    <div class="col-md-4">
        <?php
        if(!empty($errors)) {
            foreach ($errors as $error) {
                echo "<div class='alert alert-danger'>$error</div>";
            }
        }
        ?>

        <div class="card">
            <div class="card-header"><h5 class="card-title">Edit House Data</h5></div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">House Number</label>
                        <input type="text" name="house_number" class="form-control" value="<?= htmlspecialchars($house['house_number']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Street Name</label>
                        <input type="text" name="street_name" class="form-control" value="<?= htmlspecialchars($house['street_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Block Number</label>
                        <input type="text" name="block_number" class="form-control" value="<?= htmlspecialchars($house['block_number']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available" <?= $house['status']=='available'?'selected':'' ?>>Available</option>
                            <option value="occupied" <?= $house['status']=='occupied'?'selected':'' ?>>Occupied</option>
                        </select>
                    </div>
                    <input type="hidden" name="id" value="<?= $house['id'] ?>">
                    <button type="submit" name="edit_house" class="btn btn-primary">Update House</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
