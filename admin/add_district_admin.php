<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
    echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $mobile_number = $_POST['phone'];
    $otp = rand(100000, 999999);
    $status = "True";

    $district_id = $_POST['district_id'];


    $district_query = "SELECT name FROM district WHERE id = $1";
    $district_result = pg_query_params($fsms_conn, $district_query, [$district_id]);

    if ($district_result && pg_num_rows($district_result) > 0) {
        $district = pg_fetch_assoc($district_result);
        $district_name = $district['name'];
    } else {

        echo "<p style='color: red;'>Error: Invalid district selected.</p>";
        exit();
    }


    // $checkQuery = pg_query_params($admin_conn, "SELECT 1 FROM district_admins WHERE username = $1 " ,   array($username));

    // if (pg_num_rows($checkQuery) == 0) {

    // $query = pg_query_params($admin_conn, "INSERT INTO district_admins (name, username, password, mobile_number, otp, status, district_id, district_name) 
    //           VALUES ($1, $2, $3, $4, $5, $6, $7, $8)", array($name, $username, $password, $mobile_number, $otp, $status, $district_id, $district_name));

    // // $result = pg_query_params($admin_conn, $query, [$name, $username, $password, $mobile_number, $otp, $status, $district_id, $district_name]);

    // if ($query) {
    //     echo "<script>alert('You have added a District admin');</script>";
    // } else {
    //     echo "<p style='color: red;'>Error: " . pg_last_error($admin_conn) . "</p>";
    // }
    // } else {
    //     echo "<script>alert('Credentails already exists.');</script>";
    // }

    $checkQuery = pg_query_params(
        $fsms_conn,
        "SELECT 1 FROM district_admins WHERE username = $1 OR mobile_number = $2",
        array($username, $mobile_number)
    );

    if (pg_num_rows($checkQuery) == 0) {

        $query = pg_query_params(
            $fsms_conn,
            "INSERT INTO district_admins (name, username, password, mobile_number, otp, status, district_id, district_name) 
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8)",
            array($name, $username, $password, $mobile_number, $otp, $status, $district_id, $district_name)
        );

        if ($query) {
            echo "<script>alert('You have added a District admin'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<p style='color: red;'>Error: " . pg_last_error($admin_conn) . "</p>";
        }

    } else {
        echo "<script>alert('username or mobile number already exists.');</script>";
    }





}


$district_query = "SELECT * FROM district";
$district_result = pg_query($fsms_conn, $district_query);

if (!$district_result) {
    echo "<p style='color: red;'>Error fetching districts: " . pg_last_error($fsms_conn) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
    <title>Add District Admin</title>
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
            <span class="icon"><i class="fa-solid fa-circle-user"></i></span>
            <h3 class="slash">/</h3>
            <a href="#">
                <h2>Add District Admin</h2>
            </a>
        </div>

        <div class="form">
            <form method="POST">
                <label>Name</label><br>
                <input type="text" name="name" required><br><br>

                <label>Username</label><br>
                <input type="text" name="username" required><br><br>

                <label>Phone</label><br>
                <input type="tel" name="phone" required maxlength="10" pattern="\d{10}"><br><br>

                <label for="district">Select District</label>
                <select name="district_id" id="district" required>
                    <option value="">-- Select District --</option>
                    <?php
                    if ($district_result) {
                        while ($district = pg_fetch_assoc($district_result)) {
                            echo "<option value=\"" . htmlspecialchars($district['id']) . "\">" . htmlspecialchars($district['name']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No districts available</option>";
                    }
                    ?>
                </select><br><br>

                <label>Password</label><br>
                <input type="password" name="password" id="password" required>
                <div class="validation" id="passwordValidation">
                    <p>Password must contain at least one number and one uppercase and lowercase letter, and at least 10
                        characters</p>
                </div>
                <div class="form-btn">
                    <button type="submit">Add Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/valid.js"> </script>
</body>

</html>