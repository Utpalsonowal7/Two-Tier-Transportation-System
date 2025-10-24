<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
    echo "<script>alert('session has expired, please log in again!'); window.location.href = 'login.php' </script>";
    exit();
}


$districtId = isset($_POST['district_id']) ? $_POST['district_id'] : null;
$wholesalerId = isset($_POST['wholesaler_id']) ? $_POST['wholesaler_id'] : null;

$warehouses = [];
$wholesalers = [];
$selectedWarehouseDistance = '';

if ($districtId) {
    $districtQuery = pg_query_params($fsms_conn, "SELECT * FROM district WHERE id = $1", array($districtId));
    $district = pg_fetch_assoc($districtQuery);
    $wholesalerQuery = pg_query_params($master_conn, "SELECT serial_no, name FROM wholesalers WHERE district_id = $1 ORDER BY name", array($districtId));
    if ($wholesalerId) {
        $warehouseQuery = pg_query_params($master_conn, "SELECT warehouse_names, distances FROM wholesalers WHERE serial_no = $1", array($wholesalerId));
        while ($row = pg_fetch_assoc($warehouseQuery)) {
            $warehouses = preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/', $row['warehouse_names']);

            // $warehouses = explode(',', $row['warehouse_names']);
            $distances = explode(',', $row['distances']);
        }
    }
}

if (isset($_POST['map'])) {
    $warehouse_name = trim($_POST['warehouse_name'], "\"' ");
    $wholesalerId = $_POST['wholesaler_name'];
    $transport_rate = $_POST['transport_rate'];
    $distance = $_POST['distance'];
    $distance = preg_replace('/[{}]/', '', $distance);

    $wholesalerNameQuery = pg_query_params($master_conn, "SELECT name FROM wholesalers WHERE serial_no = $1", array($wholesalerId));
    $wholesalerRow = pg_fetch_assoc($wholesalerNameQuery);
    $wholesaler_name = $wholesalerRow['name'];

    $district_query = "SELECT name FROM district WHERE id = $1";
    $district_result_check = pg_query_params($fsms_conn, $district_query, [$districtId]);

    if (pg_num_rows($district_result_check) > 0) {
        $district = pg_fetch_assoc($district_result_check);
        $district_name = $district['name'];
    } else {

        echo "<p style='color: red;'>Error: Invalid district selected.</p>";
        exit();
    }

    $checkQuery = pg_query_params($master_conn, "SELECT 1 FROM warehouse_wholesale_map WHERE warehouse_name = $1 AND wholesaler_name = $2 AND district_id = $3", array($warehouse_name, $wholesaler_name, $districtId));
    if (pg_num_rows($checkQuery) == 0) {
        $insertQuery = pg_query_params($master_conn, "INSERT INTO warehouse_wholesale_map (warehouse_name, wholesaler_name, district_id, transport_rate, distance, district_name) VALUES ($1, $2, $3, $4, $5, $6)", array($warehouse_name, $wholesaler_name, $districtId, $transport_rate, $distance, $district_name));
        if ($insertQuery) {
            echo "<script>alert(' Mapping created successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . pg_last_error($master_conn) . "');</script>";
        }
    } else {
        echo "<script>alert('This mapping already exists.');</script>";
    }
}

$district_result = pg_query($fsms_conn, "SELECT * FROM district");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/add_admin.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Map Wholesaler to Warehouse</title>
</head>

<body>

    <?php include('../includes/user_sidebar.php'); ?>
    <?php include('../includes/header.php'); ?>

    <div class="main-content">
        <div class="dashboard">
            <span class="icon"><i class="fa-solid fa-link"></i></span>
            <h3 class="slash">/</h3>
            <a href="#">
                <h2>Map Wholesaler</h2>
            </a>
        </div>

        <div class="form">
            <form method="POST">
                <label for="district">Select District</label>
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
                </select><br><br>

                <?php if ($districtId): ?>
                    <label>Select Wholesaler:</label><br>
                    <select name="wholesaler_id" id="wholesaler" required onchange="this.form.submit()">
                        <option value="">-- Select Wholesaler --</option>
                        <?php while ($row = pg_fetch_assoc($wholesalerQuery)) { ?>
                            <option value="<?php echo htmlspecialchars($row['serial_no']); ?>" <?php echo ($row['serial_no'] == $wholesalerId) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php } ?>
                    </select><br><br>

                    <?php if ($wholesalerId): ?>
                        <label>Select Warehouse:</label><br>
                        <select name="warehouse_name" id="warehouse" required>
                            <option value="">-- Select Warehouse --</option>
                            <?php

                            foreach ($warehouses as &$warehouse) {
                                $warehouse = trim(preg_replace('/^{?"?|"?}?$/', '', $warehouse));
                            }
                            foreach ($distances as &$distance) {
                                $distance = trim(preg_replace('/[{}"]/', '', $distance));
                            }
                            unset($warehouse, $distance);


                            array_multisort($warehouses, SORT_ASC, $distances);


                            foreach ($warehouses as $index => $warehouse) {
                                $distance = $distances[$index] ?? 'Unknown';
                                echo "<option value=\"$warehouse\" data-distance=\"$distance\">$warehouse - Distance: $distance</option>";
                            }
                            ?>
                        </select><br><br>



                        <label for="distance">Distance:</label><br>
                        <input type="number" step="0.01" id="distance" name="distance" value="" readonly><br><br>

                        <label for="transport_rate">Transport Rate:</label><br>
                        <input type="number" step="0.01" id="transport_rate" name="transport_rate" maxlength="3" required><br><br>

                        <input type="hidden" name="wholesaler_name" id="wholesaler_name_hidden"
                            value="<?php echo htmlspecialchars($wholesalerId); ?>">

                        <div class="form-btn">
                            <button type="submit" name="map">Map</button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        const warehouseDropdown = document.getElementById('warehouse');
        if (warehouseDropdown) {
            warehouseDropdown.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.getAttribute('data-distance')) {
                    document.getElementById('distance').value = selectedOption.getAttribute('data-distance');
                } else {
                    document.getElementById('distance').value = '';
                }
            });
        }
    </script>

</body>

</html>