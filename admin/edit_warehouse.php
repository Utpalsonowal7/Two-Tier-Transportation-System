<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include("../includes/encryption.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
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
          // $district_id = $_POST['district_id'];
          $district_name = $_POST['district_name'];

          

          $updateQuery = "UPDATE warehouse SET name=$1, latitude=$2, longitude=$3, location=$4, address=$5, district_name=$6 WHERE serial_no=$7";
          $updateResult = pg_query_params($master_conn, $updateQuery, [$name, $latitude, $longitude, $location, $address,  $district_name, $id]);

          if ($updateResult) {
               echo "<script>alert('Warehouse updated successfully'); window.location.href='warehouses.php';</script>";
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
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Edit Warehouse</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
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
               <a href="#">
                    <h2>Edit Warehouse</h2>
               </a>
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
                              $selected = ($row['district_name'] == $district) ? "selected" : "";
                              echo "<option value=\"$district\" $selected>$district</option>";
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