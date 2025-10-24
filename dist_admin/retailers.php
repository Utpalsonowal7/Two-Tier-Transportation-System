<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}

$adminId = $_SESSION['adminid'];


$query = "SELECT district_id FROM district_admins WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, array($adminId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $districtId = $row['district_id'];
     // var_dump($districtId);
} else {
     echo "District not found!";
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

     <?php include("../includes/dist_sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>
     <?php include('../includes/encryption.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-store"></i> </span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>List of Retailers</h2>
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
                              <th>S.No</th>
                              <th>Id</th>
                              <th>Name</th>
                              <th>Latitude</th>
                              <th>Longitude</th>
                              <th>Location</th>
                              <th>Address</th>
                              <th>Dst. WH</th>
                              <th>Nearest WH</th>
                              <th>Action</th>
                         </tr>
                    </thead>

                    <?php
                    $query = "SELECT * FROM retailers  WHERE district_id = $districtId  ORDER BY name   ASC";
                    $result = pg_query($master_conn, $query);
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
                                   <td><?php echo $row['serial_no']; ?></td>
                                   <td><?php echo $row['name']; ?></td>
                                   <td><?php echo $row['latitude']; ?></td>
                                   <td><?php echo $row['longitude']; ?></td>
                                   <td><?php echo $row['location']; ?></td>
                                   <td><?php echo $row['address']; ?></td>
                                   <td><?php echo $row['nearest_wholesaler_distance']; ?></td>
                                   <td><?php echo $row['nearest_wholesaler_name']; ?></td>

                                   <!-- <td><?php echo ($row['status'] === 't') ? 'Active' : 'Inactive'; ?></td> -->
                                   <td>
                                        <div class="button-group">
                                             <button class="update-btn"><a
                                                       href="edit_retailer.php?id=<?php echo urlencode(encrypt_id($row['serial_no'])); ?>">Update</a></button>
                                             <button class="delete-btn"><a
                                                       href="delete_retailer.php?id=<?php echo $row['serial_no']; ?>"
                                                       onclick="return confirm('Are you sure want to delete this retailer?')">Delete</a></button>
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
                              title: 'Retailers List',
                              exportOptions: {
                                   columns: ':not(:last-child)'
                              }
                         },
                         {
                              extend: 'pdfHtml5',
                              title: 'Retailers List',
                              orientation: 'landscape',
                              pageSize: 'A4',
                              exportOptions: {
                                   columns: ':not(:last-child)'
                              }
                         },
                         {
                              extend: 'print',
                              title: 'Retailers List',
                              exportOptions: {
                                   columns: ':not(:last-child)'
                              }
                         }
                    ]
               });


               table.buttons().container().appendTo('#exportButtons');
          })
     </script>
</body>

</html>