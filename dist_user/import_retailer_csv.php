<?php
session_start();
require '../vendor/autoload.php';
include("../includes/dbconnection.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['login'])) {
     echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
     exit();
}

$imported = 0;
$skipped = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
     $file = $_FILES['excel_file']['tmp_name'];

     try {
          $spreadsheet = IOFactory::load($file);
          $sheet = $spreadsheet->getActiveSheet();
          $rows = $sheet->toArray(null, true, true, true);

          $rowNumber = 1;
          foreach ($rows as $row) {
               if ($rowNumber === 1) {
                    $rowNumber++;
                    continue;
               }

               $name = trim($row['A']);
               $latitude = trim($row['B']);
               $longitude = trim($row['C']);
               $location = trim($row['D']);
               $address = trim($row['E']);
               $distance = (float) trim($row['F']);
               $district_id = (int) trim($row['G']);
               $wholesaler_id = (int) trim($row['H']);

               if (!is_numeric($latitude) || !is_numeric($longitude)) {
                    $errors[] = "Row $rowNumber: Invalid latitude or longitude.";
                    $skipped++;
                    $rowNumber++;
                    continue;
               }

               $wh_result = pg_query_params($master_conn, "SELECT name FROM wholesalers WHERE serial_no = $1", [$wholesaler_id]);
               if (!$wh_result || pg_num_rows($wh_result) === 0) {
                    $errors[] = "Row $rowNumber: Invalid wholesaler ID.";
                    $skipped++;
                    $rowNumber++;
                    continue;
               }
               $wh_name = pg_fetch_result($wh_result, 0, 'name');

               $dist_result = pg_query_params($fsms_conn, "SELECT name FROM district WHERE id = $1", [$district_id]);
               if (!$dist_result || pg_num_rows($dist_result) === 0) {
                    $errors[] = "Row $rowNumber: Invalid district ID.";
                    $skipped++;
                    $rowNumber++;
                    continue;
               }
               $district_name = pg_fetch_result($dist_result, 0, 'name');

               $exists = pg_query_params($master_conn, "SELECT 1 FROM retailers WHERE latitude = $1 AND longitude = $2", [$latitude, $longitude]);
               if (pg_num_rows($exists) > 0) {
                    $errors[] = "Row $rowNumber: Duplicate retailer (lat/long exists).";
                    $skipped++;
                    $rowNumber++;
                    continue;
               }

               $insert = pg_query_params(
                    $master_conn,
                    "INSERT INTO retailers (name, latitude, longitude, location, address, nearest_wholesaler_distance, district_id, nearest_wholesaler_id, nearest_wholesaler_name, district_name)
                 VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)",
                    [$name, $latitude, $longitude, $location, $address, $distance, $district_id, $wholesaler_id, $wh_name, $district_name]
               );

               if ($insert) {
                    $imported++;
               } else {
                    $errors[] = "Row $rowNumber: Database error.";
                    $skipped++;
               }

               $rowNumber++;
          }

     } catch (Exception $e) {
          $errors[] = "Error reading Excel file: " . $e->getMessage();
     }
}
?>

<!DOCTYPE html>
<html>

<head>
     <title>Import Retailers Excel</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/add_admin.css">
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
               background-color: #00a86b;
               color: white;
               padding: 10px 15px;
               border-radius: 5px;
               display: inline-block;
               margin-top: 10px;
          }

          .msg {
               color: green;
               text-align: center;
               font-size: 16px;
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
               z-index: 9999;
               left: 0;
               top: 0;
               width: 100%;
               height: 100%;
               overflow: auto;
               background-color: rgba(0, 0, 0, 0.4);
          }

          .modal-content {
               background-color: #fff;
               margin: 10% auto;
               font-size: 18px;
               padding: 25px;
               border: 1px solid #ccc;
               width: 400px;
               border-radius: 8px;
               text-align: center;
               box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
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
               color: #666;
          }
     </style>
</head>

<body>
     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="upload-box">
               <h2>Import Retailers from Excel</h2>
               <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="excel_file" accept=".xlsx, .xls" required><br><br>
                    <button type="submit">Import Retailers</button>
               </form>
               <?php if ($imported || $skipped): ?>
                    <p class="msg">Imported: <?= $imported ?>, Skipped: <?= $skipped ?></p>
               <?php endif; ?>

               <?php if (!empty($errors)): ?>
                    <div class="error-box">
                         <strong>Errors:</strong><br>
                         <?php foreach ($errors as $e): ?>
                              • <?= htmlspecialchars($e) ?><br>
                         <?php endforeach; ?>
                    </div>
               <?php endif; ?>
               <br>
               <a href="add_retailer.php">← Back to Retailer Form</a>
          </div>
     </div>

     <div id="formatModal" class="modal">
          <div class="modal-content">
               <span class="close" onclick="document.getElementById('formatModal').style.display='none'">&times;</span>
               <h3> Required Column Format</h3>
               <p>Please ensure your Excel file follows this column order:</p>
               <ol style="text-align: left; padding-left: 20px;">
                    <li>Name</li>
                    <li>Latitude</li>
                    <li>Longitude</li>
                    <li>Location</li>
                    <li>Address</li>
                    <li>Distance to Wholesaler (in km)</li>
                    <li>District ID</li>
                    <li>Wholesaler ID</li>
               </ol>
          </div>
     </div>


     <script>
          window.onload = function () {

               if(!sessionStorage.getItem('formatModal')) {
               setTimeout(function () {
                    document.getElementById('formatModal').style.display = 'block';

                    sessionStorage.setItem('formatModal', 'true');
               }, 3000);
          }
          };
     </script>

</body>

</html>