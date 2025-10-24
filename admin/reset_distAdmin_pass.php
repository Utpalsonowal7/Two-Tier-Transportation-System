<?php
session_start();
include('../includes/dbconnection.php');
include('../includes/encryption.php');


if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
     header('location:logout.php');
     exit();
}


if (!isset($_GET['id'])) {
     echo "<script>alert('Invalid request.'); window.location.href='dashboard.php';</script>";
     exit();
}

$encryptedId = $_GET['id'];
$districtAdminId = decrypt_id($encryptedId);


$adminQuery = pg_query_params($fsms_conn, "SELECT name FROM district_admins WHERE id = $1", [$districtAdminId]);
$adminName = ($adminQuery && pg_num_rows($adminQuery) > 0) ? pg_fetch_result($adminQuery, 0, 'name') : 'Unknown';


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['change_password'])) {
     $newPassword = $_POST['new_password'];
     $confirmPassword = $_POST['confirm_password'];

     if ($newPassword === $confirmPassword) {
          $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

          $updateQuery = pg_query_params($fsms_conn, "UPDATE district_admins SET password = $1 WHERE id = $2", [
               $hashedPassword,
               $districtAdminId
          ]);

          if ($updateQuery) {
               echo "<script>alert('Password reset successfully for District Admin.'); window.location.href='district_admin_list.php';</script>";
               exit();
          } else {
               echo "<script>alert('Failed to reset password.');</script>";
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
     <title>Reset District Admin Password</title>
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
     <?php include('../includes/process.php'); ?>
     <?php include('../includes/sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-key"></i></span>
               <h3 class="slash">/</h3>
               <h2>Reset Password for : <span style="color: #2e8b57;"><?php echo htmlspecialchars($adminName); ?></span>
               </h2>
          </div>

          <div class="form">
               <form method="POST">

                    <label for="new_password">New Password:</label>
                    <div class="showPass">
                         <input type="password" id="password" name="new_password" required>
                         <span><i class="fa fa-eye" id="togglePassword"></i></span>
                    </div>

                    <div class="validation" id="passwordValidation" style="display: none;">
                         <p>Password must contain at least one number and one uppercase and lowercase letter, and at
                              least 10 characters</p>
                    </div>

                    <label for="confirm_password">Confirm New Password:</label>
                    <div class="showPass">
                         <input type="password" name="confirm_password" id="confirmPass" required>
                         <span><i class="fa fa-eye" id="toggleConfirmPassword"></i></span>
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