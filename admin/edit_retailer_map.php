<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include("../includes/encryption.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('Session expired, please log in again.'); window.location.href='index.php';</script>";
     exit();
}

if (!isset($_GET['id'])) {
     echo "Invalid request.";
     exit();
}

$encrypted_id = $_GET['id'];
$id = decrypt_id($encrypted_id);

$select_query = "SELECT * FROM wholesale_retailer_map WHERE id = $1";
$select_result = pg_query_params($master_conn, $select_query, [$id]);

if (!$select_result || pg_num_rows($select_result) == 0) {
     echo "Mapping not found.";
     exit();
}

$row = pg_fetch_assoc($select_result);
$district_id = $row['district_id'];

$wholesaler_result = pg_query_params($master_conn, "SELECT name FROM wholesalers WHERE district_id = $1", [$district_id]);
$retailer_result = pg_query_params($master_conn, "SELECT name FROM retailers WHERE district_id = $1", [$district_id]);

$selected_retailer = $_POST['retailer_name'] ?? $row['retailer_name'];
$retailerDetails = [];

if ($selected_retailer) {
     $retailerDetailsQuery = pg_query_params($master_conn, "SELECT * FROM retailers WHERE name = $1 AND district_id = $2", [$selected_retailer, $district_id]);
     if ($retailerDetailsQuery && pg_num_rows($retailerDetailsQuery) > 0) {
          $retailerDetails = pg_fetch_assoc($retailerDetailsQuery);
     }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
     $wholesaler_name = $_POST['wholesaler_name'];
     $retailer_name = $_POST['retailer_name'];
     $transport_rate = $_POST['transport_rate'];
     $distance = $_POST['distance'];

     $update_query = "UPDATE wholesale_retailer_map 
                      SET wholesaler_name = $1, retailer_name = $2, transport_rate = $3, distance = $4 
                      WHERE id = $5";
     $update_result = pg_query_params($master_conn, $update_query, [
          $wholesaler_name,
          $retailer_name,
          $transport_rate,
          $distance,
          $id
     ]);

     if ($update_result) {
          echo "<script>alert('Mapping updated successfully'); window.location.href='retailer_map.php';</script>";
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
     <title>Edit Retailer-Wholesaler Mapping</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
     <?php include("../includes/process.php"); ?>
     <?php include("../includes/sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h2 class="slash">/</h2>
               <h2>Edit Mapped Retailers</h2>
          </div>

          <div class="form">
               <form method="POST" id="editForm">
                    <label>Wholesaler Name</label><br>
                    <select name="wholesaler_name" required>
                         <option value="">-- Select Wholesaler --</option>
                         <?php
                         pg_result_seek($wholesaler_result, 0);
                         while ($wh = pg_fetch_assoc($wholesaler_result)) {
                              $selected = ($wh['name'] === $row['wholesaler_name']) ? 'selected' : '';
                              echo "<option value=\"" . htmlspecialchars($wh['name']) . "\" $selected>" . htmlspecialchars($wh['name']) . "</option>";
                         }
                         ?>
                    </select><br><br>

                    <label>Retailer Name</label><br>
                    <select name="retailer_name"  required>
                         <option value="">-- Select Retailer --</option>
                         <?php
                         pg_result_seek($retailer_result, 0);
                         while ($ret = pg_fetch_assoc($retailer_result)) {
                              $selected = ($ret['name'] === $selected_retailer) ? 'selected' : '';
                              echo "<option value=\"" . htmlspecialchars($ret['name']) . "\" $selected>" . htmlspecialchars($ret['name']) . "</option>";
                         }
                         ?>
                    </select><br><br>

                    <label>Distance (KM)</label><br>
                    <input type="number" name="distance" step="0.01"
                         value="<?php echo htmlspecialchars($retailerDetails['nearest_wholesaler_distance'] ?? $row['distance']); ?>"
                         required readonly><br><br>

                    <label>Transport Rate (₹)</label><br>
                    <input type="number" name="transport_rate"
                         value="<?php echo htmlspecialchars($row['transport_rate']); ?>" required><br><br>

                    <div class="form-btn">
                         <button type="submit" name="update">Update Mapping</button>
                    </div>
               </form>
          </div>
     </div>
</body>

</html>
