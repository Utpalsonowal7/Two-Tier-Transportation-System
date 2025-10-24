<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
include("../includes/dbconnection.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['login'])) {
     echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
     exit();
}

$imported = 0;
$skipped = 0;
$errors = [];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
     $file = $_FILES["csv_file"]["tmp_name"];
     $ext = pathinfo($_FILES["csv_file"]["name"], PATHINFO_EXTENSION);
     $rows = [];

     if ($ext === 'xlsx' || $ext === 'xls') {
          try {
               $spreadsheet = IOFactory::load($file);
               $sheet = $spreadsheet->getActiveSheet();
               $rows = $sheet->toArray();
          } catch (Exception $e) {
               $errors[] = "Error reading Excel file: " . $e->getMessage();
          }
     } elseif ($ext === 'csv') {
          if (($handle = fopen($file, "r")) !== false) {
               while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $rows[] = $data;
               }
               fclose($handle);
          } else {
               $errors[] = "Failed to open uploaded CSV file.";
          }
     } else {
          $errors[] = "Unsupported file format. Only CSV or Excel files are allowed.";
     }

     array_shift($rows);
     $rowNumber = 1;

     foreach ($rows as $data) {
          $rowNumber++;

          if (count($data) < 8) {
               $errors[] = "Row $rowNumber: Expected 8 columns, found " . count($data) . ".";
               $skipped++;
               continue;
          }

          $name = trim($data[0]);
          $latitude = trim($data[1]);
          $longitude = trim($data[2]);
          $location = trim($data[3]);
          $address = trim($data[4]);
          $district_id = (int) trim($data[5]);
          $warehouse_ids_raw = trim($data[6]);
          $distances_raw = trim($data[7]);

          if (!is_numeric($latitude) || !is_numeric($longitude)) {
               $errors[] = "Row $rowNumber: Latitude or longitude is not numeric.";
               $skipped++;
               continue;
          }

          $warehouse_ids = explode(",", $warehouse_ids_raw);
          $distances = explode(",", $distances_raw);

          if (count($warehouse_ids) !== count($distances)) {
               $errors[] = "Row $rowNumber: Mismatch between warehouse IDs and distances.";
               $skipped++;
               continue;
          }

          $warehouse_names = [];
          foreach ($warehouse_ids as $wid) {
               $res = pg_query_params($master_conn, "SELECT name FROM warehouse WHERE serial_no = $1", [$wid]);
               if ($res && pg_num_rows($res) > 0) {
                    $row = pg_fetch_assoc($res);
                    $warehouse_names[] = $row['name'];
               } else {
                    $errors[] = "Row $rowNumber: Invalid warehouse ID '$wid'.";
                    $skipped++;
                    continue 2;
               }
          }

          $dres = pg_query_params($fsms_conn, "SELECT name FROM district WHERE id = $1", [$district_id]);
          if (!$dres || pg_num_rows($dres) == 0) {
               $errors[] = "Row $rowNumber: Invalid district ID '$district_id'.";
               $skipped++;
               continue;
          }

          $district_name = pg_fetch_result($dres, 0, 'name');

          $exists = pg_query_params(
               $master_conn,
               "SELECT 1 FROM wholesalers WHERE latitude = $1 AND longitude = $2",
               [$latitude, $longitude]
          );

          if (pg_num_rows($exists) > 0) {
               $errors[] = "Row $rowNumber: Duplicate latitude/longitude already exists.";
               $skipped++;
               continue;
          }

          $query = pg_query_params(
               $master_conn,
               "INSERT INTO wholesalers (name, latitude, longitude, location, address, district_id, warehouse_ids, warehouse_names, distances, district_name)
             VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
               [
                    $name,
                    $latitude,
                    $longitude,
                    $location,
                    $address,
                    $district_id,
                    '{' . implode(",", $warehouse_ids) . '}',
                    '{"' . implode('","', array_map('addslashes', $warehouse_names)) . '"}',
                    '{' . implode(",", $distances) . '}',
                    $district_name
               ]
          );

          if ($query) {
               $imported++;
          } else {
               $errors[] = "Row $rowNumber: Database insertion failed.";
               $skipped++;
          }
     }

     $msg = "Import complete: $imported row(s) added, $skipped skipped.";
}
?>
<!DOCTYPE html>
<html>

<head>
     <title>Import Wholesalers</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <style>
          .upload-box {
               width: 600px;
               margin: 50px auto;
               background: #fff;
               padding: 30px;
               box-shadow: 0 0 10px #ccc;
               border-radius: 10px;
          }

          .upload-box h2 {
               text-align: center;
               margin-bottom: 20px;
          }

          .upload-box input[type="file"] {
               margin-bottom: 15px;
          }

          .upload-box button {
               display: block;
               width: 100%;
               padding: 10px;
               background-color: #00a86b;
               color: white;
               border: none;
               border-radius: 5px;
               font-weight: bold;
               cursor: pointer;
          }

          .upload-box a {
               text-decoration: none;
               color: #00a86b;
          }

          .msg {
               margin-top: 15px;
               font-size: 16px;
               text-align: center;
               color: green;
          }

          .error-box {
               margin-top: 20px;
               padding: 10px;
               background-color: #ffe6e6;
               border: 1px solid red;
               border-radius: 5px;
               color: red;
               font-size: 15px;
          }

          .modal {
               display: none;
               position: fixed;
               z-index: 1000;
               left: 0;
               top: 0;
               width: 100%;
               height: 100%;
               overflow: auto;
               background-color: rgba(0, 0, 0, 0.5);
          }

          .modal-content {
               background-color: #fff;
               margin: 10% auto;
               font-size: 18px;
               padding: 20px;
               width: 450px;
               border-radius: 8px;
               box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
               text-align: center;
          }

          .modal-content h3 {
               margin-top: 0;
               color: #00a86b;
          }

          .modal .close {
               float: right;
               font-size: 24px;
               font-weight: bold;
               cursor: pointer;
               color: #888;
          }
     </style>
</head>

<body>
     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>
     <div class="main-content">
          <div class="upload-box">
               <h2>Import Wholesalers Data</h2>
               <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="csv_file" accept=".csv,.xlsx,.xls" required><br><br>
                    <button type="submit">Import Data</button>
               </form>
               <?php if (!empty($msg)) {
                    echo "<p class='msg'>" . htmlspecialchars($msg) . "</p>";
               } ?>
               <?php if (!empty($errors)) {
                    echo "<div class='error-box'><strong>Errors Found:</strong><br>";
                    foreach ($errors as $e) {
                         echo "• " . htmlspecialchars($e) . "<br>";
                    }
                    echo "</div>";
               } ?>
               <br>
               <a href="add_wholesaler.php">← Back to Wholesaler Form</a>
          </div>
     </div>

     <div id="formatInfoModal" class="modal">
          <div class="modal-content">
               <span class="close"
                    onclick="document.getElementById('formatInfoModal').style.display='none'">&times;</span>
               <h3> Important Format Notice</h3>
               <p>Please make sure your file has the columns in the **exact order** below:</p>
               <ol style="text-align: left; padding-left: 20px;">
                    <li>name</li>
                    <li>latitude</li>
                    <li>longitude</li>
                    <li>location</li>
                    <li>address</li>
                    <li>district_id</li>
                    <li>warehouse_ids (comma-separated)</li>
                    <li>distances (comma-separated, matching warehouse_ids)</li>
               </ol>
          </div>
     </div>

  <script>
    window.onload = function () {
       
        if (!sessionStorage.getItem('formatModalShown')) {
            setTimeout(function () {
                document.getElementById('formatInfoModal').style.display = 'block';
             
                sessionStorage.setItem('formatModalShown', 'true');
            }, 3000);
        }
    };
</script>

</body>

</html>