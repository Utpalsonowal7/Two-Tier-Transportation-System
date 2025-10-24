<?php 
session_start();
include("../includes/dbconnection.php");

if(isset($_GET['id'])) {
     $id = (int) $_GET['id'];

     $query = "DELETE FROM wholesale_retailer_map WHERE id = $1";
     $result = pg_query_params($master_conn, $query, array($id));

     if(!$result) {
          die("Error: " .pg_last_error($master_conn));
     }
}    

header("Location: retailer_map.php");
exit();
?>