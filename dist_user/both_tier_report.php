<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('Session expired, please log in again!'); window.location.href='login.php';</script>";
     exit;
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$show_data = !empty($start_date) && !empty($end_date);

$start_date_time = $start_date . ' 00:00:00';
$end_date_time = $end_date . ' 23:59:59';

$adminId = $_SESSION['adminid'];

$query = "SELECT district_id FROM district_users WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, [$adminId]);

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $districtId = $row['district_id'];
} else {
     echo "District not found!";
     exit();
}

if ($show_data) {
     $query = "SELECT  district_name, wholesaler_name,  contractor_name,
        allot_commodity, allot_quantity, allot_sub_commodity, allot_sub_quantity,
        allot_commodity_1, allot_quantity_1, allot_sub_commodity_1, allot_sub_quantity_1,
        allotment_total_quantity, lifting_commodity, lifting_quantity,
        lifting_sub_commodity, lifting_sub_quantity, lifting_commodity_1, lifting_quantity_1,
        lifting_sub_commodity_1, lifting_sub_quantity_1, total_lifting_quantity,
        tier1_rate, tier2_rate, total_tier1_and_tier2_rate, central_share,
        total_bill_amt, state_share, avg_govt_rate, total_govt_amt, 
        avg_state_share_rate, state_share_to_be_paid, total_net_pay, 
        state_share_due, report_added_at
              FROM transport_report
              WHERE report_added_at BETWEEN $1 AND $2
              AND district_id = $3
              ORDER BY wholesaler_name, report_added_at ASC";

     $result = pg_query_params($master_conn, $query, [$start_date_time, $end_date_time, $districtId]);
     $district_name = '';
     $average_govt_rate = 0;
     $average_state_share_rate = 0;

     if ($result && pg_num_rows($result) > 0) {

          $firstRow = pg_fetch_assoc($result);

          $district_name = $firstRow['district_name'];
          $average_govt_rate = $firstRow['avg_govt_rate'];
          $average_state_share_rate = $firstRow['avg_state_share_rate'];


          pg_result_seek($result, 0);
     }


     if (!$result) {
          echo "Error fetching data: " . pg_last_error($master_conn);
          exit;
     }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <title>Monthly Transport Report</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

     <style>
          body {
               font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
               background: #f4f6f9;
               margin: 0;
               padding: 0;
          }

          .container {
               width: 95%;
               max-width: 1600px;
               margin: 30px auto;
               padding: 20px;
               background: #fff;
               border-radius: 10px;
               box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
          }

          .dashboard-link a {
               text-decoration: none;
               color: #007BFF;
               font-size: 1.5rem;
               font-weight: bold;
               display: block;
               margin-bottom: 20px;
               text-align: right;
          }

          h2 {
               text-align: center;
               color: #333;
               margin-bottom: 25px;
          }

          form {
               display: flex;
               justify-content: center;
               flex-wrap: wrap;
               gap: 15px;
               margin-bottom: 30px;
          }

          input[type="date"],
          button {
               padding: 10px;
               border-radius: 6px;
               border: 1px solid #ccc;
               font-size: 14px;
          }

          button[type="submit"] {
               background-color: #007BFF;
               color: white;
               font-weight: bold;
               cursor: pointer;
          }

          button:hover {
               background-color: #0056b3;
          }

          .download-btn {
               background: green;
               color: white;
               border: none;
               border-radius: 5px;
               padding: 10px 20px;
               margin-top: 20px;
               font-weight: bold;
               cursor: pointer;
          }

          .download-btn:hover {
               background: darkgreen;
          }

          .total {
               font-weight: bold;
               color: green;
          }

          .hidden-btn {
               display: none;
          }


          .report-header {
               display: flex;
               flex-wrap: wrap;
               justify-content: space-between;
               margin-bottom: 15px;
               border: 1px solid #ccc;
               padding: 10px;
               background: #f9f9f9;
          }

          .report-header div {
               flex: 1 1 45%;
               margin: 4px 0;
          }

          .report-header strong {
               color: #333;
          }

          table.dataTable {
               font-size: 12px;
               border-collapse: collapse;
          }

          table.dataTable thead th {
               white-space: normal !important;
               word-wrap: break-word;
               max-width: 120px;
               text-align: center;
               vertical-align: middle;
          }


          th,
          td {
               text-align: center;
               padding: 4px 6px;
          }

          th {
               background: #e6e6e6;
               border: 1px solid #444;
          }

          td {
               border: 1px solid #444;
          }

          tbody tr:nth-child(even) {
               background: #f9f9f9;
          }

          .scroll-container {
               overflow-x: auto;
          }

          #report-table {
               table-layout: auto;
               width: 100%;
               border-collapse: collapse;
          }

          .group-header {
               text-align: center;
               background: #dcdcdc;
               font-weight: bold;
               vertical-align: middle;
               height: 40px;
               border: 1px solid #444;
               white-space: nowrap;
          }

          .vertical {
               writing-mode: vertical-rl;
               transform: rotate(180deg);
               text-align: center;
               vertical-align: middle;
               padding: 6px 10px;
               white-space: nowrap;
               font-size: 11px;
               height: 120px;
               width: max-content;

          }


          .scroll-container {
               overflow-x: auto;
               width: 100%;
          }

          thead th {
               background-color: #e6e6e6;
               border: 1px solid #444;
          }

          @media (max-width: 768px) {
               form {
                    flex-direction: column;
                    align-items: center;
               }
          }
     </style>
</head>

<body>
     <div class="container">
          <div class="dashboard-link">
               <a href="dashboard.php">Go to Dashboard</a>
          </div>

          <h2>MONTHLY STATEMENT OF TRANSPORTATION BILL OF GPSS/FPS OF FOOD GRAINS UNDER NFSA OF LAKHIMPUR DISTRICT FOR
               THE MONTH OF <?php
               $monthName = '';
               if (!empty($start_date)) {
                    $monthName = date('F Y', strtotime($start_date)); // e.g. "November 2025"
               }
               ?></h2>

          <form method="GET" action="">
               <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
               <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
               <button type="submit">Show Data</button>
          </form>

          <?php if ($show_data): ?>
               <div class="report-header">
                    <div><strong>District: </strong><?php echo htmlspecialchars($district_name); ?></div>
                    <?php
                    $monthName = '';
                    if (!empty($start_date)) {
                         $monthName = date('F Y', strtotime($start_date));
                    }
                    ?>
                    <div><strong>Month: </strong>
                         <?php echo htmlspecialchars($monthName); ?>
                    </div>

                    <div><strong>Average Govt. Rate: </strong> <?php echo htmlspecialchars($average_govt_rate); ?></div>
                    <div><strong>Average State Share Rate: </strong>
                         <?php echo htmlspecialchars($average_state_share_rate); ?></div>
               </div>
               <div class="scroll-container">
                    <table id="report-table" class="display nowrap" style="width:100%">
                         <thead>
                              <tr>
                                   <th class="vertical" rowspan="2">Sl. No</th>
                                   <th class="vertical" rowspan="2">Contractor Name</th>
                                   <th class="vertical" rowspan="2">Name of GPSS/WCSS</th>
                                   <th colspan="5">ALLOTMENT</th>
                                   <th colspan="5">LIFTING</th>
                                   <th class="vertical" rowspan="2">Tier 1 Rate</th>
                                   <th class="vertical" rowspan="2">Tier 2 Rate</th>
                                   <th class="vertical" rowspan="2">Total Tier Rate</th>
                                   <th class="vertical" rowspan="2">Central Share (₹)</th>
                                   <th class="vertical" rowspan="2">State Share (₹)</th>
                                   <th class="vertical" rowspan="2">Total Bill (₹)</th>
                                   <th class="vertical" rowspan="2">Total Gvt amt at an avg
                                        of <?php echo htmlspecialchars($average_govt_rate); ?>%</th>
                                   <th class="vertical" rowspan="2">State should to be paid @
                                        <?php echo htmlspecialchars($average_state_share_rate); ?>%
                                   </th>
                                   <th class="vertical" rowspan="2">Total Net Pay (₹)</th>
                                   <th class="vertical" rowspan="2">State Share Due (₹)</th>
                              </tr>
                              <tr>
                                   <th>AAY Rice</th>
                                   <th>ADL AAY</th>
                                   <th>PH Rice</th>
                                   <th>ADL PH</th>
                                   <th>TOTAL</th>

                                   <th>AAY Rice</th>
                                   <th>ADL AAY</th>
                                   <th>PH Rice</th>
                                   <th>ADL PH</th>
                                   <th>TOTAL</th>
                              </tr>
                         </thead>
                         <tbody>
                              <?php
                              $sl_no = 1;
                              while ($row = pg_fetch_assoc($result)) {
                                   echo "<tr>";
                                   echo "<td>{$sl_no}</td>";
                                   echo "<td>{$row['contractor_name']}</td>";
                                   echo "<td>{$row['wholesaler_name']}</td>";
                                   // echo "<td>{$row['allot_commodity']}</td>";
                                   echo "<td>{$row['allot_quantity']}</td>";
                                   // echo "<td>{$row['allot_sub_commodity']}</td>";
                                   echo "<td>{$row['allot_sub_quantity']}</td>";
                                   // echo "<td>{$row['allot_commodity_1']}</td>";
                                   echo "<td>{$row['allot_quantity_1']}</td>";
                                   // echo "<td>{$row['allot_sub_commodity_1']}</td>";
                                   echo "<td>{$row['allot_sub_quantity_1']}</td>";
                                   echo "<td>{$row['allotment_total_quantity']}</td>";
                                   // echo "<td>{$row['lifting_commodity']}</td>";
                                   echo "<td>{$row['lifting_quantity']}</td>";
                                   // echo "<td>{$row['lifting_sub_commodity']}</td>";
                                   echo "<td>{$row['lifting_sub_quantity']}</td>";
                                   // echo "<td>{$row['lifting_commodity_1']}</td>";
                                   echo "<td>{$row['lifting_quantity_1']}</td>";
                                   // echo "<td>{$row['lifting_sub_commodity_1']}</td>";
                                   echo "<td>{$row['lifting_sub_quantity_1']}</td>";
                                   echo "<td>{$row['total_lifting_quantity']}</td>";

                                   echo "<td>{$row['tier1_rate']}</td>";
                                   echo "<td>{$row['tier2_rate']}</td>";
                                   echo "<td>{$row['total_tier1_and_tier2_rate']}</td>";
                                   echo "<td>{$row['central_share']}</td>";
                                   echo "<td>{$row['state_share']}</td>";
                                   echo "<td>{$row['total_bill_amt']}</td>";
                                   echo "<td>{$row['total_govt_amt']}</td>";
                                   echo "<td>{$row['state_share_to_be_paid']}</td>";
                                   echo "<td>{$row['total_net_pay']}</td>";
                                   echo "<td>{$row['state_share_due']}</td>";
                                   echo "</tr>";

                                   $sl_no++;
                              }
                              ?>
                         </tbody>
               </div>
               <!-- <tfoot>
                    <tr>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td></td>
                         <td style="text-align:right; font-weight:bold;">Grand Total:</td>
                         <td class="total">₹<?php echo number_format($grand_total, 2); ?></td>
                    </tr>
               </tfoot> -->
               </table>

               <!-- <button onclick="downloadExcel()" class="download-btn">Download Excel Report</button> -->
               <form method="POST" action="excel_report_btoh_tier.php" style="display:inline;">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    <button type="submit" class="download-btn">Download Excel Report</button>
               </form>
               <!-- <form method="POST" action="export_excel_real.php" style="display:inline;">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    <button type="submit" class="download-btn">Download Excel Report</button>
               </form> -->

               <!-- <button onclick="downloadPdf()" class="download-btn">Download PDF Report</button> -->
          <?php endif; ?>
     </div>


     <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
     <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

     <script>
          $(document).ready(function () {
               window.table = $('#report-table').DataTable({
                    dom: 'Blfrtip',
                    buttons: [
                         {
                              extend: 'excelHtml5',
                              title: 'MONTHLY STATEMENT OF TRANSPORTATION BILL OF GPSS/FPS OF FOOD GRAINS UNDER NFSA OF LAKHIMPUR DISTRICT FOR THE MONTH OF <?php echo strtoupper($monthName); ?>',
                              footer: true,
                              text: 'Hidden Excel Export',
                              className: 'hidden-btn'
                         },
                         {
                              extend: 'pdfHtml5',
                              title: 'MONTHLY STATEMENT OF TRANSPORTATION BILL OF GPSS/FPS OF FOOD GRAINS UNDER NFSA OF LAKHIMPUR DISTRICT FOR THE MONTH OF <?php echo strtoupper($monthName); ?>',
                              footer: true,
                              text: 'Hidden Pdf Export',
                              className: 'hidden-btn',
                              customize: function (doc) {
                                   doc.content.unshift({
                                        image: 'data:image/png;base64,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',
                                        width: 50,
                                        backgroundColor: '#f2f2f2',
                                        alignment: 'center',
                                        margin: [0, 0, 0, 10]
                                   });

                              }
                         }
                    ],
                    paging: true,
                    searching: true,
                    ordering: true
               });

               $('.hidden-btn').hide();
          });

          function downloadExcel() {

               $('.buttons-excel').click();
          }
          function downloadPdf() {

               $('.buttons-pdf').click();
          }
     </script>
</body>

</html>