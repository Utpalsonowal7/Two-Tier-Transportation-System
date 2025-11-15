<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session has expired, please log in again!'); window.location.href = 'login.php' </script>";
     exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $name = $_POST['name'];
     $latitude = $_POST['latitude'];
     $longitude = $_POST['longitude'];
     $location = $_POST['location'];
     $address = $_POST['address'];
     $nearest_wholesaler_distance = $_POST['nearest_wholesaler_distance'];
     $district_id = $_POST['district_id'];
     $nearest_wholesaler_id = $_POST['nearest_wholesaler_id'];

     $district_id = (int) $district_id;
     $nearest_wholesaler_id = (int) $nearest_wholesaler_id;

     $wh_query = "SELECT name FROM wholesalers WHERE serial_no = $1";
     $wh_result = pg_query_params($master_conn, $wh_query, [$nearest_wholesaler_id]);

     if ($wh_result && pg_num_rows($wh_result) > 0) {
          $wh_data = pg_fetch_assoc($wh_result);
          $nearest_wholesaler_name = $wh_data['name'];
     } else {
          echo "<script>alert('Invalid wholesaler selected');</script>";
          exit();
     }

     $district_query = "SELECT name FROM district WHERE id = $1";
     $district_result = pg_query_params($fsms_conn, $district_query, [$district_id]);

     if ($district_result && pg_num_rows($district_result) > 0) {
          $district = pg_fetch_assoc($district_result);
          $district_name = $district['name'];
     } else {

          echo "<p style='color: red;'>Error: Invalid district selected.</p>";
          exit();
     }


     if (!$district_result || pg_num_rows($district_result) == 0) {
          echo "<script>alert('Invalid district selected');</script>";
          exit();
     }

     // $checkQuery = pg_query_params(
     //      $master_conn,
     //      "SELECT 1 FROM retailers WHERE latitude = $1 and longitude = $2",
     //      array($latitude, $longitude)
     // );

     // if (pg_num_rows($checkQuery) == 0) {

          $query = pg_query_params(
               $master_conn,
               "INSERT INTO retailers 
        (name, latitude, longitude, location, address, nearest_wholesaler_distance, district_id, nearest_wholesaler_id, nearest_wholesaler_name, district_name) 
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",

               array(
                    $name,
                    $latitude,
                    $longitude,
                    $location,
                    $address,
                    $nearest_wholesaler_distance,
                    $district_id,
                    $nearest_wholesaler_id,
                    $nearest_wholesaler_name,
                    $district_name
               )
          );

          if ($query) {
               echo "<script>alert('Retailer added successfully');</script>";
          } else {
               echo "<p style='color: red;'>Error: " . pg_last_error($master_conn) . "</p>";
          }
     // } else {
     //      echo "<script>alert('Retailer with the same latitude and longitude already exists.');</script>";
     // }
}

$district_query = "SELECT * FROM district";
$district_result = pg_query($fsms_conn, $district_query);

$wh_query = "SELECT serial_no, name FROM wholesalers";
$wh_result = pg_query($master_conn, $wh_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Add Retailer</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

     <script>
          function loadWarehouses(districtId) {
               if (districtId) {
                    $.ajax({
                         url: 'fetch_wholesalers.php',
                         method: 'POST',
                         data: { district_id: districtId },
                         success: function (response) {
                              $('#warehouseOptions').html(response);
                         }
                    });
               } else {
                    $('#warehouseOptions').html('<option value="">-- Select Warehouse --</option>');
               }
          }
     </script>
</head>

<body>

     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-shopping-cart"></i>
               </span>
               <h3 class="slash">/</h3>
               <h2>Add Retailer</h2>
          </div>

          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" required><br><br>

                    <label>Latitude</label><br>
                    <input type="text" name="latitude" 
                         required><br><br>

                    <label>Longitude</label><br>
                    <input type="text" name="longitude" 
                         required><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" required><br><br>

                    <label>Address</label><br>
                    <textarea name="address" rows="5" cols="100" required></textarea><br><br>
                    
                    <label for="district">Select District</label><br>
                    <select name="district_id" id="district" onchange="loadWarehouses(this.value)" required>
                         <option value="">-- Select District --</option>
                         <?php
                         pg_result_seek($district_result, 0);
                         while ($district = pg_fetch_assoc($district_result)) {
                              echo "<option value=\"" . htmlspecialchars($district['id']) . "\">" . htmlspecialchars($district['name']) . "</option>";
                         }
                         ?>
                    </select><br><br>

                    <div id="warehouseContainer">
                         <label>Nearest Wholesaler Name:</label><br>
                         <select name="nearest_wholesaler_id" id="warehouseOptions" required>
                              <option value="">-- Select Wholesaler --</option>
                         </select><br><br>

                         <label>Distance to Wholesaler (in km):</label><br>
                         <input type="number" name="nearest_wholesaler_distance" required><br><br>
                    </div>

                    <div class="form-btn"> <button type="submit">Add Retailer</button>
                    </div>
               </form>
          </div>

</body>

</html>