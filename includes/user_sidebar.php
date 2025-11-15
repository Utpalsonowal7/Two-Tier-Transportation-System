<?php
$id = (int) $_SESSION['adminid'];

$query = "SELECT name, status FROM district_users  WHERE id = $1";
$result = pg_query_params($fsms_conn, $query, [$id]);

if ($result && pg_num_rows($result) > 0) {
     $admin = pg_fetch_assoc($result);

     $role = $admin['status'];
     $status = ($admin['status'] === 't') ? 'active' : 'inactive';
} else {
     $role = "Unknown Admin";
     $status = "inactive";
}
?>



<aside class="sidebar">

     <div class="admin-profile">
          <!-- <img src="<?php echo $admin['profile_image']; ?>" alt="Admin Avatar"> -->

          <i class="fa-solid fa-user"></i>
          <h2>Welcome</h2>
          <h3><?php echo $admin['name']; ?></h3>
          <p class="status <?php echo $status; ?>">
               <span class="status-dot"></span>
               <?php echo ucfirst($status); ?>
          </p>


     </div>
     <?php if ($role == 't') { ?>
          <nav class="sidebar-nav user_sideBar">
               <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>

                    <li class="dropdown"><a href="Add_warehouse.php"><i class="fas fa-warehouse"></i> Add Warehouses<span><i
                                        class="fa-solid fa-plus"></i></span></a>
                         <!-- <ul class=" dropdown-menu">
                              <li><a href="add_district_user.php">Add District Users</a></li>
                              <li><a href="district_user_list.php">Show District Users</a></li>
                         </ul> -->
                    </li>

                    <!-- <li><a href="district_user_list.php"><i class="fas fa-user-shield"></i>District Users<i
                                   class="fa-solid fa-plus"></i></a></li> -->

                    <li class="dropdown"><a href="add_wholesaler.php"><i class="fas fa-warehouse"></i>Add wholesalers<span><i
                                        class="fa-solid fa-plus"></i></span></a>
                         <!-- <ul class=" dropdown-menu">
                         <li><a href="warehouses.php"><i class="fas fa-warehouse"></i> Warehouses</a></li>
                         <li><a href="wholesalers.php"><i class="fas fa-store"></i> Wholesalers</a></li>
                         <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li>
                         </ul> -->
                    </li>

                    <li class="dropdown"><a href="add_retailer.php"><i class="fas fa-warehouse"></i></i>Add Retailers<span><i
                                        class="fa-solid fa-plus"></i></span></a>
                         <!-- <ul class=" dropdown-menu">
                         <li><a href="warehouses.php"><i class="fas fa-warehouse"></i> Warehouse_wholesale Map</a></li>
                         <li><a href="wholesalers.php"><i class="fas fa-store"></i> Wholesale_Retailer Map</a></li>
                         <!-- <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li> 
                         </ul> -->
                    </li>

                    <li>
                         <a href="wholesale_map.php"><i class="fa-solid fa-link"></i></i>Map Wholesaler<span><i
                                        class="fa-solid fa-plus"></i></span></a>
                    </li>

                    <li>
                         <a href="retailer_map.php"><i class="fa-solid fa-link"></i></i>Map Retailers<span><i
                                        class="fa-solid fa-plus"></i></span></a>
                    </li>

                    <li class="dropdown"><a><i class="fa-solid fa-upload"></i>Import data
                              <span><i class="fa-solid fa-plus" onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li>
                                   <a href="import_warehouse_csv.php">Import Warehouse Data</a>
                              </li>
                              <li>
                                   <a href="import_wholesaler_csv.php">Import Wholesaler Data</a>
                              </li>
                              <li>
                                   <a href="import_retailer_csv.php">Import Retailer Data</a>
                              </li>
                              <li>
                                   <a href="add_fps_data.php">Import FPS Data</a>
                              </li>
                         </ul>
                    </li>

                    <li class="dropdown"><a><i class="fas fa-cog"></i> Transport Report
                              <span><i class="fa-solid fa-plus" onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li><a href="add_transport_report.php">Add Both Tier report</a></li>
                              <li><a href="both_tier_report.php">Show Both Tier Report</a></li>
                         </ul>
                    </li>


                    <li class="dropdown"><a><i class="fas fa-cog"></i> Settings
                              <span><i class="fa-solid fa-plus" onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li><a href="setting.php">Update profile</a></li>
                              <li><a href="change_password.php">Change Password</a></li>
                         </ul>
                    </li>



               </ul>
          </nav>
     <?php } ?>
     <div class="logout">
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
     </div>
</aside>