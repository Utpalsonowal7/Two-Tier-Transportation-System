<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include('../includes/encryption.php');

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('Session expired, please log in again!');window.location.href='login.php';</script>";
     exit();
}

$encrypted_id = $_GET['id'] ?? null;
$id = decrypt_id($encrypted_id);

if (!$id) {
     echo "<script>alert('No wholesaler selected');window.location.href='wholesalers.php';</script>";
     exit();
}

$query = "SELECT * FROM wholesalers WHERE serial_no = $1";
$result = pg_query_params($master_conn, $query, [$id]);
$row = pg_fetch_assoc($result);

if (!$row) {
     echo "<script>alert('Wholesaler not found');window.location.href='wholesalers.php';</script>";
     exit();
}

$district_result = pg_query($fsms_conn, "SELECT * FROM district");


$selected_warehouse_ids = explode(',', trim($row['warehouse_ids'], '{}'));
$selected_distances = explode(',', trim($row['distances'], '{}'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $name = $_POST['name'];
     $latitude = $_POST['latitude'];
     $longitude = $_POST['longitude'];
     $location = $_POST['location'];
     $address = $_POST['address'];
     $district_id = $_POST['district_id'];

     $warehouse_ids = $_POST['warehouse_names'] ?? [];
     $distances = $_POST['distances'] ?? [];

     if(empty($name) || empty($latitude) || empty($longitude) || empty($location) || empty($address) || empty($district_id) || empty($warehouse_ids) || empty($distances)) {
          echo "<script>alert('All fields are required!'); window.location.href='edit_wholesaler.php?id=" . htmlspecialchars($encrypted_id) . "';</script>";
          exit();
     }

     $warehouse_names = [];
     foreach ($warehouse_ids as $warehouse_id) {
          $wh_query = "SELECT name FROM warehouse WHERE serial_no = $1";
          $wh_result = pg_query_params($master_conn, $wh_query, [$warehouse_id]);
          if ($wh_result && pg_num_rows($wh_result) > 0) {
               $wh = pg_fetch_assoc($wh_result);
               $warehouse_names[] = $wh['name'];
          }
     }

     $warehouse_names_array = "{" . implode(",", array_map(function ($item) {
          return '"' . addslashes($item) . '"';
     }, $warehouse_names)) . "}";
     $distances_array = "{" . implode(",", $distances) . "}";
     $warehouse_ids_array = "{" . implode(",", $warehouse_ids) . "}";

     $update_query = "UPDATE wholesalers SET name=$1, latitude=$2, longitude=$3, location=$4, address=$5, district_id=$6, warehouse_ids=$7, warehouse_names=$8, distances=$9 WHERE serial_no=$10";
     $update_result = pg_query_params($master_conn, $update_query, [
          $name,
          $latitude,
          $longitude,
          $location,
          $address,
          $district_id,
          $warehouse_ids_array,
          $warehouse_names_array,
          $distances_array,
          $id
     ]);

     if ($update_result) {
          echo "<script>alert('Wholesaler updated successfully');window.location.href='wholesalers.php';</script>";
          exit();
     } else {
          echo "<p style='color:red;'>Error: " . pg_last_error($master_conn) . "</p>";
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Edit Wholesaler</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

     <style>
          .modal {
               display: none;
               position: fixed;
               z-index: 1000;
               left: 0;
               top: 0;
               width: 100%;
               height: 100%;
               background-color: rgba(0, 0, 0, 0.5);
          }

          .modal-content {
               background-color: #fff;
               margin: 15% auto;
               padding: 20px;
               width: 400px;
               font-size: 18px;
               border-radius: 8px;
               text-align: center;
               box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
          }

          .modal-content button {
               margin-top: 15px;
               padding: 8px 20px;
               background-color: #0175e4;
               color: #fff;
               border: none;
               border-radius: 5px;
               cursor: pointer;
          }
     </style>

     <script>
     // AJAX for loading Warehouse
     function loadWarehouses(districtId, callback) {
          if (districtId) {
               $.ajax({
                    url: 'fetch_warehouse.php',
                    method: 'POST',
                    data: { district_id: districtId },
                    success: function (response) {
                         $('#warehouseOptions').html(response);
                         if (typeof callback === 'function') {
                              callback();
                         }
                    }
               });
          } else {
               $('#warehouseOptions').html('<option value="">-- Select Warehouse --</option>');
          }
     }

     // Add more warehouse option
     function addWarehouseField(selectedId = '', distance = '') {
          const container = document.getElementById("warehouseContainer");
          const options = document.getElementById("warehouseOptions").innerHTML;
          const newField = document.createElement("div");

          newField.innerHTML = `
               <label>Warehouse Name:</label><br>
               <select name="warehouse_names[]" class="warehouse-dropdown" required>${options}</select><br>
               <label>Distance to Warehouse (in km):</label><br>
               <input type="number" name="distances[]" value="${distance}" min="0" required><br>
               <button type="button" class="rmv-btn"
                onclick="this.parentElement.remove(); updateWarehouseDropdowns();">Remove</button><br>
          `;

          container.appendChild(newField);

          const selects = container.querySelectorAll('select[name="warehouse_names[]"]');
          selects[selects.length - 1].value = selectedId;

          updateWarehouseDropdowns();
     }

     // only non adding warehouse to show only or hide already used warhouse
     function updateWarehouseDropdowns() {
          const dropdowns = document.querySelectorAll('.warehouse-dropdown');
          const selectedValues = Array.from(dropdowns).map(d => d.value);

          dropdowns.forEach(currentDropdown => {
               const currentValue = currentDropdown.value;
               const options = currentDropdown.querySelectorAll('option');

               options.forEach(option => {
                    if (option.value === "") return;
                    if (selectedValues.includes(option.value) && option.value !== currentValue) {
                         option.style.display = 'none';
                    } else {
                         option.style.display = 'block';
                    }
               });
          });
     }

     // Show modal when district is changed
     function showModal() {
          document.getElementById("districtModal").style.display = "block";
     }

     // Close modal
     function closeModal() {
          document.getElementById("districtModal").style.display = "none";
     }

     // Initial script
     document.addEventListener("DOMContentLoaded", function () {
          const districtSelect = document.querySelector('select[name="district_id"]');
          const selectedDistrictId = districtSelect.value;

          // Pre-load warehouse fields if a district is selected
          if (selectedDistrictId) {
               loadWarehouses(selectedDistrictId, function () {
                    const warehouseIds = <?= json_encode($selected_warehouse_ids) ?>;
                         const distances = <?= json_encode($selected_distances) ?>;

                         const container = document.getElementById("warehouseContainer");
                         container.innerHTML = "";

                         for (let i = 0; i < warehouseIds.length; i++) {
                              addWarehouseField(warehouseIds[i], distances[i] || '');
                         }
                    });
               }

               // When user changes district
               districtSelect.addEventListener("change", function () {
                    const districtId = this.value;
                    document.getElementById("warehouseContainer").innerHTML = "";
                    loadWarehouses(districtId);
                    showModal();
               });
          });
     </script>

</head>

<body>
     <?php include('../includes/dist_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
           <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h2 class="slash">/</h2>
               <h2>Edit Wholesaler</h2>
          </div>
          

          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required><br><br>

                    <label>Latitude</label><br>
                    <input type="number" step="0.000001" name="latitude"
                         value="<?= htmlspecialchars($row['latitude']) ?>" required><br><br>

                    <label>Longitude</label><br>
                    <input type="number" step="0.000001" name="longitude"
                         value="<?= htmlspecialchars($row['longitude']) ?>" required><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" value="<?= htmlspecialchars($row['location']) ?>"
                         required><br><br>

                    <label>Address</label><br>
                    <textarea name="address" cols="100"
                         rows="5"><?= htmlspecialchars($row['address']) ?></textarea><br><br>

                    <label>Select District</label><br>
                    <select name="district_id" required>
                         <option value="">-- Select District --</option>
                         <?php while ($district = pg_fetch_assoc($district_result)): ?>
                              <option value="<?= $district['id'] ?>" <?= $district['id'] == $row['district_id'] ? 'selected' : '' ?>>
                                   <?= htmlspecialchars($district['name']) ?>
                              </option>
                         <?php endwhile; ?>
                    </select><br><br>

                    <div id="warehouseContainer">
                      
                    </div>

                    <button type="button" onclick="addWarehouseField()" style="font-size: 15px; width: 200px;">Add More
                         Warehouses</button><br><br>

                    <div class="form-btn">
                         <button type="submit">Update</button>
                    </div>
               </form>
          </div>
     </div>

     <div id="warehouseOptions" style="display: none;">

     </div>


     <div id="districtModal" class="modal">
          <div class="modal-content">
               <p>District changed. Please click "Add More Warehouses" to select warehouses from the new district.
               </p>
               <button onclick="closeModal()">OK</button>
          </div>
     </div>

</body>

</html>