<?php



include("../includes/dbconnection.php");

sleep(1);

if (!isset($_GET['id'])) {
     echo "Invalid Request";
     exit;
}

$id = (int) $_GET['id'];

$query = "SELECT * FROM wholesalers WHERE serial_no = $1";
$result = pg_query_params($master_conn, $query, [$id]);

if (!$result || pg_num_rows($result) === 0) {
     echo "Wholesaler not found.";
     exit;
}

$row = pg_fetch_assoc($result);


$warehouses = trim($row['warehouse_names'], '{}');

// $warehouses = str_replace('"', '', $warehouses);           
// $warehouseArray = explode(',', $warehouses);

$distances = trim($row['distances'], '{}');
$warehouse_id = trim($row['warehouse_ids'], '{}');

echo "
<table class='table table-bordered'>
    <tr><th>Serial No(Id)</th><td>{$row['serial_no']}</td></tr>
    <tr><th>Name</th><td>{$row['name']}</td></tr>
    <tr><th>District</th><td>{$row['district_name']}</td></tr>
    <tr><th>District Id</th><td>{$row['district_id']}</td></tr>
    <tr><th>Latitude</th><td>{$row['latitude']}</td></tr>
    <tr><th>Longitude</th><td>{$row['longitude']}</td></tr>
    <tr><th>Location</th><td>{$row['location']}</td></tr>
    <tr><th>Address</th><td>{$row['address']}</td></tr>
    <tr><th> Warehouses</th><td>{$warehouses}</td></tr>
    <tr><th>Warehouse Id's</th><td>{$warehouse_id}</td></tr>
    <tr><th>Distances</th><td>{$distances}</td></tr>
</table>";
?>