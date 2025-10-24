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
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>

                    <li class="dropdown"><a><i class="fas fa-user-shield"></i> District Admins<span><i
                                        class="fa-solid fa-plus onclick=" toggleDropdown(event)" "></i></span></a>
                                        <ul class=" dropdown-menu">
                    <li><a href="add_district_admin.php">Add District Admin</a></li>
                    <li><a href="district_admin_list.php">Show District Admin</a></li>
               </ul>
               </li>

               <li><a href="district_user_list.php"><i class="fas fa-user-shield"></i>District Users<i
                              class="fa-solid fa-plus"></i></a></li>

               <li class="dropdown"><a><i class="fa-solid fa-database"></i>Master Data<span><i class="fa-solid fa-plus onclick="
                                   toggleDropdown(event)" "></i></span></a>
                              <ul class=" dropdown-menu">
               <li><a href="warehouses.php"><i class="fas fa-warehouse"></i> Warehouses</a></li>
               <li><a href="wholesalers.php"><i class="fas fa-store"></i> Wholesalers</a></li>
               <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li>
               </ul>
               </li>

               <li class="dropdown"><a><i class="fa-solid fa-link"></i></i>Mapping Data<span><i
                                   class="fa-solid fa-plus onclick=" toggleDropdown(event)" "></i></span></a>
                              <ul class=" dropdown-menu">
               <li><a href="wholesale_map.php"><i class="fas fa-warehouse"></i> Warehouse_wholesale Map</a></li>
               <li><a href="retailer_map.php"><i class="fas fa-store"></i> Wholesale_Retailer Map</a></li>
               <!-- <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li> -->
               </ul>
               </li>

               <li class="dropdown"><a><i class="fa-solid fa-truck"></i>Transport Data<span><i
                                   class="fa-solid fa-plus onclick=" toggleDropdown(event)" "></i></span></a>
                              <ul class=" dropdown-menu">
               <li><a href="warehouse_wholesale_data.php"><i class="fa-solid fa-database"></i> Warehouse_wholesale Data</a></li>
               <li><a href="retailer_wholesale_data.php"><i class="fa-solid fa-database"></i> Wholesale_Retailer Data</a></li>
               <!-- <li><a href="retailers.php"><i class="fas fa-shopping-cart"></i> Retailers</a></li> -->
               </ul>
               </li>

               <li class="dropdown"><a><i class="fas fa-cog"></i> Settings
               <span><i class="fa-solid fa-plus onclick=" toggleDropdown(event)" "></i></span></a>
                 <ul class=" dropdown-menu">
                    <li><a href="setting.php">Update profile</a></li>
                    <li><a href="change_password.php">Change Password</a></li>
                 </ul>
               </li>
        
          </nav>
     <?php } ?>
     <div class="logout">
          <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
     </div>
</aside>