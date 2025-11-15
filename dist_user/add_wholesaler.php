<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session has expired, please log in again!'); window.location.href = 'login.php' </script>";
     exit();
}


$adminId = $_SESSION['adminid'] ?? '';


$district_result = pg_query($fsms_conn, "SELECT * FROM district");


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'], $_POST['latitude'], $_POST['longitude'], $_POST['location'], $_POST['district_id'], $_POST['warehouse_names'], $_POST['distances'])) {
     $name = $_POST['name'];
     $latitude = $_POST['latitude'];
     $longitude = $_POST['longitude'];
     $location = $_POST['location'];
     $address = $_POST['address'] ?? '';
     $district_id = $_POST['district_id'];

     $warehouse_ids = $_POST['warehouse_names'];
     $distances = $_POST['distances'];


     $district_query = "SELECT name FROM district WHERE id = $1";
     $district_result_check = pg_query_params($fsms_conn, $district_query, [$district_id]);

     if ($district_result && pg_num_rows($district_result_check) > 0) {
          $district = pg_fetch_assoc($district_result_check);
          $district_name = $district['name'];
     } else {

          echo "<p style='color: red;'>Error: Invalid district selected.</p>";
          exit();
     }


     if (!$district_result_check || pg_num_rows($district_result_check) == 0) {
          echo "<script>alert('Invalid district selected');</script>";
          exit();
     }


     $warehouse_names = [];
     foreach ($warehouse_ids as $warehouse_id) {
          $warehouse_query = "SELECT name FROM warehouse WHERE serial_no = $1";
          $warehouse_result = pg_query_params($master_conn, $warehouse_query, [$warehouse_id]);
          if ($warehouse_result && pg_num_rows($warehouse_result) > 0) {
               $warehouse = pg_fetch_assoc($warehouse_result);
               $warehouse_names[] = $warehouse['name'];
          }
     }

     // $warehouse_names_array = "{" . implode(",", $warehouse_names) . "}";
     // $distances_array = "{" . implode(",", $distances) . "}";
     // $warehouse_ids_array = "{" . implode(",", $warehouse_ids) . "}";

     // $warehouse_names_array = "{" . implode('","', $warehouse_names) . "}";

     $warehouse_names_array = "{" . implode(",", array_map(function ($item) {
          return '"' . addslashes($item) . '"';
     }, $warehouse_names)) . "}";
     $distances_array = "{" . implode(",", $distances) . "}";
     $warehouse_ids_array = "{" . implode(",", $warehouse_ids) . "}";

     // $checkQuery = pg_query_params(
     //      $master_conn,
     //      "SELECT 1 FROM wholesalers WHERE  latitude = $1 or longitude = $2",
     //      array($latitude, $longitude)
     // );

     // if (pg_num_rows($checkQuery) == 0) {
          $query = pg_query_params(
               $master_conn,
               "INSERT INTO wholesalers (name, latitude, longitude, location, address, district_id, warehouse_ids, warehouse_names, distances, district_name) 
              VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
               array(
                    $name,
                    $latitude,
                    $longitude,
                    $location,
                    $address,
                    $district_id,
                    $warehouse_ids_array,
                    $warehouse_names_array,
                    $distances_array,
                    $district_name
               )
          );

          if ($query) {
               echo "<script>alert('Wholesaler added successfully'); window.location.href='add_wholesaler.php';</script>";
               exit();
          } else {
               echo "<p style='color: red;'>Error: " . pg_last_error($master_conn) . "</p>";
          }
     // } else {
     //      echo "<script>alert('Credentials already exists.');</script>";
     // }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Add Wholesaler</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script>

          function loadWarehouses(districtId) {
               if (districtId) {
                    $.ajax({
                         url: 'fetch_warehouse.php',
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

          function addWarehouseField() {
               const container = document.getElementById("warehouseContainer");
               const options = document.getElementById("warehouseOptions").innerHTML;
               const newField = document.createElement("div");
               newField.innerHTML = `
                <label>Warehouse Name:</label><br>
                <select name="warehouse_names[]" class="warehouse-dropdown" required>${options}</select><br>
                <label>Distance to Warehouse (in km):</label><br>
                <input type="number" name="distances[]" required><br>
                <button type="button" class="rmv-btn"
                onclick="this.parentElement.remove(); updateWarehouseDropdowns();">Remove</button><br>
            `;
               container.appendChild(newField);

               const newDropdown = newField.querySelector('.warehouse-dropdown');
               newDropdown.addEventListener('change', updateWarehouseDropdowns);

               updateWarehouseDropdowns();

          }

          function updateWarehouseDropdowns() {
               const dropdowns = document.querySelectorAll('.warehouse-dropdown');
               const selectedValues = Array.from(dropdowns).map(d => d.value);

               dropdowns.forEach(currentDropdown => {
                    const currentValue = currentDropdown.value;
                    const options = currentDropdown.querySelectorAll('option');

                    options.forEach(option => {
                         if (option.value === "") return;
                         if (
                              selectedValues.includes(option.value) &&
                              option.value !== currentValue
                         ) {
                              option.style.display = 'none';
                         } else {
                              option.style.display = 'block';
                         }
                    });
               });
          }

          document.addEventListener('DOMContentLoaded', () => {
               updateWarehouseDropdowns();
               document.querySelectorAll('.warehouse-dropdown').forEach(dropdown => {
                    dropdown.addEventListener('change', updateWarehouseDropdowns);
               });
          });
     </script>
</head>

<body>
     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-warehouse"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Add Wholesaler</h2>
               </a>
          </div>

          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" required><br><br>

                    <label>Latitude</label><br>
                    <input type="text" name="latitude" required
                         placeholder="Use this format: 12.123456 → number with 6 digits after the decimal."><br><br>

                    <label>Longitude</label><br>
                    <input type="text" name="longitude"
                         placeholder="Use this format: 12.123456 → number with 6 digits after the decimal."
                         required><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" required><br><br>

                    <label>Address</label><br>
                    <textarea name="address" cols="100" rows="5"></textarea><br><br>

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
                         <label>Warehouse Name:</label><br>
                         <select name="warehouse_names[]" id="warehouseOptions" class="warehouse-dropdown" required>
                              <option value="">-- Select Warehouse --</option>
                         </select><br><br>

                         <label>Distance to Warehouse (in km):</label><br>
                         <input type="number" name="distances[]" required><br><br>
                    </div>

                    <button type="button" style="font-size:15px; width: 200px; margin-left: 10px; margin-top: 0px;"
                         onclick="addWarehouseField()">Add More Warehouses</button><br><br>

                    <div class="form-btn">
                         <button type="submit">Add wholesaler</button>
                    </div>
               </form>
          </div>
     </div>
</body>

</html>