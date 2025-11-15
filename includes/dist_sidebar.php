<?php
$id = $_SESSION['adminid'];

$query = "SELECT name, status FROM district_admins  WHERE id = $id";
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
          <nav class="sidebar-nav">
               <ul>
                    <li><a href="dist_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>

                    <li class="dropdown"><a><i class="fas fa-user-shield"></i> District Users<span><i
                                        class="fa-solid fa-plus" onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li><a href="add_district_user.php">Add District Users</a></li>
                              <li><a href="district_user_list.php">Show District Users</a></li>
                         </ul>
                    </li>

                    <!-- <li><a href="district_user_list.php"><i class="fas fa-user-shield"></i>District Users<i
                                   class="fa-solid fa-plus"></i></a></li> -->

                    <li class="dropdown"><a><i class="fa-solid fa-database"></i>Master Data<span><i class="fa-solid fa-plus"
                                        onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li><a href="warehouse.php"><i class="fas fa-warehouse"></i> Warehouses</a></li>
                              <li><a href="wholesalers.php"><i class="fas fa-store"></i> Wholesalers</a></li>
                              <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li>
                         </ul>
                    </li>

                    <li class="dropdown"><a><i class="fa-solid fa-link"></i></i>Mapping Data<span><i class="fa-solid fa-plus"
                                        onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li><a href="wholesaler_map.php"><i class="fas fa-warehouse"></i> Warehouse_wholesale Map</a>
                              </li>
                              <li><a href="retailer_map.php"><i class="fas fa-store"></i> Wholesale_Retailer Map</a></li>
                              <!-- <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li> -->
                         </ul>
                    </li>


                    <li class="dropdown"><a><i class="fa-solid fa-truck"></i>Manage Transport<span><i
                                        class="fa-solid fa-plus" onclick="toggleDropdown(event)"></i></span></a>
                         <ul class=" dropdown-menu">
                              <li><a href="add_comodity_transport.php"><i class="fa-solid fa-truck"></i> Add Comodities
                                        Transport Data Tier 1</a></li>
                              <li><a href="add_comodity_transport2.php"><i class="fa-solid fa-truck"></i> Add Comodities
                                        Transport Data Tier 2</a></li>
                              <li><a href="commodity_transport_data.php"><i class="fas fa-store"></i>Show Comodities Tranport
                                        Data Tier 1</a></li>
                              <li><a href="commodity_transport_data2.php"><i class="fas fa-store"></i>Show Comodities
                                        Tranport Data Tier 2</a></li>
                              <!-- <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li> -->
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
          <a href="dist_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
     </div>
</aside>