<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session expired, please log in again');window.location.href='index.php';</script>";
     exit();
}

$limit = 10;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;


$total_query = "SELECT COUNT(*) AS total FROM warehouse_wholesale_map ";
$total_result = pg_query($master_conn, $total_query);
if (!$total_result) {
     die("Count query failed: " . pg_last_error($master_conn));
}
$total_row = pg_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);
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
               <span class="icon"><i class="fas fa-link"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>List of Mapped Wholesalers</h2>
               </a>

          </div>

          <div class="table-container">

               <div class="table-header">
                    <ul>
                         <li id="exportButtons"></li>
                         <li></li>
                    </ul>
               </div>

               <table id="WholesaleMap">
                    <thead>
                         <tr>
                              <th>Id</th>
                              <th>District_Id</th>
                              <th>District_Name</th>
                              <th>Warehouse Name</th>
                              <th>Wholesaler Name</th>
                              <th>Transport Rate</th>
                              <th>Distance</th>
                              <th>Action</th>
                         </tr>
                    </thead>

                    <?php

                    $query = "SELECT * FROM warehouse_wholesale_map ORDER BY id ASC";
                    $result = pg_query($master_conn, $query);
                    if (!$result) {
                         die("Query failed: " . pg_last_error($master_conn));
                    }
                    $i = 1;
                    ?>

                    <tbody>
                         <?php
                         while ($row = pg_fetch_assoc($result)) {
                              ?>
                              <tr>
                                   <td><?php echo $row['id']; ?></td>
                                   <td><?php echo $row['district_id']; ?></td>
                                   <td><?php echo $row['district_name']; ?></td>
                                   <td><?php echo $row['warehouse_name']; ?></td>
                                   <td><?php echo $row['wholesaler_name']; ?></td>
                                   <td><?php echo $row['transport_rate']; ?></td>
                                   <td><?php echo $row['distance']; ?></td>
                                   <td>
                                        <div class="button-group">
                                             <button class="update-btn"><a
                                                       href="edit_wholesaler_map.php?id=<?php echo urlencode(encrypt_id($row['id'])); ?>">Update</a></button>
                                             <button class="delete-btn"><a
                                                       href="delete_warehouse_map.php?id=<?php echo $row['id']; ?>"
                                                       onclick="return confirm('are you sure want to delete this mapping');">Delete</a></button>
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
          </div>


          <script>
                 $(document).ready(function () {
                    if ($.fn.dataTable.isDataTable('#WholesaleMap')) {
                         $('#WholesaleMap').DataTable().destroy();
                    }

                    let table = $('#WholesaleMap').DataTable({
                         paging: true,
                         lengthChange: true,
                         searching: true,
                         ordering: true,
                         info: true,
                         autoWidth: false,
                         buttons: [
                              {
                                   extend: 'excelHtml5',
                                   title: 'WholesaleMapped List',
                                   exportOptions: {
                                        columns: ':not(:last-child)'
                                   }
                              },
                              {
                                   extend: 'pdfHtml5',
                                   title: 'WholesaleMapped List',
                                   orientation: 'landscape',
                                   pageSize: 'A4',
                                   exportOptions: {
                                        columns: ':not(:last-child)'
                                   }
                              },
                              {
                                   extend: 'print',
                                   title: 'WholesaleMapped List',
                                   exportOptions: {
                                        columns: ':not(:last-child)'
                                   }
                              }
                         ]
                    });


                    table.buttons().container().appendTo('#exportButtons');
               });

          </script>
</body>

</html>