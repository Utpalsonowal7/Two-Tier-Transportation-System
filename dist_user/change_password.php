<?php
session_start();
include("../includes/dbconnection.php");

// if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
//      header('location:logout.php');
//      exit();
// }

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}


$id = $_SESSION['adminid'];


$query = "SELECT password FROM district_users WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, [$id]);
$row = pg_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
     $old_password = trim($_POST['old_password']);
     $new_password = trim($_POST['new_password']);
     $confirm_password = trim($_POST['confirm_password']);

     if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
          echo "<script>alert('All fields are required!');</script>";
     } elseif ($new_password !== $confirm_password) {
          echo "<script>alert('New passwords do not match!');</script>";
     } elseif (!password_verify($old_password, $row['password'])) {
          echo "<script>alert('Incorrect old password!');</script>";
     } else {
          $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);


          $update_query = "UPDATE district_users SET password = $1 WHERE id = $2";
          $update_result = pg_query_params($fsms_conn, $update_query, [$hashed_password, $id]);

          if ($update_result) {
               echo "<script>alert('Password changed successfully!'); window.location.href='login.php';</script>";
          } else {
               echo "<script>alert('Password update failed. Please try again.');</script>";
          }
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Change Password</title>
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-key"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Change Password</h2>
               </a>
          </div>

          <div class="form">
               <form action="change_password.php" method="POST">
                    <label for="old_password">Old Password:</label>
                    <input type="password" name="old_password" required>

                    <label for="new_password">New Password:</label>
                    <div class="showPass">
                         <input type="password" name="new_password" id="password" required>
                         <span><i class="fas fa-eye" id="togglePassword"></i></span>
                    </div>

                    <div class="validation" id="passwordValidation">
                         <p>Password must contain at least one number and one uppercase and lowercase letter, and at
                              least 10
                              characters</p>
                    </div>

                    <label for="confirm_password">Confirm New Password:</label>
                    <div class="showPass">
                         <input type="password" name="confirm_password" id="confirmPass" required>
                         <span><i class="fas fa-eye" id="toggleConfirmPassword"></i></span>
                    </div>

                    <div class="form-btn">
                         <button type="submit" name="change_password">Update</button>
                    </div>
               </form>
          </div>
     </div>

     <script src="../js/valid.js"></script>
</body>

</html>