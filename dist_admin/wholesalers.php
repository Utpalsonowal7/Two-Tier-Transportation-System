<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}


$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;




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


$total_query = "SELECT COUNT(*) AS total FROM wholesalers where district_id = $districtId";

$total_result = pg_query($master_conn, $total_query);
if (!$total_result) {
     die("Count query failed: " . pg_last_error($admin_conn));
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
     <link rel="stylesheet" href="../assets/add_admin.css">
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

     <style>
          .custom-modal {
               display: none;
               position: fixed;
               z-index: 1000;
               top: 0;
               left: 0;
               width: 100%;
               height: 100%;
               overflow: auto;
               background-color: rgba(0, 0, 0, 0.5);
               animation: fadeIn 0.4s ease-in-out;
          }

          .custom-modal-content {
               position: relative;
               background-color: #ffffff;
               margin: 100px auto;
               padding: 30px 40px;
               border-radius: 12px;
               width: 60%;
               max-width: 600px;
               box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 0 10px rgba(0, 123, 255, 0.2);
               animation: scaleUp 0.4s ease-in-out;
               font-family: 'Segoe UI', sans-serif;
               color: #333;
               text-align: center;
          }

          .custom-modal-content h2 {
               margin: 0 0 10px;
               font-size: 24px;
               color: #007BFF;
          }

          .custom-modal-close {
               position: absolute;
               top: 12px;
               right: 16px;
               font-size: 28px;
               font-weight: bold;
               color: #666;
               cursor: pointer;
               transition: color 0.3s ease;
          }

          .custom-modal-close:hover {
               color: red;
               transform: scale(1.2);
          }

          .loader {
               border: 6px solid #f3f3f3;
               border-top: 6px solid #3498db;
               border-radius: 50%;
               width: 40px;
               height: 40px;
               animation: spin 1s linear infinite;
               margin: 30px auto;
          }

          @keyframes spin {
               0% {
                    transform: rotate(0deg);
               }

               100% {
                    transform: rotate(360deg);
               }
          }


          @keyframes fadeIn {
               from {
                    opacity: 0;
               }

               to {
                    opacity: 1;
               }
          }

          @keyframes scaleUp {
               from {
                    transform: scale(0.8);
                    opacity: 0;
               }

               to {
                    transform: scale(1);
                    opacity: 1;
               }
          }
     </style>

</head>

<body>

     <?php include("../includes/dist_sidebar.php"); ?>
     <?php include("../includes/header.php"); ?>
     <?php include("../includes/encryption.php"); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fas fa-warehouse"></i></span>
               <h3 class="slash">/</h3>
               <a href="#">
                    <h2>List of Wholesalers</h2>
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
                              <th>Id</th>
                              <th>Name</th>
                              <th>Latitude</th>
                              <th>Longitude</th>
                              <th>Location</th>
                              <th>Address</th>
                              <th>Action</th>
                         </tr>
                    </thead>

                    <?php
                    $query = "SELECT * FROM wholesalers  WHERE district_id = $districtId  ORDER BY name   ASC ";
                    $result = pg_query($master_conn, $query);
                    if (!$result) {
                         die("Query failed: " . pg_last_error($admin_conn));
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


                                   <!-- <td><?php echo ($row['status'] === 't') ? 'Active' : 'Inactive'; ?></td> -->
                                   <td>
                                        <div class="button-group">
                                             <button class="update-btn"><a
                                                       href="edit_wholesaler.php?id=<?php echo urldecode(encrypt_id($row['serial_no'])); ?>">Update</a></button>
                                             <button class="delete-btn"><a
                                                       href="delete_wholesaler.php?id=<?php echo $row['serial_no']; ?>"
                                                       onclick="return confirm('Are you sure want to delete this    wholesaler?')">Delete</a></button>
                                             <button class="view-btn" data-id="<?php echo $row['serial_no']; ?>">View
                                                  More</button>
                                        </div>
                                   </td>
                              </tr>
                              <?php
                              $i++;
                         }
                         ?>
                    </tbody>
               </table>

               <div id="customModal" class="custom-modal">
                    <div class="custom-modal-content">
                         <span class="custom-modal-close" id="closeModal">&times;</span>
                         <h2>Wholesaler Details</h2>
                         <!-- <div id="custom-modal-body">Loading...</div> -->
                         <div id="custom-modal-body">
                              <div class="loader"></div>
                         </div>

                    </div>
               </div>

          </div>


     </div>
     <script>
          document.querySelectorAll('.view-btn').forEach(button => {
               button.addEventListener('click', function () {
                    const serialNo = this.getAttribute('data-id');
                    const modal = document.getElementById('customModal');
                    const modalBody = document.getElementById('custom-modal-body');


                    modal.style.display = "block";
                    // modalBody.innerHTML = "Loading...";
                    modalBody.innerHTML = `<div class="loader"></div>`;

                    const timeId = setTimeout(() => {
                         modal.style.display = "none";
                         modalBody.innerHTML = "";
                         alert("The data is taking longer to load, please wait a moment.");

                    }, 50000);


                    fetch(`get_wholesaler.php?id=${serialNo}`)
                         .then(response => response.text())
                         .then(data => {
                              clearTimeout(timeId)
                              modalBody.innerHTML = data;
                         })
                         .catch(err => {
                              clearTimeout(timeId)
                              if (modal.style.display !== "none") {
                                   modalBody.innerHTML = 'The data is taking longer to load, please wait a moment.';
                              }
                         });
               });
          });


          document.getElementById('closeModal').addEventListener('click', () => {
               document.getElementById('customModal').style.display = "none";
          });

          window.onclick = function (event) {
               const modal = document.getElementById('customModal');
               if (event.target === modal) {
                    modal.style.display = "none";
               }
          }

          //section for DataTable
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
                              title: ' Wholesalers List',
                              exportOptions: {
                                   columns: ':not(:last-child)'
                              }
                         },
                         {
                              extend: 'pdfHtml5',
                              title: ' Wholesalers List',
                              orientation: 'landscape',
                              pageSize: 'A4',
                              exportOptions: {
                                   columns: ':not(:last-child)'
                              }
                         },
                         {
                              extend: 'print',
                              title: ' Wholesalers List',
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