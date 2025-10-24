<?php
include("../includes/dbconnection.php");

if (isset($_POST['district_id'])) {
     $district_id = $_POST['district_id'];
     $selected_wh_id = $_POST['selected_wholesaler_id'] ?? '';
     $selected_distance = $_POST['selected_distance'] ?? '';

     $query = "SELECT serial_no, name FROM wholesalers WHERE district_id = $1";
     $result = pg_query_params($master_conn, $query, [$district_id]);

     if (!$result) {
          echo "<p style='color:red;'>Error fetching wholesalers.</p>";
          exit;
     }

     echo '<label>Select Nearest Wholesaler</label><br>';
     echo '<select name="nearest_wholesaler_id" required>';
     echo '<option value="">-- Select Wholesaler --</option>';

     while ($row = pg_fetch_assoc($result)) {
          $selected = ($row['serial_no'] == $selected_wh_id) ? 'selected' : '';
          echo '<option value="' . htmlspecialchars($row['serial_no']) . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
     }

     echo '</select><br><br>';

     echo '<label>Nearest Wholesaler Distance</label><br>';
     echo '<input type="number" name="nearest_wholesaler_distance" value="' . htmlspecialchars($selected_distance) . '" required><br><br>';
}
?>