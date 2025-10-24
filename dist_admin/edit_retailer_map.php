<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include('../includes/encryption.php');

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
    echo "<script>alert('Session expired, please log in again!'); window.location.href='login.php';</script>";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$admin_id = $_SESSION['adminid'];
$district_query = "SELECT district_id FROM district_admins WHERE id = $1";
$district_result = pg_query_params($fsms_conn, $district_query, [$admin_id]);
$district_row = pg_fetch_assoc($district_result);
$district_id = $district_row['district_id'];


$encrypted_id = $_GET['id'];
$id = decrypt_id($encrypted_id);

$map_result = pg_query_params($master_conn, "SELECT * FROM wholesale_retailer_map WHERE id = $1", [$id]);
if (!$map_result || pg_num_rows($map_result) == 0) {
    echo "Mapping not found.";
    exit();
}
$map_row = pg_fetch_assoc($map_result);
$current_retailer = $map_row['retailer_name'];


$retailer_data = [];
$retailer_query = pg_query_params($master_conn, "SELECT name, nearest_wholesaler_name, nearest_wholesaler_distance FROM retailers where district_id = $1 ORDER BY name", [$district_id]);

while ($r = pg_fetch_assoc($retailer_query)) {
    $retailer_data[$r['name']] = [
        "wholesaler" => $r['nearest_wholesaler_name'],
        "distance" => $r['nearest_wholesaler_distance']
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $retailer_name = $_POST['retailer_name'];
    $wholesaler_name = $_POST['wholesaler_name'];
    $distance = $_POST['distance'];
    $transport_rate = $_POST['transport_rate'];

    $update_query = "UPDATE wholesale_retailer_map 
                     SET wholesaler_name = $1, retailer_name = $2, transport_rate = $3, distance = $4 
                     WHERE id = $5";
    $update_result = pg_query_params($master_conn, $update_query, [
        $wholesaler_name, $retailer_name, $transport_rate, $distance, $id
    ]);

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
    <title>Edit Retailer Mapping</title>
    <link rel="stylesheet" href="../assets/add_admin.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        const retailerMap = <?php echo json_encode($retailer_data); ?>;
    </script>
</head>

<body>
<?php include("../includes/dist_sidebar.php"); ?>
<?php include("../includes/header.php"); ?>

<div class="main-content">
    <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Edit Retailer Mapped</h2>
               </a>
          </div>

    <div class="form">
        <form method="POST">
            
            <label>Retailer Name</label><br>
            <select name="retailer_name" id="retailerDropdown" required>
                <option value="">-- Select Retailer --</option>
                <?php foreach ($retailer_data as $retailer => $info): ?>
                    <option value="<?php echo htmlspecialchars($retailer); ?>"
                        <?php echo ($retailer === $current_retailer) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($retailer); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

           
            <label>Wholesaler Name</label><br>
            <input type="text" name="wholesaler_name" id="wholesalerInput"
                   value="<?php echo htmlspecialchars($map_row['wholesaler_name']); ?>" readonly required><br><br>

         
            <label>Distance (km)</label><br>
            <input type="text" name="distance" id="distanceInput"
                   value="<?php echo htmlspecialchars($map_row['distance']); ?>" readonly required><br><br>

            
            <label>Transport Rate (₹)</label><br>
            <input type="number" name="transport_rate"
                   value="<?php echo htmlspecialchars($map_row['transport_rate']); ?>" required><br><br>

            <div class="form-btn">
                <button type="submit">Update Mapping</button>
            </div>
        </form>
    </div>
</div>

<script>
    const retailerDropdown = document.getElementById('retailerDropdown');
    const wholesalerInput = document.getElementById('wholesalerInput');
    const distanceInput = document.getElementById('distanceInput');

    retailerDropdown.addEventListener('change', function () {
        const selected = this.value;
        if (retailerMap[selected]) {
            wholesalerInput.value = retailerMap[selected].wholesaler;
            distanceInput.value = retailerMap[selected].distance;
        } else {
            wholesalerInput.value = '';
            distanceInput.value = '';
        }
    });
</script>
</body>
</html>
