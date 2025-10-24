<?php
session_start();
include("../includes/dbconnection.php");

if (isset($_GET['id'])) {
     $id = (int) $_GET['id'];

     $query = "DELETE FROM district_users WHERE id = $1";
     $result = pg_query_params($fsms_conn, $query, array($id));

     if ($result) {
          $_SESSION['message'] = "District User deleted successfully.";
     } else {
          $_SESSION['message'] = "Error deleting District User: " . pg_last_error($fsms_conn);
     }
}

header("Location: district_user_list.php");
exit();

?>