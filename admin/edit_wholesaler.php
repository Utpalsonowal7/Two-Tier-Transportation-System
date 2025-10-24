<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include("../includes/encryption.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
     exit();
}

$encrypted_id = $_GET['id'] ?? null;
$id = decrypt_id($encrypted_id);

if (!$id) {
     echo "<script>alert('No wholesaler selected'); window.location.href='wholesalers.php';</script>";
     exit();
}

$query = "SELECT * FROM wholesalers WHERE serial_no = $1";
$result = pg_query_params($master_conn, $query, [$id]);
$row = pg_fetch_assoc($result);

$district_result = pg_query($fsms_conn, "SELECT * FROM district");


$all_warehouses = [];
$wh_query = pg_query($master_conn, "SELECT serial_no, name FROM warehouse");
while ($wh = pg_fetch_assoc($wh_query)) {
     $all_warehouses[] = $wh;
}

$selected_warehouse_ids = array_map('trim', explode(',', trim($row['warehouse_ids'], '{}')));
$selected_distances = array_map('trim', explode(',', trim($row['distances'], '{}')));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $name = $_POST['name'];
     $latitude = $_POST['latitude'];
     $longitude = $_POST['longitude'];
     $location = $_POST['location'];
     $address = $_POST['address'];
     $district_id = $_POST['district_id'];

     $warehouse_ids = $_POST['warehouse_names'] ?? [];
     $distances = $_POST['distances'] ?? [];

     if (empty($warehouse_ids) || empty($distances)) {
          echo "<script>alert('At least one warehouse and distance must be added.'); window.location.href='edit_wholesaler.php?id=" . $encrypted_id . "';</script>";
          exit();
     }

     if (count($warehouse_ids) !== count(array_unique($warehouse_ids))) {
          echo "<script>alert('Duplicate warehouse selected. Please select unique ones.'); window.location.href='edit_wholesaler.php?id=" . $encrypted_id . "';</script>";
          exit();
     }

     $distances = array_map(function ($d) {
          return number_format((float) trim($d), 2, '.', '');
     }, $distances);

     $district_result_check = pg_query_params($fsms_conn, "SELECT name FROM district WHERE id = $1", [$district_id]);
     if ($district_result_check && pg_num_rows($district_result_check) > 0) {
          $district = pg_fetch_assoc($district_result_check);
          $district_name = $district['name'];
     } else {
          echo "<p style='color:red;'>Invalid district.</p>";
          exit();
     }

     $warehouse_names = [];
     foreach ($warehouse_ids as $warehouse_id) {
          foreach ($all_warehouses as $wh) {
               if ($wh['serial_no'] == $warehouse_id) {
                    $warehouse_names[] = $wh['name'];
                    break;
               }
          }
     }

     foreach ($warehouse_ids as $i => $wid) {
          $distance = trim($distances[$i] ?? '');

          if (empty($wid) || $wid == '' || $wid === '0') {
               echo "<script>alert('One or more warehouse names are missing. Please select valid warehouses.'); window.location.href='edit_wholesaler.php?id=" . $encrypted_id . "';</script>";
               exit();
          }

          if ($distance === '' || !is_numeric($distance) || $distance <= 0) {
               echo "<script>alert('One or more distances are missing or invalid. Please enter valid distance values.'); window.location.href='edit_wholesaler.php?id=" . $encrypted_id . "';</script>";
               exit();
          }
     }

     $warehouse_names_array = "{" . implode(",", array_map(function ($item) {
          return '"' . addslashes($item) . '"';
     }, $warehouse_names)) . "}";
     $warehouse_ids_array = "{" . implode(",", $warehouse_ids) . "}";
     $distances_array = "{" . implode(",", $distances) . "}";

     $update_query = "UPDATE wholesalers SET name=$1, latitude=$2, longitude=$3, location=$4, address=$5, district_id=$6, district_name=$7, warehouse_ids=$8, warehouse_names=$9, distances=$10 WHERE serial_no=$11";
     $update_result = pg_query_params($master_conn, $update_query, [
          $name,
          $latitude,
          $longitude,
          $location,
          $address,
          $district_id,
          $district_name,
          $warehouse_ids_array,
          $warehouse_names_array,
          $distances_array,
          $id
     ]);

     if ($update_result) {
          echo "<script>alert('Wholesaler updated successfully'); window.location.href='wholesalers.php'</script>";
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
     <title>Edit Wholesaler</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <!-- <script>
          function addWarehouseField() {
               const container = document.getElementById("warehouseContainer");
               const options = document.getElementById("warehouseOptions").innerHTML;
               const newField = document.createElement("div");
               newField.innerHTML = `
                <label>Warehouse Name:</label><br>
                <select name="warehouse_names[]" required>${options}</select><br><br>
                <label>Distance to Warehouse (in km):</label><br>
                <input type="number" step="0.01" name="distances[]" required><br><br>
            `;
               container.appendChild(newField);
          }
     </script> -->
</head>

<body>
     <?php include("../includes/process.php"); ?>
     <?php include("../includes/sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>

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
                    <input type="text" name="latitude" value="<?= htmlspecialchars($row['latitude']) ?>"
                         required><br><br>

                    <label>Longitude</label><br>
                    <input type="text" name="longitude" value="<?= htmlspecialchars($row['longitude']) ?>"
                         required><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" value="<?= htmlspecialchars($row['location']) ?>"
                         required><br><br>

                    <label>Address</label><br>
                    <textarea name="address" cols="100"
                         rows="5"><?= htmlspecialchars($row['address']) ?></textarea><br><br>

                    <label>District</label><br>
                    <select name="district_id" required>
                         <option value="">-- Select District --</option>
                         <?php
                         $district_query = pg_query($fsms_conn, "SELECT id, name FROM district");
                         while ($district = pg_fetch_assoc($district_query)) {
                              $selected = ($district['id'] == $row['district_id']) ? 'selected' : '';
                              echo "<option value='{$district['id']}' $selected>" . htmlspecialchars($district['name']) . "</option>";
                         }
                         ?>
                    </select><br><br>

                    <div id="warehouseContainer">
                    <?php foreach ($selected_warehouse_ids as $index => $wh_id): ?>
                         <div class="warehouse-entry">
                              <label>Warehouse Name:</label><br>
                              <select name="warehouse_names[]" class="warehouse-dropdown" required>
                                   <option value="">-- Select Warehouse --</option>
                                   <?php foreach ($all_warehouses as $wh): ?>
                                        <option value="<?= $wh['serial_no'] ?>" <?= $wh['serial_no'] == $wh_id ? 'selected' : '' ?>>
                                             <?= htmlspecialchars($wh['name']) ?>
                                        </option>
                                   <?php endforeach; ?>
                              </select>
                              <label>Distance to Warehouse (in km):</label><br>
                              <input type="number" step="0.01" name="distances[]" required
                                   value="<?= htmlspecialchars($selected_distances[$index] ?? '') ?>">

                              <button type="button" class="rmv-btn" onclick="removeWarehouseField(this)">Remove</button>
                         </div>
                    <?php endforeach; ?>
                    </div>

                    <button type="button" onclick="addWarehouseField()">Add More Warehouses</button><br>

                    <div class="form-btn">
                         <button type="submit">Update</button>
                    </div>
               </form>
          </div>
     </div>


     <div id="warehouseOptions" style="display:none;">
          <option value="">-- Select Warehouse --</option>
          <?php foreach ($all_warehouses as $wh): ?>
               <option value="<?= htmlspecialchars($wh['serial_no']) ?>"><?= htmlspecialchars($wh['name']) ?></option>
          <?php endforeach; ?>
     </div>

     <script>
          function updateWarehouseDropdowns() {
               const dropdowns = document.querySelectorAll('.warehouse-dropdown');
               const selectedValues = Array.from(dropdowns).map(d => d.value);

               dropdowns.forEach(currentDropdown => {
                    const currentValue = currentDropdown.value;
                    const options = currentDropdown.querySelectorAll('option');

                    options.forEach(option => {
                         if (option.value === "") return; // Skip default option
                         if (
                              selectedValues.includes(option.value) &&
                              option.value !== currentValue
                         ) {
                              option.style.display = 'none'; // Hide if selected in another dropdown
                         } else {
                              option.style.display = 'block'; // Show if not selected
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

          function addWarehouseField() {
               const container = document.getElementById("warehouseContainer");
               const options = document.getElementById("warehouseOptions").innerHTML;
               const newField = document.createElement("div");
               newField.classList.add("warehouse-entry");

               newField.innerHTML = `
        <label>Warehouse Name:</label><br>
        <select name="warehouse_names[]" class="warehouse-dropdown" required>
            ${options}
        </select><br>
        <label>Distance to Warehouse (in km):</label>
        <input type="number" step="0.01" name="distances[]" required><br>
        <button type="button" class="rmv-btn" onclick="removeWarehouseField(this)">Remove</button><br>
    `;

               container.appendChild(newField);

               newField.querySelector('.warehouse-dropdown').addEventListener('change', updateWarehouseDropdowns);
               updateWarehouseDropdowns();
          }

          function removeWarehouseField(button) {
               const entry = button.closest(".warehouse-entry");
               entry.remove();
               updateWarehouseDropdowns();
          }

     </script>

</body>

</html>