<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}

$adminId = $_SESSION['adminid'];

$adminQuery = pg_query_params($fsms_conn, "SELECT district_id FROM district_admins WHERE id = $1", [$adminId]);
$adminDistrictId = pg_fetch_result($adminQuery, 0, 'district_id');

$districtNameQuery = pg_query_params($fsms_conn, "SELECT name FROM district WHERE id = $1", [$adminDistrictId]);
$adminDistrictName = pg_fetch_result($districtNameQuery, 0, 'name');


if ($_GET['action'] ?? '' === 'get_wholesalers') {
     $warehouse = $_GET['warehouse'];
     $district_id = $_GET['district_id'];

     $query = pg_query($master_conn, "SELECT DISTINCT wholesaler_name, transport_rate, distance FROM warehouse_wholesale_map WHERE warehouse_name='$warehouse' AND district_id=$district_id");

     $wholesalers = [];
     while ($row = pg_fetch_assoc($query)) {
          $wholesalers[] = [
               'wholesaler_name' => $row['wholesaler_name'],
               'transport_rate' => $row['transport_rate'],
               'distance' => $row['distance']
          ];
     }

     if ($wholesalers) {
          echo json_encode($wholesalers);
     } else {
          echo json_encode(['error' => 'No wholesalers found']);
     }
     exit;
}

// $districtId = isset($_POST['district_id']) ? $_POST['district_id'] : null;

// if ($districtId) {
//      $warehouseQuery = pg_query($master_conn, "SELECT DISTINCT warehouse_name FROM warehouse_wholesale_map WHERE district_id = $districtId");
// }

$districtId = $adminDistrictId;
$warehouseQuery = pg_query_params($master_conn, "SELECT DISTINCT warehouse_name FROM warehouse_wholesale_map WHERE district_id = $1", [$districtId]);


$district_result = pg_query($fsms_conn, "SELECT * FROM district");

if (isset($_POST['submit'])) {
     $district_id = $_POST['district_id'];
     $warehouse_name = $_POST['warehouse_name'];
     $wholesaler_name = $_POST['wholesaler_name'];
     $commodity = $_POST['commodity'];
     $quantity = $_POST['quantity'];
     $rate = $_POST['rate'];
     $distance = $_POST['distance'];
     $area_type = $_POST['area_type'];

     if ($area_type == 'plain' && $rate > 135) {
          echo "<script>alert('Transport rate for plain area cannot exceed ₹135 per Quintal!');</script>";
     } elseif ($area_type == 'riverine' && $rate > 150) {
          echo "<script>alert('Transport rate for riverine area cannot exceed ₹150 per Quintal!');</script>";
     } else {
          $insertQuery = pg_query($master_conn, "INSERT INTO transport (warehouse_name, wholesaler_name, quantity, rate, distance, area_type, district_id, commodity) 
                                             VALUES ('$warehouse_name', '$wholesaler_name', $quantity, $rate, $distance, '$area_type', $district_id, '$commodity')");

          if ($insertQuery) {
               echo "<script>alert('Data submitted successfully!');</script>";
          } else {
               echo "<script>alert('Error: " . pg_last_error($master_conn) . "');</script>";
          }
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8" />
     <title>Commodity Transport Form</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <style>
          .radio-group label {
               font-weight: normal;
               display: inline-flex;
               align-items: center;
               gap: 4px;
               margin: 5px 0;
          }

          button {
               background: #007bff;
               color: white;
               padding: 12px 25px;
               border: none;
               border-radius: 6px;
               cursor: pointer;
               margin-top: 25px;
               width: 100%;
               font-size: 16px;
          }

          button:hover {
               background: #0056b3;
          }
     </style>
</head>

<body>
     <?php include('../includes/dist_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-truck"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Add Commodity Transport Details</h2>
               </a>
          </div>

          <div class="form">
               <form method="POST" onsubmit="return validateAndSubmit()">
                    <!-- <label for="district">Select District</label>
                    <select name="district_id" id="district" required onchange="this.form.submit()">
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
                    </select><br><br> -->

                    <label for="district">District</label>
                    <select id="district" disabled>
                         <option selected><?php echo htmlspecialchars($adminDistrictName); ?></option>
                    </select>
                    <input type="hidden" name="district_id" value="<?php echo htmlspecialchars($adminDistrictId); ?>">

                    <label>Select Warehouse:</label><br>
                    <select name="warehouse_name" id="warehouse" required onchange="fetchWholesalers()">
                         <option value="">-- Select Warehouse --</option>
                         <?php while ($row = pg_fetch_assoc($warehouseQuery)) { ?>
                              <option value="<?php echo htmlspecialchars($row['warehouse_name']); ?>">
                                   <?php echo htmlspecialchars($row['warehouse_name']); ?>
                              </option>
                         <?php } ?>
                    </select><br><br>

                    <label>Select Wholesaler:</label><br>
                    <select name="wholesaler_name" id="wholesaler" required onchange="updateRateAndDistance()">
                         <option value="">-- Select Wholesaler --</option>
                    </select><br><br>

                    <label for="commodity">Select Commodity:</label>
                    <select name="commodity" id="commodity" required>
                         <option value="">-- Choose Commodity --</option>
                         <option value="Rice">Rice</option>
                         <option value="Dal">Dal</option>
                         <option value="Sugar">Sugar</option>
                         <option value="Wheat">Wheat</option>
                         <option value="Others">Others</option>
                    </select><br><br>

                    <label for="quantity">Quantity (In Quintals):</label>
                    <input type="number" step="0.01" name="quantity" id="quantity" min="1" required><br><br>

                    <label for="rate">Transport Rate(Quintal/KM):</label>
                    <input type="number" step="0.01" name="rate" id="rate" min="1" required><br><br>

                    <label for="distance">Distance to cover (In Km):</label>
                    <input type="number" step="0.01" name="distance" id="distance" min="1" required><br><br>

                    <label>Area Type:</label>
                    <div class="radio-group">
                         <label><input type="radio" name="area_type" value="plain" required> Plain
                              (max=₹135/Quintal)</label>
                         <label><input type="radio" name="area_type" value="riverine"> Riverine
                              (max=₹150/Quintal)</label>
                    </div><br>

                    <div class="form-btn">
                         <button type="submit" name="submit">Submit</button>
                    </div>
               </form>
          </div>
     </div>

     <script>
          let wholesalersData = [];

          function fetchWholesalers() {
               const warehouse = document.getElementById("warehouse").value;
               const district = "<?php echo $adminDistrictId; ?>";

               if (warehouse && district) {
                    fetch(`?action=get_wholesalers&warehouse=${encodeURIComponent(warehouse)}&district_id=${district}`)
                         .then(res => res.json())
                         .then(data => {
                              wholesalersData = data;
                              const wholesalerSelect = document.getElementById("wholesaler");
                              wholesalerSelect.innerHTML = '<option value="">-- Select Wholesaler --</option>';

                              if (data.length > 0) {
                                   data.forEach(wholesaler => {
                                        const option = document.createElement("option");
                                        option.value = wholesaler.wholesaler_name;
                                        option.textContent = wholesaler.wholesaler_name;
                                        wholesalerSelect.appendChild(option);
                                   });
                              } else {
                                   alert('No wholesalers found for this warehouse!');
                              }
                         })
                         .catch(err => alert('Error fetching wholesalers: ' + err));
               }
          }

          function updateRateAndDistance() {
               const wholesalerName = document.getElementById("wholesaler").value;
               const wholesaler = wholesalersData.find(item => item.wholesaler_name === wholesalerName);

               if (wholesaler) {
                    document.getElementById("rate").value = wholesaler.transport_rate;
                    document.getElementById("distance").value = wholesaler.distance;
               }
          }

          function validateAndSubmit() {
               const rate = document.getElementById("rate").value;
               const areaType = document.querySelector('input[name="area_type"]:checked')?.value;

               if (areaType === 'plain' && rate > 135) {
                    alert('Transport rate for plain area cannot exceed ₹135 per Quintal!');
                    return false;
               }

               if (areaType === 'riverine' && rate > 150) {
                    alert('Transport rate for riverine area cannot exceed ₹150 per Quintal!');
                    return false;
               }

               return true;
          }
     </script>
</body>

</html>