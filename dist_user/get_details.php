<?php
session_start();
include("../includes/dbconnection.php");
header('Content-type: application/json');

if (!isset($_SESSION['login'])) {
     echo json_encode([
          "error" => "Session Expired. Please logIn again!."
     ]);
     exit();
}

$id = $_SESSION['adminid'];

$userQuery = "select district_id, district_name from district_users where id = $1";
$userResult = pg_query_params($fsms_conn, $userQuery, [$id]);
$result = pg_fetch_assoc($userResult);
$district_id = $result['district_id'] ?? '';
$district = $result['district_name'] ?? '';

$warehouseQuery = "select serial_no, name from warehouse where district_id = $1";
$warehouseResult = pg_query_params($master_conn, $warehouseQuery, [$district_id]);
$warehouse = [];
while ($row = pg_fetch_assoc($warehouseResult)) {
     $warehouse[] = $row;
}


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//      var_dump($_POST);
//      exit(); 
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

     if (isset($_POST['warehouseName'])) {
          $warehouse_name = $_POST['warehouseName'] ?? '';

          $wholeQuery = "select DISTINCT wholesaler_name, transport_rate from warehouse_wholesale_map where district_id = $1 AND warehouse_name = $2";
          $wholeResult = pg_query_params($master_conn, $wholeQuery, [$district_id, $warehouse_name]);
          $wholesaler = [];
          while ($row = pg_fetch_assoc($wholeResult)) {
               $wholesaler[] = $row;
          }

          echo json_encode([
               "wholesaler" => $wholesaler
          ]);
          exit();
     }


     if (isset($_POST['wholesalerName'])) {
          $wholesaler_name = trim($_POST['wholesalerName']);

          $retailerQuery = "select DISTINCT retailer_name, transport_rate from wholesale_retailer_map where district_id = $1 and TRIM(wholesaler_name) = $2";
          $retailerResult = pg_query_params($master_conn, $retailerQuery, [$district_id, $wholesaler_name]);
          $retailer = [];

          while ($row = pg_fetch_assoc($retailerResult)) {
               $retailer[] = $row;
          }

          echo json_encode(["retailer" => $retailer]);
          exit();
     }
}



echo json_encode([
     "district_id" => $district_id,
     "district" => $district,
     "warehouse" => $warehouse,
     // "wholesaler" => $wholesaler,
]);


?>