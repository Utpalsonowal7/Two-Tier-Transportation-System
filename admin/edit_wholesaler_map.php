<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include("../includes/encryption.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
     exit();
}

if (!isset($_GET['id'])) {
     echo "Invalid request.";
     exit();
}

$encrypted_id = $_GET['id'];
$id = decrypt_id($encrypted_id);

$select_query = "SELECT * FROM warehouse_wholesale_map WHERE id = $1";
$select_result = pg_query_params($master_conn, $select_query, [$id]);

if (!$select_result || pg_num_rows($select_result) == 0) {
     echo "Mapping not found.";
     exit();
}

$row = pg_fetch_assoc($select_result);

$warehouses_query = "SELECT * FROM warehouse";
$warehouses_result = pg_query($master_conn, $warehouses_query);

$wholesalers_query = "SELECT * FROM wholesalers";
$wholesalers_result = pg_query($master_conn, $wholesalers_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $warehouse_name = $_POST['warehouse_name'];
     $wholesaler_name = $_POST['wholesaler_name'];
     $transport_rate = $_POST['transport_rate'];
     $distance = $_POST['distance'];

     $checkQuery = pg_query_params(
          $master_conn,
          "SELECT 1 FROM warehouse_wholesale_map WHERE warehouse_name = $1 AND wholesaler_name = $2 AND transport_rate = $3 AND id != $4",
          [$warehouse_name, $wholesaler_name, $transport_rate, $id]
     );

     if (pg_num_rows($checkQuery) > 0) {
          echo "<script>alert('This mapping already exists.'); window.location.href='edit_wholesaler_map.php?id=" . htmlspecialchars($encrypted_id) . "';</script>";
          exit();
     }

     $update_query = "UPDATE warehouse_wholesale_map 
                     SET warehouse_name = $1, wholesaler_name = $2, transport_rate = $3, distance = $4 
                     WHERE id = $5";
     $update_result = pg_query_params($master_conn, $update_query, [
          $warehouse_name,
          $wholesaler_name,
          $transport_rate,
          $distance,
          $id
     ]);

     if ($update_result) {
          echo "<script>alert('Mapping updated successfully'); window.location.href='wholesale_map.php';</script>";
          exit();
     } else {
          echo "<p style='color:red;'>Update failed: " . pg_last_error($master_conn) . "</p>";
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <title>Edit Warehouse-Wholesaler Mapping</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <style>
          .info-banner {
               background: #eef9ff;
               padding: 12px 20px;
               margin-bottom: 15px;
               border-left: 4px solid #007BFF;
               font-size: 15px;
          }

          label {
               font-weight: 600;
               margin-top: 10px;
               display: inline-block;
          }

          input,
          select {
               padding: 8px;
               margin-bottom: 10px;
               width: 100%;
               border-radius: 4px;
               border: 1px solid #ccc;
          }

          .form-btn {
               margin-top: 20px;
          }

          #distanceStatus {
               font-size: 13px;
               color: gray;
          }
     </style>
</head>

<body>
     <?php include("../includes/process.php"); ?>
     <?php include("../includes/sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h2 class="slash">/</h2>
               <h2>Edit Mapped Wholesaler</h2>
          </div>

          <div class="form">
               <div class="info-banner">
                    <strong>Current Mapping:</strong> <?= htmlspecialchars($row['warehouse_name']) ?> ➝
                    <?= htmlspecialchars($row['wholesaler_name']) ?>
                    (<?= htmlspecialchars($row['distance']) ?> km @
                    ₹<?= htmlspecialchars($row['transport_rate']) ?>/unit)
               </div>

               <form method="POST">
                    <label>Wholesaler Name</label>
                    <select name="wholesaler_name" id="wholesalerSelect" required>
                         <option value="">-- Select Wholesaler --</option>
                         <?php
                         $selectedWholesaler = $row['wholesaler_name'];
                         while ($wh_row = pg_fetch_assoc($wholesalers_result)) {
                              $wholesalerName = htmlspecialchars($wh_row['name']);
                              $warehouseArray = json_decode(str_replace(['{', '}'], ['[', ']'], $wh_row['warehouse_names']), true);
                              $distanceArray = json_decode(str_replace(['{', '}'], ['[', ']'], $wh_row['distances']), true);
                              $selected = ($selectedWholesaler == $wh_row['name']) ? 'selected' : '';

                              echo "<option 
                value='$wholesalerName' 
                data-warehouses='" . json_encode($warehouseArray) . "' 
                data-distances='" . json_encode($distanceArray) . "' 
                $selected>$wholesalerName</option>";
                         }
                         ?>
                    </select>

                    <label>Warehouse Name</label>
                    <select name="warehouse_name" id="warehouseSelect" required>
                         <option value="">-- Select Warehouse --</option>
                    </select>

                    <label>Transport Rate (₹)</label>
                    <input type="number" name="transport_rate" min="1" max="150" step="0.01"
                         value="<?= htmlspecialchars($row['transport_rate']) ?>" required>

                    <label>Distance (km)</label>
                    <input type="number" name="distance" id="distanceInput" step="0.01" min="0"
                         value="<?= htmlspecialchars($row['distance']) ?>" required>
                    <small id="distanceStatus" style="color: gray;"></small>

                    <div class="form-btn">
                         <button type="submit">Update Mapping</button>
                    </div>
               </form>
          </div>
     </div>

     <script>
          const wholesalerSelect = document.getElementById('wholesalerSelect');
          const warehouseSelect = document.getElementById('warehouseSelect');
          const distanceInput = document.getElementById('distanceInput');
          const distanceStatus = document.getElementById('distanceStatus');

          wholesalerSelect.addEventListener('change', () => {
               const selectedOption = wholesalerSelect.options[wholesalerSelect.selectedIndex];
               const warehousesData = selectedOption.getAttribute('data-warehouses');
               const distancesData = selectedOption.getAttribute('data-distances');

               warehouseSelect.innerHTML = '<option value="">-- Select Warehouse --</option>';
               distanceInput.value = '';
               distanceStatus.textContent = '';

               if (!warehousesData || !distancesData) return;

               try {
                    const warehouses = JSON.parse(warehousesData);
                    const distances = JSON.parse(distancesData);

                    warehouses.forEach((warehouse, i) => {
                         const dist = distances[i] || '';
                         const option = document.createElement('option');
                         option.value = warehouse;
                         option.textContent = `${warehouse} (${dist} km)`;
                         option.setAttribute('data-distance', dist);
                         if (warehouse.trim() === "<?= $row['warehouse_name'] ?>".trim()) {
                              option.selected = true;
                              distanceInput.value = dist;
                              // distanceStatus.textContent = 'Auto-filled based on wholesaler mapping.';
                         }
                         warehouseSelect.appendChild(option);
                    });
               } catch (err) {
                    console.error('Error parsing JSON:', err);
               }
          });

          warehouseSelect.addEventListener('change', () => {
               const selectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
               const distance = selectedOption.getAttribute('data-distance') || '';
               distanceInput.value = distance;
               distanceStatus.textContent = distance ? '' : 'No distance found.';
          });

          window.addEventListener('DOMContentLoaded', () => {
               wholesalerSelect.dispatchEvent(new Event('change'));
          });
     </script>

</body>

</html>