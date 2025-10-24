<?php
ob_start();
session_start();
// echo "<pre>";
// print_r($_SESSION); 
// echo "</pre>";
// exit(); 
include('../includes/dbconnection.php');

if (!isset($_SESSION['login'])) {
	echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
	exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>System Admin Dashboard</title>
	<link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
	<link rel="stylesheet" href="../assets/style.css">
	<link rel="stylesheet" href="../assets/dashboard.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
	<?php include('../includes/process.php'); ?>
	<?php include('../includes/sidebar.php'); ?>
	<?php include('../includes/header.php'); ?>



	<div class="main-content">
		<div class="dashboard">
			<span class="icon"><i class="fa-solid fa-house-chimney"></i></span>
			<h3 class="slash">/</h3>
			<a href="#">
				<h2>Dashboard</h2>
			</a>
		</div>

		<div class="cards">
			<?php $query = "SELECT COUNT(*) AS total FROM district_admins";
			$result = pg_query($fsms_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalAdmins = $row['total'];

			?>
			<div class="card card1">
				<h3>Total Districts Admins</h3>
				<div class="circle blue countCircle" data-count="<?php echo $totalAdmins; ?>"><?php echo $totalAdmins; ?></div>
			</div>

			<?php $query = "SELECT COUNT(*) AS total FROM district_users";
			$result = pg_query($fsms_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalUser = $row['total'];
			?>
			<div class="card card2">
				<h3>Total Districts User's</h3>
				<div class="circle orange countCircle" data-count="<?php echo $totalUser; ?>"><?php echo $totalUser; ?></div>
			</div>

			<?php $query = "SELECT COUNT(*) AS total FROM warehouse";
			$result = pg_query($master_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalWarehouse = $row['total'];
			?>
			<div class="card card3">
				<h3>Total Registered Warehouse</h3>
				<div class="circle teal countCircle" data-count="<?php echo $totalWarehouse; ?>"><?php echo $totalWarehouse; ?></div>
			</div>

			<?php $query = "SELECT COUNT(*) AS total FROM wholesalers";
			$result = pg_query($master_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalWholesalers = $row['total'];
			?>
			<div class="card card4">
				<h3>Total Registered Wholesalers</h3>
				<div class="circle red countCircle" data-count="<?php echo $totalWholesalers; ?>"><?php echo $totalWholesalers; ?></div>
				<!-- <div class="circle red" id="countCircle">10000</div>  -->
			</div>

			<?php $query = "SELECT COUNT(*) AS total FROM retailers";
			$result = pg_query($master_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalRetailers = $row['total'];
			?>
			<div class="card card5">
				<h3>Total Registered Retailers</h3>
				<div class="circle blue countCircle" data-count="<?php echo $totalRetailers; ?>"><?php echo $totalRetailers; ?></div>
			</div>

			<?php $query = "SELECT COUNT(*) AS total FROM  warehouse_wholesale_map";
			$result = pg_query($master_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalWarehouseMapped = $row['total'];
			?>
			<div class="card card5">
				<h4>Total Wholesalers mapped with Warehouse</h4>
				<div class="circle blue countCircle" data-count="<?php echo $totalWarehouseMapped; ?>"><?php echo $totalWarehouseMapped; ?></div>
			</div>

			<?php $query = "SELECT COUNT(*) AS total FROM  wholesale_retailer_map";
			$result = pg_query($master_conn, $query);
			$row = pg_fetch_assoc($result);
			$totalRetailersMapped = $row['total'];
			?>
			<div class="card card5">
				<h4>Total Retailers Mapped with Wholesalers</h4>
				<div class="circle blue countCircle" data-count="<?php echo $totalRetailersMapped; ?>"><?php echo $totalRetailersMapped; ?></div>
			</div>

			<?php $query = " SELECT COUNT(*) AS total FROM transport  where date_trunc('month', transport_date) = date_trunc('month', CURRENT_DATE)";

			$result = pg_query($master_conn, $query);

			if ($result && pg_num_rows($result) > 0) {
				$row = pg_fetch_assoc($result);
				$monthlyTransportCountTier1 = $row['total'];
			} else {
				$monthlyTransportCountTier1 = 0;
			}
			?>
			<div class="card card5">
				<h3>Monthly Transports Tier1</h3>
				<div class="circle blue countCircle" data-count="<?php echo $monthlyTransportCountTier1; ?>"><?php echo $monthlyTransportCountTier1; ?></div>
			</div>

			<?php $query = " SELECT COUNT(*) AS total FROM transport_tier2  where date_trunc('month', created_at) = date_trunc('month', CURRENT_DATE)";

			$result = pg_query($master_conn, $query);

			if ($result && pg_num_rows($result) > 0) {
				$row = pg_fetch_assoc($result);
				$monthlyTransportCountTier2 = $row['total'];
			} else {
				$monthlyTransportCountTier2 = 0;
			}
			?>
			<div class="card card5">
				<h3>Monthly Transports Tier2</h3>
				<div class="circle blue countCircle" data-count="<?php echo $monthlyTransportCountTier2; ?>"><?php echo $monthlyTransportCountTier2; ?></div>
			</div>
		</div>
	</div>
	</div>

	<script src="../js/script.js"></script>
	<script src="../js/toggle.js"></script>
	<script src="../js/cardResize.js"></script>
</body>

</html>