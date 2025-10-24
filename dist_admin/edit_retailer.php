<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include('../includes/encryption.php');

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}


$retailer_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$encrypted_id = $_GET['id'];
$retailer_id = decrypt_id($encrypted_id);

if (!$retailer_id) {
     echo "<script>alert('Retailer ID is required.'); </script>";
     exit();
}

$fetch_query = "SELECT * FROM retailers WHERE serial_no = $1";
$fetch_result = pg_query_params($master_conn, $fetch_query, [$retailer_id]);

if (!$fetch_result || pg_num_rows($fetch_result) === 0) {
     echo "<script>alert('Retailer not found.'); window.location.href='dist_dashboard.php';</script>";
     exit();
}

$retailer = pg_fetch_assoc($fetch_result);


$district_query = "SELECT * FROM district";
$district_result = pg_query($fsms_conn, $district_query);

$wh_query = "SELECT serial_no, name FROM wholesalers WHERE district_id = $1";
$wh_result = pg_query_params($master_conn, $wh_query, [$retailer['district_id']]);

if (!$wh_result) {
     echo "<script>alert('Error fetching wholesalers.');</script>";
     exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $name = $_POST['name'];
     $latitude = $_POST['latitude'];
     $longitude = $_POST['longitude'];
     $location = $_POST['location'];
     $address = $_POST['address'];
     $nearest_wholesaler_distance = $_POST['nearest_wholesaler_distance'];
     $district_id = (int) $_POST['district_id'];
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

     $update_query = "UPDATE retailers SET 
        name = $1,
        latitude = $2,
        longitude = $3,
        location = $4,
        address = $5,
        nearest_wholesaler_distance = $6,
        district_id = $7,
        nearest_wholesaler_id = $8,
        nearest_wholesaler_name = $9
        WHERE serial_no = $10";

     $result = pg_query_params($master_conn, $update_query, [
          $name,
          $latitude,
          $longitude,
          $location,
          $address,
          $nearest_wholesaler_distance,
          $district_id,
          $nearest_wholesaler_id,
          $nearest_wholesaler_name,
          $retailer_id
     ]);

     if ($result) {
          echo "<script>alert('Retailer updated successfully.'); window.location.href='dist_dashboard.php';</script>";
     } else {
          echo "<p style='color: red;'>Error: " . pg_last_error($master_conn) . "</p>";
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Update Retailer</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<script>
     function loadWholesalers(districtId) {
          if (districtId) {
               $.ajax({
                    url: 'fetch_wholesalers.php',
                    method: 'POST',
                    data: {
                         district_id: districtId,
                         selected_wholesaler_id: '<?= $retailer["nearest_wholesaler_id"] ?>',
                         selected_distance: '<?= $retailer["nearest_wholesaler_distance"] ?>'
                    },
                    success: function (response) {
                         $('#wholesalerContainer').html(response);
                    },
                    error: function () {
                         alert("Failed to load wholesalers for selected district.");
                    }
               });
          } else {
               $('#wholesalerContainer').html('<option value="">-- Select Wholesaler --</option>');
          }
     }

     $(document).ready(function () {
          // Load initially if editing existing retailer
          const distId = $('#district_id').val();
          if (distId) {
               loadWholesalers(distId);
          }
     });
</script>


<body>

     <?php include('../includes/dist_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

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


                    <label>Select District</label><br>
                    <select name="district_id" id="district_id" onchange="loadWholesalers(this.value)" required>
                         <option value="">-- Select District --</option>
                         <?php
                         pg_result_seek($district_result, 0);
                         while ($district = pg_fetch_assoc($district_result)) {
                              $selected = ($district['id'] == $retailer['district_id']) ? 'selected' : '';
                              echo "<option value=\"" . htmlspecialchars($district['id']) . "\" $selected>" . htmlspecialchars($district['name']) . "</option>";
                         }
                         ?>

                    </select><br><br>

                    <div id="wholesalerContainer">
                         <label>Select Nearest Wholesaler</label><br>
                         <select name="nearest_wholesaler_id" required>
                              <option value="">-- Select Wholesaler --</option>
                         </select><br><br>


                         <label>Nearest Wholesaler Distance</label><br>
                         <input type="number" name="nearest_wholesaler_distance" required><br><br>
                    </div>

                    <div class="form-btn">
                         <button type="submit">Update Retailer</button>
                    </div>

               </form>
          </div>
     </div>

</body>

</html>