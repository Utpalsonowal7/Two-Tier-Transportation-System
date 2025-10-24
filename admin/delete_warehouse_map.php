<?php
session_start();
include("../includes/dbconnection.php");

if (isset($_GET['id'])) {
     $id = (int) $_GET['id'];

     $query = "DELETE FROM warehouse_wholesale_map WHERE id = $1";
     $result = pg_query_params($master_conn, $query, array($id));

     if ($result) {
          $_SESSION['message'] = "Mapp deleted successfully.";
     } else {
          $_SESSION['message'] = "Error deleting retailer: " . pg_last_error($master_conn);
     }
}

header("Location: wholesale_map.php");
exit();
?>