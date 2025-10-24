<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script> alert('session has expired, please log in again!');window.location.href = 'login.php' </script>";
     exit();
}


$districtId = isset($_POST['district_id']) ? $_POST['district_id'] : null;
$retailerDetails = [];

if ($districtId) {
     $retailerQuery = pg_query($master_conn, "SELECT name FROM retailers WHERE district_id = $districtId");
}

if (isset($_POST['retailer_name']) && $districtId) {
     $selectedRetailer = pg_escape_string($master_conn, $_POST['retailer_name']);
     $retailerDetailsQuery = pg_query($master_conn, "SELECT * FROM retailers WHERE name = '$selectedRetailer' AND district_id = $districtId");
     if ($retailerDetailsQuery && pg_num_rows($retailerDetailsQuery) > 0) {
          $retailerDetails = pg_fetch_assoc($retailerDetailsQuery);
     }
}

if (isset($_POST['map'])) {
     $retailer_name = $_POST['retailer_name'];
     $wholesaler_name = $_POST['wholesaler_name'];
     $transport_rate = $_POST['transport_rate'];
     $distance = $_POST['distance'];

     $checkQuery = pg_query($master_conn, "SELECT 1 FROM wholesale_retailer_map 
          WHERE wholesaler_name = '$wholesaler_name' 
          AND retailer_name = '$retailer_name' 
          AND district_id = $districtId");

     $district_query = "SELECT name FROM district WHERE id = $1";
     $district_result_check = pg_query_params($fsms_conn, $district_query, [$districtId]);

     if (pg_num_rows($district_result_check) > 0) {
          $district = pg_fetch_assoc($district_result_check);
          $district_name = $district['name'];
     } else {

          echo "<p style='color: red;'>Error: Invalid district selected.</p>";
          exit();
     }

     if (pg_num_rows($checkQuery) == 0) {
          $insertQuery = pg_query($master_conn, "
               INSERT INTO wholesale_retailer_map 
               (wholesaler_name, retailer_name, district_id, transport_rate, distance, district_name) 
               VALUES ('$wholesaler_name', '$retailer_name', $districtId, $transport_rate, $distance, '$district_name')");

          if ($insertQuery) {
               echo "<script>alert('Mapping created successfully!');</script>";
          } else {
               echo "<script>alert(' Error: " . pg_last_error($mapp_conn) . "');</script>";
          }
     } else {
          echo "<script>alert('This mapping already exists.');</script>";
     }
}

$district_result = pg_query($fsms_conn, "SELECT * FROM district");
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <title>Map Retailer to Wholesaler</title>
     <script>
          function autoSubmit() {
               document.getElementById("mainForm").submit();
          }
     </script>
</head>

<body>

     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-link"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Map Retailer</h2>
               </a>
          </div>

          
          <div class="form">
               <form method="POST" id="mainForm">
                    <label for="district">Select District</label>
                    <select name="district_id" id="district" required onchange="autoSubmit()">
                         <option value="">-- Select District --</option>
                         <?php
                         if ($district_result) {
                              while ($district = pg_fetch_assoc($district_result)) {
                                   $selected = ($district['id'] == $districtId) ? 'selected' : '';
                                   echo "<option value=\"" . htmlspecialchars($district['id']) . "\" $selected>" . htmlspecialchars($district['name']) . "</option>";
                              }
                         } else {
                              echo "<option value=''>No districts available</option>";
                         }
                         ?>
                    </select><br><br>

                    <?php if ($districtId): ?>
                         <label for="retailer">Select Retailer:</label><br>
                         <select name="retailer_name" id="retailer" onchange="autoSubmit()" required>
                              <option value="">-- Select Retailer --</option>
                              <?php
                              pg_result_seek($retailerQuery, 0);
                              while ($row = pg_fetch_assoc($retailerQuery)) {
                                   $selected = (isset($_POST['retailer_name']) && $_POST['retailer_name'] === $row['name']) ? 'selected' : '';
                                   echo "<option value=\"" . htmlspecialchars($row['name']) . "\" $selected>" . htmlspecialchars($row['name']) . "</option>";
                              }
                              ?>
                         </select><br><br>
                    <?php endif; ?>

                    <?php if (!empty($retailerDetails)): ?>
                         <label>Nearest Wholesaler:</label><br>
                         <input type="text" name="wholesaler_name"
                              value="<?php echo htmlspecialchars($retailerDetails['nearest_wholesaler_name']); ?>" readonly
                              required><br><br>

                         <label>Distance to Wholesaler (KM):</label><br>
                         <input type="number" step="0.01" name="distance"
                              value="<?php echo htmlspecialchars($retailerDetails['nearest_wholesaler_distance']); ?>"
                              readonly required><br><br>

                         <label>Transport Rate (₹):</label><br>
                         <input type="number" step="0.01" name="transport_rate" required><br><br>

                         <div class="form-btn">
                              <button type="submit" name="map">Map</button>
                         </div>
                    <?php endif; ?>
               </form>
          </div>
     </div>
</body>

</html>