<!-- <div class="search-container">
     <input type="text" id="searchDist" placeholder="Enter district">
     <button onclick="filterTableByDist()">Search</button>
</div>

<style>
     .search-container {
          margin-bottom: 15px;
          display: flex;
          align-items: center;
          gap: 10px;
     }

     #searchDist {
          padding: 10px;
          width: 250px;
          border: 1px solid #ccc;
          border-radius: 5px;
          font-size: 16px;
     }

     .search-container button {
          padding: 10px 15px;
          background-color: #007BFF;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-size: 16px;
          transition: 0.3s;
     }

     button:hover {
          background-color: #0056b3;
     }
</style>

<script>
     document.addEventListener("DOMContentLoaded", function () {
          document.getElementById("searchDist").addEventListener("keyup", filterTableByDist);
     });

     function filterTableByDist() {
          let input = document.getElementById("searchDist").value.toLowerCase();
          let table = document.getElementById("dataTable"); // Ensure your table has this ID
          let rows = table.getElementsByTagName("tr");

          for (let i = 1; i < rows.length; i++) { // Skip header row
               let distColumn = rows[i].getElementsByTagName("td")[2]; // Assuming "District" is in the 3rd column
               if (distColumn) {
                    let distValue = distColumn.textContent.toLowerCase();
                    rows[i].style.display = distValue.includes(input) ? "" : "none";
               }
          }
     }
</script> -->

<!-- <div class="search-container">
    <input type="text" id="searchDist" placeholder="Enter district">
    <button onclick="searchByDistrict()">Search</button>
</div>

<style>
    .search-container {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #searchDist {
        padding: 10px;
        width: 250px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }

    .search-container button {
        padding: 10px 15px;
        background-color: #007BFF;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: 0.3s;
    }

    button:hover {
        background-color: #0056b3;
    }
</style>

<script>
    function searchByDistrict() {
        let dist = document.getElementById("searchDist").value;

        // Send AJAX request to fetch filtered data
        fetch("fetch_table.php?dist=" + encodeURIComponent(dist))
            .then(response => response.text())
            .then(data => {
                document.getElementById("tableContainer").innerHTML = data;
            })
            .catch(error => console.error("Error fetching data:", error));
    }
</script> -->


<div class="search-container">
    <select id="tableSelect">
        <option value="warehouses">Warehouses</option>
        <option value="wholesalers">Wholesalers</option>
        <option value="retailers">Retailers</option>
        <option value="warehouse_wholesaler_mapping">Warehouse-Wholesaler Mapping</option>
        <option value="wholesaler_retailer_mapping">Wholesaler-Retailer Mapping</option>
    </select>
    
    <input type="text" id="searchDist" placeholder="Enter district">
    <button onclick="searchData()">Search</button>
</div>

<div id="results"></div>

<style>
    .search-container {
        margin-bottom: 10px;
        text-align: center;
    }
    .search-container select, .search-container input {
        padding: 8px;
        margin-right: 5px;
    }
    .search-container button {
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }
    .search-container button:hover {
        background-color: #0056b3;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #007bff;
        color: white;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function searchData() {
        let table = document.getElementById("tableSelect").value;
        let dist = document.getElementById("searchDist").value;

        $.post("search.php", { table: table, dist: dist }, function(response) {
            document.getElementById("results").innerHTML = response;
        });
    }
</script>
