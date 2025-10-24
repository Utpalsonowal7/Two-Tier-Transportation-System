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
     $district_id = (int) $_POST['district_id'];



     $district_query = "SELECT name FROM district WHERE id = $1";
     $district_result = pg_query_params($fsms_conn, $district_query, [$district_id]);

     if ($district_result && pg_num_rows($district_result) > 0) {
          $district = pg_fetch_assoc($district_result);
          $district_name = $district['name'];
     } else {

          echo "<p style='color: red;'>Error: Invalid district selected.</p>";
          exit();
     }


     $district_check = pg_query_params($fsms_conn, "SELECT id FROM district WHERE id = $1", [$district_id]);
     if (!$district_check || pg_num_rows($district_check) == 0) {
          echo "<script>alert('Invalid district selected');</script>";
          exit();
     }

     $checkQuery = pg_query_params(
          $master_conn,
          "SELECT 1 FROM warehouse WHERE latitude = $1 and longitude = $2",
          array($latitude, $longitude)
     );

     if (pg_num_rows($checkQuery) == 0) {
          $query = pg_query_params(
               $master_conn,
               "INSERT INTO warehouse (name, latitude, longitude, location, address, district_id, district_name)
              VALUES ($1, $2, $3, $4, $5, $6, $7)",
               array(
                    $name,
                    $latitude,
                    $longitude,
                    $location,
                    $address,
                    $district_id,
                    $district_name
               )
          );


          if ($query) {
               echo "<script>alert('Warehouse added successfully');</script>";
          } else {
               echo "<p style='color:red;'>Error: " . pg_last_error($master_conn) . "</p>";
          }
     } else {
          echo "<script>alert('Warehouse with the same latitude and longitude already exists.');</script>";
     }
}


$district_result = pg_query($fsms_conn, "SELECT id, name FROM district");
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Add Warehouse</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-warehouse"></i></span>
               <h3 class="slash">/</h3>
               <h2>Add Warehouse</h2>
          </div>

          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" required><br><br>

                    <label>Latitude</label><br>
                    <input type="text" name="latitude" pattern="^\d{1,2}\.\d{1,6}$" placeholder="Use this format: 12.123456 → number with 6 digits after the decimal." required><br><br>

                    <label>Longitude</label><br>
                    <input type="text" name="longitude" pattern="^\d{1,2}\.\d{1,6}$" placeholder="Use this format: 12.123456 → number with 6 digits after the decimal." required><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" required><br><br>

                    <label>Address</label><br>
                    <textarea name="address" rows="5" cols="100" required></textarea><br><br>

                    <label>Select District</label><br>
                    <select name="district_id" required>
                         <option value="">-- Select District --</option>
                         <?php
                         if ($district_result) {
                              while ($district = pg_fetch_assoc($district_result)) {
                                   echo "<option value=\"" . htmlspecialchars($district['id']) . "\">" . htmlspecialchars($district['name']) . "</option>";
                              }
                         }
                         ?>
                    </select><br><br>
<div class="form-btn">
                    <button type="submit" >Add Warehouse</button>
                    </div>
               </form>
          </div>
     </div>

</body>

</html>