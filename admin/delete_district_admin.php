<?php
session_start();
include("../includes/dbconnection.php");

if(isset($_GET['id'])) {
     $id = (int) $_GET['id'];

     $query = "DELETE FROM district_admins WHERE id = $1";
     $result = pg_query_params($fsms_conn, $query, array($id));

     if ($result) {
          $_SESSION['message'] = "District Admin deleted successfully.";
     } else {
          $_SESSION['message'] = "Error deleting District Admin: " . pg_last_error($fsms_conn);
     }
}
header("Location: district_admin_list.php");

?>