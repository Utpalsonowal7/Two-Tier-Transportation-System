<?php
ob_start();
session_start();
include('../includes/dbconnection.php');

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}


$adminId = $_SESSION['adminid'];


$query = "SELECT district_id FROM district_admins WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, array($adminId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $districtId = $row['district_id'];
} else {
     echo "District not found!";
     exit();
}


// $query = "SELECT name FROM district WHERE id = $1";
// $result = pg_query_params($admin_conn, $query, array($districtId));

// if ($result && pg_num_rows($result) > 0) {
//      $row = pg_fetch_assoc($result);
//      $districtName = $row['name'];
// } else {
//      $districtName = "Unknown District";
// }

$query = "SELECT COUNT(*) AS total FROM district_users WHERE district_id = $1";
$result = pg_query_params($fsms_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $totalDistrictUsers = $row['total'];
} else {
     $totalDistrictUsers = 0;
}

$query = "SELECT COUNT(*) AS total FROM warehouse  WHERE district_id = $1";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $totalWarehouse = $row['total'];
} else {
     $totalWarehouse = 0;
}


$query = "SELECT COUNT(*) AS total FROM wholesalers  WHERE district_id = $1";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $totalWholesale = $row['total'];
} else {
     $totalWholesale = 0;
}

$query = "SELECT COUNT(*) AS total FROM retailers  WHERE district_id = $1";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $totalRetailer = $row['total'];
} else {
     $totalRetailer = 0;
}

$query = " SELECT COUNT(*) AS total FROM transport WHERE district_id = $1
  AND date_trunc('month', transport_date) = date_trunc('month', CURRENT_DATE)
";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $monthlyTransportCountTier1 = $row['total'];
} else {
     $monthlyTransportCountTier1 = 0;
}

$query = " SELECT COUNT(*) AS total FROM transport_tier2 WHERE district_id = $1
  AND date_trunc('month', created_at) = date_trunc('month', CURRENT_DATE)
";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $monthlyTransportCountTier2 = $row['total'];
} else {
     $monthlyTransportCountTier2 = 0;
}

$query = "SELECT COUNT(*) AS total FROM warehouse_wholesale_map WHERE district_id = $1";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $totalWholesalersMapped = $row['total'];
} else {
     $totalWholesalersMapped = 0;
}

$query = "SELECT COUNT(*) AS total FROM wholesale_retailer_map WHERE district_id = $1";
$result = pg_query_params($master_conn, $query, array($districtId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $totalUser = $row['total'];
} else {
     $totalUser = 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Dist Admin Dashboard</title>
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
     <?php include('../includes/header.php'); ?>
     <?php include('../includes/dist_sidebar.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-house-chimney"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Dashboard</h2>
               </a>
          </div>

          <div class="cards">
               <div class="card card1">
                    <h3>Total District Users</h3>
                    <div class="circle blue countCircle" data-count="<?php echo $totalDistrictUsers; ?>"><?php echo $totalDistrictUsers; ?></div>
               </div>
               <!-- 
               <div class="card card2">
                    <h3>Total Districts User's</h3>
                    <div class="circle orange"></div>
               </div>
                -->

               <div class="card card3">
                    <h3>Total Registered Warehouse</h3>
                    <div class="circle teal countCircle" data-count="<?php echo $totalWarehouse; ?>"><?php echo $totalWarehouse; ?></div>
               </div>


               <div class="card card4">
                    <h3>Total Registered Wholesalers</h3>
                    <div class="circle red countCircle" data-count="<?php echo $totalWholesale; ?>"><?php echo $totalWholesale; ?></div>
               </div>


               <div class="card card5">
                    <h3>Total Registered Retailers</h3>
                    <div class="circle blue countCircle" data-count="<?php echo $totalRetailer; ?>" ><?php echo $totalRetailer; ?></div>
               </div>


               <div class="card card5">
                    <h3>Monthly Transports Tier1</h3>
                    <div class="circle blue countCircle" data-count="<?php echo $monthlyTransportCountTier1; ?>"><?php echo $monthlyTransportCountTier1; ?></div>
               </div>

               <div class="card card5">
                    <h3>Monthly Transports Tier2</h3>
                    <div class="circle blue countCircle" data-count="<?php echo $monthlyTransportCountTier2; ?>"><?php echo $monthlyTransportCountTier2; ?></div>
               </div>

               <div class="card card5">
                    <h4>Total Wholesalers mapped with Warehouse</h4>
                    <div class="circle blue countCircle" data-count="<?php echo $totalWholesalersMapped; ?>"><?php echo $totalWholesalersMapped; ?></div>
               </div>

               <div class="card card5">
                    <h4>Total Retailers mapped with Wholesalers</h4>
                    <div class="circle blue countCircle" data-count="<?php echo $totalUser; ?>"><?php echo $totalUser; ?></div>
               </div>
          </div>
     </div>

     <script src="../js/script.js"></script>
     <script src="../js/cardResize.js"></script>
</body>

</html>