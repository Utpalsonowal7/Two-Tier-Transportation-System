<?php
session_start();
include("../includes/dbconnection.php");

if (isset($_GET['id'])) {
     $id = (int) $_GET['id'];

     $query = "DELETE FROM transport WHERE id = $1";
     $result = pg_query_params($master_conn, $query, array($id));

     if ($result) {
          echo "<script>alert('Transport deleted successfully.');</script>";
     } else {
          $_SESSION['message'] = "Error deleting retailer: " . pg_last_error($master_conn);
     }
}

header("Location: warehouse_wholesale_data.php");
exit();

?>