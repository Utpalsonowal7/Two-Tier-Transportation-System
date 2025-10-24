<?php
include("../includes/dbconnection.php");

if (isset($_POST['district_id'])) {
     $district_id = $_POST['district_id'];
     $query = "SELECT serial_no, name FROM wholesalers WHERE district_id = $1";
     $result = pg_query_params($master_conn, $query, [$district_id]);

     if ($result && pg_num_rows($result) > 0) {
          echo '<option value="">-- Select Wholesaler --</option>';
          while ($wholesaler = pg_fetch_assoc($result)) {
               echo "<option value=\"" . htmlspecialchars($wholesaler['serial_no']) . "\">" . htmlspecialchars($wholesaler['name']) . "</option>";
          }
     } else {
          echo '<option value="">No wholesalers found</option>';
     }
}
?>      