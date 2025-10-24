<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login']) || !isset($_SESSION['adminid'])) {
     echo "<script>alert('session expried, please log in again!');window.location.href='login.php';</script>";
     exit();
}



$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$show_data = !empty($start_date) && !empty($end_date);

$start_date_time = $start_date . ' 00:00:00';
$end_date_time = $end_date . ' 23:59:59';


$adminId = $_SESSION['adminid'];


$query = "SELECT district_id FROM district_admins WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, array($adminId));

if ($result && pg_num_rows($result) > 0) {
     $row = pg_fetch_assoc($result);
     $districtId = $row['district_id'];

} else {
     echo "District not found!";
     exit();
}


if ($show_data) {
     $query = "SELECT  wholesaler_name, retailer_name, commodity, quantity, rate, distance, created_at 
              FROM transport_tier2
              WHERE created_at BETWEEN '$start_date_time' AND '$end_date_time'
          and district_id = '$districtId'
              ORDER BY wholesaler_name";

     $result = pg_query($master_conn, $query);

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
     <meta name="description" content="">
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <title>Transport Data with Total Price</title>
     <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
     <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
     <style>
          body {
               font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
               margin: 0;
               padding: 0;
               background: #f4f6f9;
          }

          .container {
               width: 95%;
               max-width: 1200px;
               margin: 30px auto;
               padding: 20px;
               background: #fff;
               border-radius: 10px;
               box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
          }

          h2 {
               text-align: center;
               margin-bottom: 25px;
               color: #333;
          }

          form {
               display: flex;
               flex-wrap: wrap;
               justify-content: center;
               gap: 15px;
               margin-bottom: 30px;
          }

          input[type="date"] {
               padding: 10px;
               border-radius: 6px;
               border: 1px solid #ccc;
               font-size: 14px;
          }

          button {
               padding: 10px 20px;
               background-color: #007BFF;
               color: white;
               border: none;
               border-radius: 6px;
               font-weight: bold;
               cursor: pointer;
               transition: background-color 0.3s;
          }

          button:hover {
               background-color: #0056b3;
          }
/* 
          table {
               width: 100%;
               border-collapse: collapse;
          } */

          /* th,
          td {
               padding: 12px;
               border: 1px solid #e0e0e0;
               text-align: center;
          }

          th {
               background-color: #343a40;
               color: #fff;
          }

          tr:nth-child(even) {
               background-color: #f1f1f1;
          }

          tr:nth-child(odd) {
               background-color: #615b5bff;
          } */

          .total {
               font-weight: bold;
               color: green;
          }

          .dashboard-link {
               text-align: right;
               margin-bottom: 20px;
          }

          .dashboard-link a {
               background-color: #28a745;
               color: white;
               padding: 10px 16px;
               text-decoration: none;
               border-radius: 6px;
               font-weight: bold;
               transition: background-color 0.3s;
          }

          .dashboard-link a:hover {
               background-color: #1e7e34;
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

          .hidden-btn {
            display: none;
        }

          @media (max-width: 768px) {
               form {
                    flex-direction: column;
                    align-items: center;
               }

               input[type="date"],
               button {
                    width: 100%;
                    max-width: 300px;
               }
          }
     </style>
</head>

<body>

     <div class="container">
          <div class="dashboard-link">
               <a href="dist_dashboard.php">Go to Dashboard</a>
          </div>

          <h2>Tier 2 Transport Report</h2>

          <form method="GET" action="">
               <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
               <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
               <button type="submit">Show Data</button>
          </form>

          <?php if ($show_data): ?>


               <!-- <h2>Records from
                    <?php echo htmlspecialchars($start_date); ?> to
                    <?php echo htmlspecialchars($end_date); ?>
               </h2> -->

               <table id="report-table" class="display nowrap" tyle="width:100%">
                    <thead>
                         <tr>
                              <th>Date</th>
                              <th>Wholesaler</th>
                              <th>Retailer</th>
                              <th>Commodity</th>
                              <th>Quantity(In Quintals)</th>
                              <th>Rate (₹)</th>
                              <th>Distance(In KM)</th>
                              <th>Total Price (₹)</th>
                         </tr>
                    </thead>

                    <tbody>
                         <?php
                         $grand_total = 0;
                         while ($row = pg_fetch_assoc($result)) {
                              $quantity = (float) $row['quantity'];
                              $rate = (float) $row['rate'];
                              $total = $quantity * $rate;
                              $grand_total += $total;
                              $created_at = date("Y-m-d", strtotime($row['created_at']));

                              echo "<tr>
                        <td>{$created_at}</td>
                        <td>{$row['wholesaler_name']}</td>
                        <td>{$row['retailer_name']}</td>
                        <td>{$row['commodity']}</td>
                        <td>{$quantity}</td>
                        <td>₹{$rate}</td>
                        <td>{$row['distance']}</td>
                        <td class='total'>₹" . number_format($total, 2) . "</td>
                    </tr>";
                         }
                         ?>
                    </tbody>

                    <tfoot>
                         <tr>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td style="text-align: right; font-weight: bold;">Grand Total:</td>
                              <td class="total">₹<?php echo number_format($grand_total, 2); ?></td>
                         </tr>
                    </tfoot>

               </table>


               <button onclick="downloadExcel()" class="download-btn">
                    Download Excel Report
               </button>
               <button onclick="downloadPdf()" class="download-btn">
                    Download Pdf Report
               </button>
          <?php endif; ?>
     </div>

     <!-- <script>

          const start_date = "<?php echo htmlspecialchars($start_date); ?>";
          const end_date = "<?php echo htmlspecialchars($end_date); ?>";

          function downloadExcel() {
               const table = document.getElementById("report-table");
               if (!table) {
                    alert("No data to export.");
                    return;
               }


               const wb = XLSX.utils.book_new();


               const sheet = XLSX.utils.table_to_sheet(table, { raw: true });


               const tableData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

               const titleRow = [`                                                                                            Monthly Transport Report Tier 2 from ${start_date} to ${end_date}`];
               const blankRow = [];

               const finalData = [titleRow, blankRow, ...tableData];

               const finalSheet = XLSX.utils.aoa_to_sheet(finalData);

               finalSheet['!merges'] = [{
                    s: { r: 0, c: 0 },
                    e: { r: 0, c: tableData[0].length - 1 }
               }];

               const titleCell = finalSheet['A1'];
               if (titleCell) {
                    titleCell.s = {
                         font: { bold: true, sz: 20 },
                         alignment: { horizontal: 'center', vertical: 'center' },
                         border: {
                              top: { style: 'thick' },
                              bottom: { style: 'thick' },
                              left: { style: 'thick' },
                              right: { style: 'thick' },
                         },
                         fill: {
                              fgColor: { rgb: "D9EAD3" }
                         }
                    };
               }

               const headerRowIndex = 2;
               for (let col = 0; col < tableData[0].length; col++) {
                    const cellAddress = { r: headerRowIndex, c: col };
                    const cellRef = XLSX.utils.encode_cell(cellAddress);
                    if (finalSheet[cellRef]) {
                         finalSheet[cellRef].s = {
                              font: { bold: true },
                              alignment: { horizontal: 'center' },
                              border: {
                                   top: { style: 'thick' },
                                   bottom: { style: 'thick' },
                                   left: { style: 'thick' },
                                   right: { style: 'thick' },
                              },
                              fill: {
                                   fgColor: { rgb: "B6D7A8" }
                              }
                         };
                    }
               }


               for (let row = 1; row < finalData.length; row++) {
                    for (let col = 0; col < finalData[row].length; col++) {
                         const cellAddress = { r: row, c: col };
                         const cellRef = XLSX.utils.encode_cell(cellAddress);
                         if (finalSheet[cellRef]) {
                              finalSheet[cellRef].s = {
                                   border: {
                                        top: { style: 'thick' },
                                        bottom: { style: 'thick' },
                                        left: { style: 'thick' },
                                        right: { style: 'thick' },
                                   }
                              };
                         }
                    }
               }


               finalSheet['!cols'] = new Array(tableData[0].length).fill({ wch: 18 });

               XLSX.utils.book_append_sheet(wb, finalSheet, "Monthly Report");


               try {
                    XLSX.writeFile(wb, "Monthly_Transport_Report.xlsx");
               } catch (error) {
                    alert("Error downloading the file. Please check if the browser supports file downloads.");
               }
          }


     </script> -->

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
                              title: 'Monthly Transport Report Tier 2 from <?php echo $start_date . " to " . $end_date; ?>',
                              footer: true,
                              text: 'Hidden Excel Export',
                              className: 'hidden-btn'
                         },
                         {
                              extend: 'pdfHtml5',
                              title: 'Monthly Transport Report Tier 2 from <?php echo $start_date . " to " . $end_date; ?>',
                              footer: true,
                              text: 'Hidden Pdf Export',
                              className: 'hidden-btn'
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