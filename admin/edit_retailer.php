<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include("../includes/encryption.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
     exit();
}

$retailer_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$encrypted_id = $_GET['id'] ?? null;
$retailer_id = decrypt_id($encrypted_id);

if (!$retailer_id) {
     echo "<script>alert('Retailer ID is required.'); </script>";
     exit();
}

$fetch_query = "SELECT * FROM retailers WHERE serial_no = $1";
$fetch_result = pg_query_params($master_conn, $fetch_query, [$retailer_id]);


if (!$fetch_result || pg_num_rows($fetch_result) === 0) {
     echo "<script>alert('Retailer not found.'); window.location.href='dashboard.php';</script>";
     exit();
}

$retailer = pg_fetch_assoc($fetch_result);


$district_query = "SELECT * FROM district";
$district_result = pg_query($fsms_conn, $district_query);

$wh_query = "SELECT serial_no, name FROM wholesalers";
$wh_result = pg_query($master_conn, $wh_query);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $name = $_POST['name'];
     $latitude = $_POST['latitude'];
     $longitude = $_POST['longitude'];
     $location = $_POST['location'];
     $address = $_POST['address'];
     $nearest_wholesaler_distance = $_POST['nearest_wholesaler_distance'];
     // $district_id = (int) $_POST['district_id'];
     $district_name = $_POST['district_name'];
     $nearest_wholesaler_id = (int) $_POST['nearest_wholesaler_id'];

     $wh_query = "SELECT name FROM wholesalers WHERE serial_no = $1";
     $wh_result_name = pg_query_params($master_conn, $wh_query, [$nearest_wholesaler_id]);

     if ($wh_result_name && pg_num_rows($wh_result_name) > 0) {
          $wh_data = pg_fetch_assoc($wh_result_name);
          $nearest_wholesaler_name = $wh_data['name'];
     } else {
          echo "<script>alert('Invalid wholesaler selected');</script>";
          exit();
     }


     $district_query = "SELECT id FROM district WHERE name = $1";
     $district_result_check = pg_query_params($fsms_conn, $district_query, [$district_name]);

     if ($district_result_check && pg_num_rows($district_result_check) > 0) {
          $district = pg_fetch_assoc($district_result_check);
          $district_id = $district['id'];
     } else {
          echo "<p style='color: red;'>Error: Invalid district selected.</p>";
          exit();
     }


     $update_query = "UPDATE retailers SET 
        name = $1,
        latitude = $2,
        longitude = $3,
        location = $4,
        address = $5,
        nearest_wholesaler_distance = $6,
        district_id = $7,
        district_name = $8,
        nearest_wholesaler_id = $9,
        nearest_wholesaler_name = $10
        WHERE serial_no = $11";

     $result = pg_query_params($master_conn, $update_query, [
          $name,
          $latitude,
          $longitude,
          $location,
          $address,
          $nearest_wholesaler_distance,
          $district_id,
          $district_name,
          $nearest_wholesaler_id,
          $nearest_wholesaler_name,
          $retailer_id
     ]);

     if ($result) {
          echo "<script>alert('Retailer updated successfully.'); window.location.href='retailers.php';</script>";
     } else {
          echo "<p style='color: red;'>Error: " . pg_last_error($master_conn) . "</p>";
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Update Retailer</title>
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
               <h2>Update Retailer</h2>
          </div>

          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" value="<?= htmlspecialchars($retailer['name']) ?>" required><br><br>

                    <label>Latitude</label><br>
                    <input type="text" name="latitude" value="<?= htmlspecialchars($retailer['latitude']) ?>"
                         required><br><br>

                    <label>Longitude</label><br>
                    <input type="text" name="longitude" value="<?= htmlspecialchars($retailer['longitude']) ?>"
                         required><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" value="<?= htmlspecialchars($retailer['location']) ?>"
                         required><br><br>

                    <label>Address</label><br>
                    <textarea name="address" rows="5" cols="100"
                         required><?= htmlspecialchars($retailer['address']) ?></textarea><br><br>

                    <!-- <label>Select District</label><br>
                         <select name="district_id" required>
                              <option value="">-- Select District --</option>
                                    <option value="">-- Select District --</option>
                         <?php
                         if ($district_result_check) {
                              while ($district = pg_fetch_assoc($district_result_check)) {
                                   $selected = $district['id'] == $retailer['district_id'] ? 'selected' : '';
                                   echo "<option value=\"" . htmlspecialchars($district['id']) . "\" $selected>" . htmlspecialchars($district['name']) . "</option>";
                              }
                         }
                         ?>
                    </select><br><br> -->

                    <label>District</label><br>
                    <select name="district_name" required>
                         <option value="">-- Select District --</option>
                         <?php
                         $districts = [
                              "Baksa",
                              "Barpeta",
                              "Biswanath",
                              "Bongaigaon",
                              "Cachar",
                              "Charaideo",
                              "Chirang",
                              "Darrang",
                              "Dhemaji",
                              "Dhubri",
                              "Dibrugarh",
                              "Goalpara",
                              "Golaghat",
                              "Hailakandi",
                              "Hojai",
                              "Jorhat",
                              "Kamrup",
                              "Kamrup Metropolitan",
                              "Karbi Anglong",
                              "Karimganj",
                              "Kokrajhar",
                              "Lakhimpur",
                              "Majuli",
                              "Morigaon",
                              "Nagaon",
                              "Nalbari",
                              "Dima Hasao",
                              "Sivasagar",
                              "Sonitpur",
                              "South Salmara-Mankachar",
                              "Tinsukia",
                              "Udalguri",
                              "West Karbi Anglong"
                         ];

                         foreach ($districts as $district) {
                              $selected = ($retailer['district_name'] == $district) ? "selected" : "";
                              echo "<option value=\"$district\" $selected>$district</option>";
                         }
                         ?>
                    </select><br><br>

                    <label>Select Nearest Wholesaler</label><br>
                    <select name="nearest_wholesaler_id" required>
                         <option value="">-- Select Wholesaler --</option>
                         <?php
                         if ($wh_result) {
                              while ($wh = pg_fetch_assoc($wh_result)) {
                                   $selected = $wh['serial_no'] == $retailer['nearest_wholesaler_id'] ? 'selected' : '';
                                   echo "<option value=\"" . htmlspecialchars($wh['serial_no']) . "\" $selected>" . htmlspecialchars($wh['name']) . "</option>";
                              }
                         }
                         ?>
                    </select><br><br>


                    <label>Nearest Wholesaler Distance</label><br>
                    <input type="number" name="nearest_wholesaler_distance"
                         value="<?= htmlspecialchars($retailer['nearest_wholesaler_distance']) ?>" required><br><br>

                    <div class="form-btn">
                         <button type="submit">Update Retailer</button>
                    </div>

               </form>
          </div>
     </div>

</body>

</html>