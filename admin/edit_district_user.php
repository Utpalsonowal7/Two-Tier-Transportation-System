<?php
ob_start();
session_start();
include("../includes/dbconnection.php");
include('../includes/encryption.php'); 

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
     exit();
}

$id = $_GET['id'] ?? null;
$encrypted_id = $_GET['id'];
$id = decrypt_id($encrypted_id);


if ($id) {
     $sqlEdit = "SELECT * FROM district_users  WHERE id = $1";
     $result = pg_query_params($fsms_conn, $sqlEdit, [$id]);
     $row = pg_fetch_assoc($result);

     if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $name = $_POST['name'];
          $username = $_POST['username'];
          // $password = $_POST['password']; 
          $mobile_number = $_POST['phone'];

          // $status = "True";
          $status = $_POST['status'] === "true" ? 'true' : 'false';
          $district_name = $_POST['district_name'];

          // $stored_password = $row['password'];

          // if (password_verify($password, $stored_password)) {
               // $password = password_hash($password, PASSWORD_BCRYPT);


               $query = "UPDATE district_users  SET name=$1, username=$2, mobile_number=$3,  status=$4::BOOlean, district_name=$5 WHERE id=$6";

               $updateResult = pg_query_params($fsms_conn, $query, [$name, $username, $mobile_number, $status, $district_name, $id]);

               if ($updateResult) {
                    echo "<script>alert('You have updated a District User'); window.location.href='district_user_list.php';</script>";
               } else {
                    echo "<script>alert('Error updating District User');</script>";
               }
          // } else {
          //      echo "<script>alert('Incorrect password! Update failed.');</script>";
          // }
     }
} else {
     echo "No admin found";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Edit District Users</title>
     <link rel="stylesheet" href="../assets/add_admin.css">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
     <?php include('../includes/process.php'); ?>
     <?php include('../includes/sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>
     


     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-pen-to-square"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>Edit District Users</h2>
               </a>
          </div>

          <div class="form">
               <form method="POST">
                    <label>Name</label><br>
                    <input type="text" name="name" required
                         value="<?php echo htmlspecialchars($row['name']); ?>"><br>

                    <label>Username</label><br>
                    <input type="text" name="username" required
                         value="<?php echo htmlspecialchars($row['username']); ?>"><br>


                    <label>Phone</label><br>
                    <input type="tel" name="phone" required maxlength="10" pattern="\d{10}"
                         value="<?php echo htmlspecialchars($row['mobile_number']); ?>"><br>

                    <label>Status</label><br>
                    <select name="status" required>
                         <option value="true" <?php echo ($row['status'] === 't' || $row['status'] == true) ? "selected" : ""; ?>>Active
                         </option>
                         <option value="false" <?php echo ($row['status'] === 'f' || $row['status'] == false) ? "selected" : ""; ?>>Deactive
                         </option>
                    </select><br>


                    <label for="district">Select District</label>
                    <select name="district_name" id="district" required>
                         <option value="">-- Select District --</option>
                         <?php
                         $districts = [
                              "Baksa",
                              "Barpeta",
                              "Biswanath",
                              "Bongaigaon",
                              "Cachar",
                              "Charaideo",
                              "Chirang",
                              "Darrang",
                              "Dhemaji",
                              "Dhubri",
                              "Dibrugarh",
                              "Goalpara",
                              "Golaghat",
                              "Hailakandi",
                              "Hojai",
                              "Jorhat",
                              "Kamrup",
                              "Kamrup Metropolitan",
                              "Karbi Anglong",
                              "Karimganj",
                              "Kokrajhar",
                              "Lakhimpur",
                              "Majuli",
                              "Morigaon",
                              "Nagaon",
                              "Nalbari",
                              "Dima Hasao",
                              "Sivasagar",
                              "Sonitpur",
                              "South Salmara-Mankachar",
                              "Tinsukia",
                              "Udalguri",
                              "West Karbi Anglong",
                              "Tamulpur"
                         ];

                         foreach ($districts as $district) {
                              $selected = ($row['district_name'] == $district) ? "selected" : "";
                              echo "<option value=\"$district\" $selected>$district</option>";
                         }
                         ?>
                    </select><br><br>

                    <!-- <label>Password</label><br>
                    <input type="password" name="password" required><br> -->

                    <div class="form-btn">
                         <button type="submit">Update User</button>
                    </div>
               </form>
          </div>
     </div>
</body>

</html>
