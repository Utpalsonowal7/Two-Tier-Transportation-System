<?php
session_start();
include('../includes/dbconnection.php');

if (isset($_SESSION['login']) && $_SESSION['adminid']) {
    $adminId = $_SESSION['adminid'];
     $newOtp = rand(100000, 999999);

    $res =  pg_query_params($fsms_conn, "UPDATE systemadmin SET otp = $1 WHERE id = $2", array($newOtp, $adminId));

     if ($res) {
          echo $newOtp;  
     } else {
          echo "ERROR";
     }
} else {
     echo "UNAUTHORIZED";
}
?>