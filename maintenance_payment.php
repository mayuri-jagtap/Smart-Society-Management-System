<?php

require_once 'config.php';

if (!isset($_SESSION['resident_id']) || ($_SESSION['resident_role'] !== 'admin' && $_SESSION['resident_role'] !== 'user') )
{
  	header('Location: logout.php');
  	exit();
}

if(isset($_POST['maintenance_payment']))
{
	// Validate the form data
  	$paid_amount = $_POST['paid_amount'];
  	$paid_date = $_POST['paid_date'];
  	$id = $_POST['id'];

  	if (empty($paid_date)) 
  	{
	    $errors[] = 'Please Select Maintenance Payment Date';
  	}
  	if (empty($paid_amount)) 
  	{
    	$errors[] = 'Please enter Maintenance Amount You Paid';
  	} 
  	else if($paid_amount != $_POST['hidden_amount'])
  	{
  		$errors[] = 'Amount not match, please enter ' . $_POST['hidden_amount'] . '';
  	}
  	// If the form data is valid, update the user's password
  	if (empty($errors)) 
  	{    $id = $_GET['id'];
  		// Insert user data into the database
	    $stmt = $pdo->prepare("UPDATE maintenance SET paid_date = ?, paid_amount = ? WHERE id = ?");

	    $stmt->execute([$paid_date, $paid_amount, $id]);

	    $admin_id = $pdo->query("SELECT id FROM resident WHERE role = 'admin'")->fetchColumn();

	    // insert notification data into notifications table
		$message = "Bill Payment Done by Resident- ".$_POST['resident_id'].".";
		
		$notification_link = 'maintenance_payment.php?id='.$id.'&action=notification';
		$stmt = $pdo->prepare("INSERT INTO notifications (resident_id, notification_type, event_id, message, link) VALUES (?, ?, ?, ?, ?)");
		$stmt->execute([$admin_id, 'Maintenance Bill Payment', $id, $message, $notification_link]);

  		$_SESSION['success'] = 'Maintenance Payment has been done';

  		header('location:maintenance.php');
  		exit();
  	}
}

if(isset($_GET['id']))
{
	$stmt = $pdo->prepare("SELECT maintenance.id, maintenance.resident_id, maintenance.amount, maintenance.month, maintenance.paid_date, maintenance.paid_amount, maintenance.created_at FROM maintenance WHERE maintenance.id = ?");

	$stmt->execute([$_GET['id']]);

	$maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

	if(isset($_GET['action']) && $_GET['action'] == 'notification')
	{
		if($_SESSION['resident_role'] == 'admin')
		{
			$notification_type = 'Maintenance Bill Payment';
		}
		else
		{
			$notification_type = 'Maintenance Bill';
		}
		$stmt = $pdo->prepare("UPDATE notifications SET read_status = 'read' WHERE resident_id = '".$_SESSION['resident_id']."' AND notification_type = '".$notification_type."' AND event_id = '".$_GET['id']."'");

		$stmt->execute();
	}
}

include('header.php');

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Bill Payment</h1>
    <ol class="breadcrumb mb-4">
    	<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="maintenance.php">Maintenance Bills Management</a></li>
        <li class="breadcrumb-item active">Maintenance Bill Payment</li>
    </ol>
	<div class="col-md-4">
		<?php

		if(isset($errors))
        {
            foreach ($errors as $error) 
            {
                echo "<div class='alert alert-danger'>$error</div>";
            }
        }

		?>
		<div class="card">
			<div class="card-header">
				<h5 class="card-title">Maintenance Bill Payment</h5>
			</div>
			<div class="card-body">
				<form method="post">
				  	<div class="mb-3">
				  		<div class="row">
				    		<div class="col-md-5"><b>Resident ID</b></div>
				    		<div class="col-md-7"><?php echo (isset($maintenance['resident_id'])) ? $maintenance['resident_id'] : ''; ?></div>
				    	</div>
				    	<div class="row">
				    		<div class="col-md-5"><b>Maintenance Bill Amount</b></div>
				    		<div class="col-md-7"><?php echo (isset($maintenance['amount'])) ? $maintenance['amount'] : ''; ?></div>
				    	</div>
				    	<div class="row">
				    		<div class="col-md-5"><b>Bill Month</b></div>
				    		<div class="col-md-7"><?php echo (isset($maintenance['month'])) ? $maintenance['month'] : ''; ?></div>
				    	</div>
				  	
				  	<?php
				  	if(isset($maintenance['paid_date']) && !is_null($maintenance['paid_date']))
				  	{
				  	?>
				  		<div class="row">
				    		<div class="col-md-5"><b>Payment Date</b></div>
				    		<div class="col-md-7"><?php echo (isset($maintenance['paid_date'])) ? $maintenance['paid_date'] : ''; ?></div>
				    	</div>
				    	<div class="row">
				    		<div class="col-md-5"><b>Paid Bill Amount</b></div>
				    		<div class="col-md-7"><?php echo (isset($maintenance['paid_amount'])) ? $maintenance['paid_amount'] : ''; ?></div>
				    	</div>
				  	<?php 
				  	}
				  	else
				  	{
				  		if($_SESSION['resident_role'] == 'user')
				  		{
				  	?>
				  	</div>
				  	<div class="mb-3">
				  		<label>Payment Date</label>
				  		<input type="date" class="form-control" name="paid_date" id="paid_date">
				  	</div>
				  	<div class="mb-3">
				    	<label for="paid_amount">Paid Bill Amount</label>
				    	<input type="number" id="paid_amount" name="paid_amount" class="form-control" step="0.01" value="">
				  	</div>
				  	<input type="hidden" name="id" value="<?php echo (isset($maintenance['id'])) ? $maintenance['id'] : ''; ?>" />
				  	<input type="hidden" name="hidden_amount" value="<?php echo (isset($maintenance['amount'])) ? $maintenance['amount'] : ''; ?>" />
				  	<input type="hidden" name="resident_id" value="<?php echo (isset($maintenance['resident_id'])) ? $maintenance['resident_id'] : ''; ?>" />
				  	<button type="submit" name="maintenance_payment" class="btn btn-primary">maintenance Payment</button>
				  	<?php
				  		}
				  		else
				  		{
				  	?>
				  		<div class="row">
				    		<div class="col-md-5"><b>Payment Status</b></div>
				    		<div class="col-md-7"><span class="badge bg-danger">Not Paid</span></div>
				    	</div>
				    </div>
				  	<?php
				  		}
				  	}
				  	?>
				</form>
			</div>
		</div>
	</div>
</div>

<?php

include('footer.php');

?>
<?php include 'voice_command.php'; ?>