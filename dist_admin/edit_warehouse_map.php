<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include('../includes/encryption.php');

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expired, please log in again!');window.location.href='login.php';</script>";
     exit();
}

if (!isset($_GET['id'])) {
     echo "Invalid request.";
     exit();
}

$encrypted_id = $_GET['id'];
$id = decrypt_id($encrypted_id);

$admin_id = $_SESSION['adminid'];
$district_query = "SELECT district_id FROM district_admins WHERE id = $1";
$district_result = pg_query_params($fsms_conn, $district_query, [$admin_id]);
$district_row = pg_fetch_assoc($district_result);
$district_id = $district_row['district_id'];


$select_query = "SELECT * FROM warehouse_wholesale_map WHERE id = $1";
$select_result = pg_query_params($master_conn, $select_query, [$id]);

if (!$select_result || pg_num_rows($select_result) == 0) {
     echo "Mapping not found.";
     exit();
}

$row = pg_fetch_assoc($select_result);
$wholesaler_query = pg_query_params($master_conn, "SELECT serial_no, name, warehouse_names, distances FROM wholesalers where district_id = $1 ORDER BY name",[$district_id]);

$wh_data_js = [];
pg_result_seek($wholesaler_query, 0);
while ($wh = pg_fetch_assoc($wholesaler_query)) {
     $warehouses = preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/', $wh['warehouse_names']);
     $distances = explode(',', $wh['distances']);
     $cleaned = [];
     foreach ($warehouses as $i => $w) {
          $w = trim(preg_replace('/^{?"?|"?}?$/', '', $w));
          $d = trim(preg_replace('/[{}"]/', '', $distances[$i] ?? ''));
          if ($w)
               $cleaned[] = ["name" => $w, "distance" => $d];
     }
     $wh_data_js[$wh['name']] = $cleaned;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $warehouse_name = $_POST['warehouse_name'];
     $wholesaler_name = $_POST['wholesaler_name'];
     $transport_rate = $_POST['transport_rate'];
     $distance = $_POST['distance'];

     $update_query = "UPDATE warehouse_wholesale_map SET warehouse_name = $1, wholesaler_name = $2, transport_rate = $3, distance = $4 WHERE id = $5";
     $update_result = pg_query_params($master_conn, $update_query, [$warehouse_name, $wholesaler_name, $transport_rate, $distance, $id]);

     if ($update_result) {
          echo "<script>alert('Mapping updated successfully'); window.location.href='dist_dashboard.php';</script>";
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
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Edit Mapping</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <script>
          const wholesalerData = <?php echo json_encode($wh_data_js); ?>;
     </script>
</head>

<body>
     <?php include("../includes/dist_sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h2 class="slash">/</h2>
               <h2>Edit Mapped Wholesaler</h2>
          </div>

          <div class="form">
               <form method="POST">
                    <label>Select Wholesaler</label><br>
                    <select name="wholesaler_name" id="wholesalerDropdown" required>
                         <option value="">-- Select Wholesaler --</option>
                         <?php
                         pg_result_seek($wholesaler_query, 0);
                         while ($wrow = pg_fetch_assoc($wholesaler_query)) { ?>
                              <option value="<?php echo htmlspecialchars($wrow['name']); ?>" <?php echo ($wrow['name'] == $row['wholesaler_name']) ? 'selected' : ''; ?>>
                                   <?php echo htmlspecialchars($wrow['name']); ?>
                              </option>
                         <?php } ?>
                    </select><br><br>

                    <label>Select Warehouse</label><br>
                    <select name="warehouse_name" id="warehouseDropdown" required>
                         <option value="">-- Select Warehouse --</option>
                         <?php
                         $selected_wh = $row['wholesaler_name'];
                         if (isset($wh_data_js[$selected_wh])) {
                              foreach ($wh_data_js[$selected_wh] as $item) {
                                   $selected = $item['name'] == $row['warehouse_name'] ? 'selected' : '';
                                   echo "<option value=\"" . htmlspecialchars($item['name']) . "\" data-distance=\"" . htmlspecialchars($item['distance']) . "\" $selected>" .
                                        htmlspecialchars($item['name'] . " - Distance: " . $item['distance']) .
                                        "</option>";
                              }
                         }
                         ?>
                    </select><br><br>

                    <label>Distance (km)</label><br>
                    <input type="text" name="distance" id="distanceInput"
                         value="<?php echo htmlspecialchars($row['distance']); ?>" readonly><br><br>


                    <label>Transport Rate (₹)</label><br>
                    <input type="number" name="transport_rate"
                         value="<?php echo htmlspecialchars($row['transport_rate']); ?>" required><br><br>

                    <div class="form-btn">
                         <button type="submit">Update Mapping</button>
                    </div>
               </form>
          </div>
     </div>

     <!-- <script>
          const wholesalerDropdown = document.getElementById('wholesalerDropdown');
          const warehouseDropdown = document.getElementById('warehouseDropdown');
          const dynamicFields = document.getElementById('dynamicFields');
          const staticFields = document.getElementById('staticFields');
          const distanceField = document.getElementById('dynamicDistance');

          wholesalerDropdown.addEventListener('change', function () {
               const selectedWh = this.value;
               if (selectedWh in wholesalerData) {
                    dynamicFields.style.display = 'block';
                    staticFields.style.display = 'none';
                    warehouseDropdown.innerHTML = '<option value="">-- Select Warehouse --</option>';
                    wholesalerData[selectedWh].forEach(item => {
                         const option = document.createElement('option');
                         option.value = item.name;
                         option.setAttribute('data-distance', item.distance);
                         option.textContent = `${item.name} - Distance: ${item.distance}`;
                         warehouseDropdown.appendChild(option);
                    });
               } else {
                    dynamicFields.style.display = 'none';
                    staticFields.style.display = 'block';
               }
          });

          warehouseDropdown.addEventListener('change', function () {
               const selected = this.options[this.selectedIndex];
               distanceField.value = selected.getAttribute('data-distance') || '';
          });
     </script> -->

     <script>
          const wholesalerDropdown = document.getElementById('wholesalerDropdown');
          const warehouseDropdown = document.getElementById('warehouseDropdown');
          const distanceInput = document.getElementById('distanceInput');

         
          wholesalerDropdown.addEventListener('change', function () {
               const selectedWh = this.value;
               warehouseDropdown.innerHTML = '<option value="">-- Select Warehouse --</option>';
               distanceInput.value = '';

               if (selectedWh in wholesalerData) {
                    wholesalerData[selectedWh].forEach(item => {
                         const option = document.createElement('option');
                         option.value = item.name;
                         option.textContent = `${item.name} - Distance: ${item.distance}`;
                         option.setAttribute('data-distance', item.distance);
                         warehouseDropdown.appendChild(option);
                    });
               }
          });

          
          warehouseDropdown.addEventListener('change', function () {
               const selected = this.options[this.selectedIndex];
               const dist = selected.getAttribute('data-distance');
               if (dist) {
                    distanceInput.value = dist;
               }
          });

     </script>

</body>

</html>