<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include('../includes/encryption.php');

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}


$id = $_GET['id'] ?? null;
$encrypted_id = $_GET['id'];
$id = decrypt_id($encrypted_id);


if ($id) {
     $query = "SELECT * FROM warehouse WHERE serial_no = $1";
     $result = pg_query_params($master_conn, $query, [$id]);
     $row = pg_fetch_assoc($result);

     if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $name = $_POST['name'];
          $latitude = $_POST['latitude'];
          $longitude = $_POST['longitude'];
          $location = $_POST['location'];
          $address = $_POST['address'];
          $district_id = $_POST['district_id'];

          $updateQuery = "UPDATE warehouse SET name=$1, latitude=$2, longitude=$3, location=$4, address=$5, district_id=$6 WHERE serial_no=$7";
          $updateResult = pg_query_params($master_conn, $updateQuery, [$name, $latitude, $longitude, $location, $address, $district_id, $id]);

          if ($updateResult) {
               echo "<script>alert('Warehouse updated successfully');</script>";
          } else {
               echo "<script>alert('Error updating warehouse');</script>";
          }
     }
} else {
     echo "No warehouse selected";
     exit;
}

$districtsQuery = "SELECT * FROM district";
$districtsResult = pg_query($fsms_conn, $districtsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <meta name="viewport" content="width=device-width, initial-scale=1.0
     <title>Edit Warehouse</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

     <?php include('../includes/dist_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h2 class="slash">/</h2>
               <h2>Edit Warehouse</h2>
          </div>


          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" required value="<?= htmlspecialchars($row['name']) ?>"><br><br>

                    <label>Latitude</label><br>
                    <input type="text" name="latitude" required
                         value="<?= htmlspecialchars($row['latitude']) ?>"><br><br>

                    <label>Longitude</label><br>
                    <input type="text" name="longitude" required
                         value="<?= htmlspecialchars($row['longitude']) ?>"><br><br>

                    <label>Location</label><br>
                    <input type="text" name="location" required
                         value="<?= htmlspecialchars($row['location']) ?>"><br><br>

                    <label>Address</label><br>
                    <textarea name="address" cols="100"
                         rows="3"><?= htmlspecialchars($row['address']) ?></textarea><br><br>

                    <label>District</label><br>
                    <select name="district_id" required>
                         <option value="">-- Select District --</option>
                         <?php
                         while ($district = pg_fetch_assoc($districtsResult)) {
                              $selected = $row['district_id'] == $district['id'] ? "selected" : "";
                              echo "<option value=\"{$district['id']}\" $selected>{$district['name']}</option>";
                         }
                         ?>
                    </select><br><br>

                    <div class="form-btn">
                         <button type="submit">Update Warehouse</button>
                    </div>
               </form>
          </div>
     </div>

</body>

</html>