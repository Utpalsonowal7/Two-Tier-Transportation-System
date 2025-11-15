<?php
session_start();
include("../includes/dbconnection.php");


require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['login'])) {
     echo "<script>alert('Session expired. Please log in again.'); window.location.href='login.php';</script>";
     exit();
}

$imported = 0;
$skipped = 0;
$errors = [];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {
     $file = $_FILES['excel_file']['tmp_name'];

     try {
          $spreadsheet = IOFactory::load($file);
          $sheet = $spreadsheet->getActiveSheet();
          $rows = $sheet->toArray();


          for ($i = 1; $i < count($rows); $i++) {
               $data = $rows[$i];
               $rowNumber = $i + 1;

               if (count($data) < 8) {
                    $errors[] = "Row $rowNumber: Missing one or more required columns.";
                    $skipped++;
                    continue;
               }

               $name = trim($data[0]);
               $latitude = trim($data[1]);
               $longitude = trim($data[2]);
               $location = trim($data[3]);
               $address = trim($data[4]);
               $district_id = (int) trim($data[5]);

               if (!is_numeric($latitude) || !is_numeric($longitude)) {
                    $errors[] = "Row $rowNumber: Invalid latitude or longitude.";
                    $skipped++;
                    continue;
               }

               $district_result = pg_query_params($fsms_conn, "SELECT name FROM district WHERE id = $1", [$district_id]);
               if (!$district_result || pg_num_rows($district_result) == 0) {
                    $errors[] = "Row $rowNumber: Invalid district ID '$district_id'.";
                    $skipped++;
                    continue;
               }

               $district_name = pg_fetch_result($district_result, 0, 'name');


               $exists = pg_query_params($master_conn, "SELECT 1 FROM warehouse WHERE name = $1", [ $name]);
               if (pg_num_rows($exists) > 0) {
                    $errors[] = "Row $rowNumber: Duplicate coordinates (already exists).";
                    $skipped++;
                    continue;
               }


               $insert = pg_query_params(
                    $master_conn,
                    "INSERT INTO warehouse (name, latitude, longitude, location, address, district_id, district_name)
                VALUES ($1, $2, $3, $4, $5, $6, $7)",
                    [$name, $latitude, $longitude, $location, $address, $district_id, $district_name]
               );

               if ($insert) {
                    $imported++;
               } else {
                    $errors[] = "Row $rowNumber: Database insertion failed.";
                    $skipped++;
               }
          }

          $message = "Import complete: $imported row(s) added, $skipped skipped.";
     } catch (Exception $e) {
          $errors[] = "Excel read error: " . $e->getMessage();
     }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <title>Import Warehouse (Excel)</title>
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
               background-color: rgba(0, 0, 0, 0.4);
          }

          .modal-content {
               background-color: #fff;
               font-size: 20px;
               margin: 10% auto;
               padding: 40px;
               border: 1px solid #888;
               width: 400px;
               border-radius: 10px;
               text-align: center;
               box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
          }

          .close {
               color: #aaa;
               float: right;
               font-size: 24px;
               font-weight: bold;
               cursor: pointer;
          }
     </style>
</head>

<body>
     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="upload-box">
               <h2>Import Warehouse Data from Excel</h2>
               <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="excel_file" accept=".xlsx,.xls" required><br><br>
                    <button type="submit">Import Data</button>
               </form>

               <?php if (!empty($message)) {
                    echo "<p class='msg'>" . htmlspecialchars($message) . "</p>";
               } ?>

               <?php if (!empty($errors)) {
                    echo "<div class='error-box'><strong>Errors:</strong><br>";
                    foreach ($errors as $err) {
                         echo "• " . htmlspecialchars($err) . "<br>";
                    }
                    echo "</div>";
               } ?>

               <br>
               <a href="add_warehouse.php">← Back to Add Warehouse</a>
          </div>
     </div>

     <div id="excelNoticeModal" class="modal">
          <div class="modal-content">
               <span class="close"
                    onclick="document.getElementById('excelNoticeModal').style.display='none'">&times;</span>
               <h3> Important Notice</h3>
               <p>Please ensure your Excel file columns are in the following order:</p>
               <ol style="text-align: left; padding-left: 20px;">
                    <li>name</li>
                    <li>latitude (decimal)</li>
                    <li>longitude (decimal)</li>
                    <li>location</li>
                    <li>address</li>
                    <li>district_id (number)</li>
               </ol>
          </div>
     </div>

     <script>
          window.onload = function () {

               if(!sessionStorage.getItem ('excelNoticeModal')) {
               setTimeout(function () {
                    document.getElementById("excelNoticeModal").style.display = "block";

                    sessionStorage.setItem('excelNoticeModal', 'true');
               }, 3000); 
          }
          };
     </script>

</body>

</html>