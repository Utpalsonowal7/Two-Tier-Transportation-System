<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
     exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>District Admin List</title>
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/list_admin.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.1/css/buttons.dataTables.min.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
     <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.print.min.js"></script>

</head>

<body>
     <?php include("../includes/process.php"); ?>
     <?php include("../includes/sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>
     <?php include('../includes/encryption.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-circle-user"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>List of District Users</h2>
               </a>
          </div>

          <div class="table-container">
               <div class="table-header">
                    <ul>
                         <li id="exportButtons"></li>
                    </ul>
               </div>

               <table id="dataTable">
                    <thead>
                         <tr>
                              <th>Sl No</th>
                              <th>District Id</th>
                              <th>District Name</th>
                              <th>Id</th>
                              <th>Name</th>
                              <th>Username</th>
                              <th>Phone</th>
                              <th>Status</th>
                              <th>Action</th>
                         </tr>
                    </thead>

                    <?php
                    $query = "SELECT * FROM district_users ORDER BY username ASC";
                    $result = pg_query($fsms_conn, $query);
                    if (!$result) {
                         die("Query failed: " . pg_last_error($fsms_conn));
                    }

                    $i = 1;
                    ?>

                    <tbody>
                         <?php
                         while ($row = pg_fetch_assoc($result)) {
                              ?>
                              <tr>
                                   <td><?php echo $i; ?></td>
                                   <td><?php echo $row['district_id']; ?></td>
                                   <td><?php echo $row['district_name'] ?></td>
                                   <td><?php echo $row['id']; ?></td>
                                   <td><?php echo $row['name']; ?></td>
                                   <td><?php echo $row['username']; ?></td>
                                   <td><?php echo $row['mobile_number']; ?></td>
                                   <td><?php echo ($row['status'] === 't') ? 'Active' : 'Inactive'; ?></td>
                                   <td>
                                        <div class="button-group">
                                             <button class="update-btn"><a
                                                       href="edit_district_user.php?id=<?php echo urlencode(encrypt_id($row['id'])); ?>">Update</a></button>
                                             <button class="delete-btn"><a
                                                       href="delete_district_user.php?id=<?php echo $row['id']; ?>"
                                                       onclick="return confirm('Are you sure wantto delete this user?')">Delete</a></button>
                                        </div>
                                   </td>
                              </tr>
                              <?php
                              $i++;
                         }
                         ?>
                    </tbody>
               </table>
          </div>

          <script>
               $(document).ready(function () {
                    if ($.fn.dataTable.isDataTable('#dataTable ')) {
                         $('#dataTable ').dataTable().destroy();
                    }

                    let table = $('#dataTable ').DataTable({
                         paging: true,
                         lengthChange: true,
                         searching: true,
                         ordering: true,
                         info: true,
                         autoWidth: false,
                         responsive: true,
                         buttons: [
                              {
                                   extend: 'excelHtml5',
                                   title: 'Dist User List',
                                   exportOptions: {
                                        columns: ':not(:last-child)'
                                   }
                              },
                              {
                                   extend: 'pdfHtml5',
                                   title: 'Dist User List',
                                   orientation: 'landscape',
                                   pageSize: 'A4',
                                   exportOptions: {
                                        columns: ':not(:last-child)'
                                   }
                              },
                              {
                                   extend: 'print',
                                   title: 'Dist User List',
                                   exportOptions: {
                                        columns: ':not(:last-child)'
                                   }
                              }
                         ]
                    });


                    table.buttons().container().appendTo('#exportButtons');
               })
          </script>

          <script src="../js/toggle.js"></script>
</body>

</html>