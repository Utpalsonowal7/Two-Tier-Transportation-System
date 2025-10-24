<?php
session_start();
include("../includes/dbconnection.php");

if(isset($_GET['id'])) {
     $id = (int) $_GET['id'];

     $query = "DELETE FROM warehouse WHERE serial_no = $1";
     $result = pg_query_params($master_conn, $query, array($id));

     if (!$result) {
          die("Error in deletion: " . pg_last_error($master_conn));
     }

}

header("Location: warehouse.php");
exit();   
?>