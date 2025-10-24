<?php
$id = $_SESSION['adminid'];

$query = "SELECT name, status FROM systemadmin WHERE id = $id";
$result = pg_query($fsms_conn, $query);

if ($result && pg_num_rows($result) > 0) {
     $admin = pg_fetch_assoc($result);

     $role = $admin['status'];
     $status = ($admin['status'] === 't') ? 'active' : 'inactive';
} else {
     $role = "Unknown Admin";
     $status = "inactive";
}
?>



