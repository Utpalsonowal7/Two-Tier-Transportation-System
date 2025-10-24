<?php
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['adminid']) || strlen($_SESSION['adminid']) == 0) {
     header('location:dist_logout.php');
     exit();
}

$id = $_SESSION['adminid'];

$query = "SELECT name, username, mobile_number, password FROM  district_users WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, [$id]);
$row = pg_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
     $name = trim($_POST['name']);
     $username = trim($_POST['username']);
     $mobile_number = trim($_POST['mobile_number']);
     $password = trim($_POST['password']);

     if (empty($name) || empty($username) || empty($mobile_number) || empty($password)) {
          echo "<script>alert('All fields are required!');</script>";
     } else {

          if (!password_verify($password, $row['password'])) {
               echo "<script>alert('Incorrect password! Please try again.');</script>";
          } else {

               $update_query = "UPDATE  district_users SET name = $1, username = $2, mobile_number = $3 WHERE id = $4";
               $update_result = pg_query_params($fsms_conn, $update_query, [$name, $username, $mobile_number, $id]);

               if ($update_result) {
                    echo "<script>alert('Profile updated successfully!'); window.location.href = 'dashboard.php';</script>";

                    $row['name'] = $name;
                    $row['username'] = $username;
                    $row['mobile_number'] = $mobile_number;
               } else {
                    echo "<script>alert('Update failed. Please try again.');</script>";
               }
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
     <title>Update Profile</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>


     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-user"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Update Profile</h2>
               </a>
          </div>

          <div class="form">
               <?php if ($row) { ?>
                    <form action="setting.php" method="POST">
                         <label for="name">Name:</label>
                         <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>

                         <label for="username">Username:</label>
                         <input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>"
                              required>

                         <label for="mobile_number">Phone Number:</label>
                         <input type="text" name="mobile_number" maxlength="10" pattern="\d{10}"
                              value="<?php echo htmlspecialchars($row['mobile_number']); ?>" required>
                              <br><br>

                         <label for="password">Enter Your Password:</label>
                         <input type="password" name="password" required>

                         <div class="form-btn">
                              <button type="submit" name="update">Update</button>
                         </div>
                    </form>
               <?php } else { ?>
                    <p>Error: Unable to fetch profile details.</p>
               <?php } ?>
          </div>
     </div>

</body>

</html>