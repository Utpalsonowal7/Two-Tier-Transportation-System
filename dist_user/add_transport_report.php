<?php
ob_start();
session_start();
include("../includes/dbconnection.php");

if (!isset($_SESSION['login'])) {
     echo "<script>alert('session has expired, please log in again!'); window.location.href = 'login.php' </script>";
     exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8" />
     <title>WCSS Contractor Form</title>
     <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
     <link rel="stylesheet" href="../assets/dashboard.css">
     <link rel="stylesheet" href="../assets/style.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

     <style>
          #infoForm {
               margin-top: 20px;
               padding: 20px;
               border: 1px solid #ccc;
               border-radius: 5px;
               background-color: #ffffffff;
          }

          .one {
               /* margin-bottom: 2px; */
               border: 2px solid #ddd;
               /* width: 800px; */
               padding: 10px;
               /* display: flex; */
          }

          .heading {
               display: flex;
               width: 100%;
               font-size: 1.2em;
               font-weight: bold;
               margin-bottom: 10px;
               background-color: #f4f4f4;
          }

          .heading1 {
               display: flex;
               justify-content: space-between;
               align-items: center;
               background-color: #f4f4f4;
               font-size: 1.2em;
               font-weight: bold;
               padding: 5px 8px;
               margin-bottom: 10px;
          }

          .sameAS {
               /* width: 500px; */
               display: flex;
               gap: 1px;
               align-items: center;
               margin-left: auto;
          }

          .sameAS input {
               width: auto;
               margin: 5px;
          }

          .sameAS label {
               margin: 0;
          }

          /* .heading input {
                font-size: 10px;
            } */

          .allotment {
               display: flex;
          }

          .two {
               margin-bottom: 2px;
               border: 2px solid #ddd;
               /* width: 800px; */
               padding: 10px;
               display: flex;
          }

          .two .sec {
               width: 100%;
          }

          /* input,
            textarea,
            select {
                width: 50%;
            } */

          .cal {
               margin-bottom: 2px;
               border: 2px solid #ddd;
               padding: 10px;
               display: flex;
               flex-wrap: wrap;
               gap: 2px;
          }

          /* .cal {
                border: 2px solid #f76707;
                border-radius: 5px;
                background-color: #fff4e6;
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                padding: 10px;
                margin-top: 10px;
            } */

          .cal>.sec {
               flex: 1 1 250px;
               /* border: 2px solid #ffa94d;
                border-radius: 5px;
                background-color: #ffd8a8;
                padding: 10px;
                color: #d9480f; */
          }

          .cal .sec input {
               width: 80%;
          }

          .two .sec input,
          .two .sec select {
               width: 80%;
               box-sizing: border-box;
          }

          .one .comoditi {
               width: 100%;
          }

          .one .total {
               width: 100%;
          }

          /* .selInpu select{
               width: 80%;
          } */

          .one .comoditi input,
          .one .comoditi select {
               width: 95%;
               box-sizing: border-box;
               outline: none;
          }

          /* .one .option-style:hover {
                background-color: #f1f1f1;
               
            } */
          /* section h3 {
                margin-top: 0;
                margin-bottom: 5px;
            } */

          label {
               display: block;
               margin-top: 5px;
               font-weight: bold;
               font-size: 14px;
          }

          input,
          textarea,
          select {
               width: 50%;
               padding: 8px;
               margin-top: 4px;
               border: 1px solid #ccc;
               border-radius: 5px;
               box-sizing: border-box;
               outline: none;
          }

          button {
               margin-top: 15px;
               padding: 10px 15px;
               cursor: pointer;
               background: #007bff;
               color: white;
               border: none;
               border-radius: 5px;
          }

          button:hover {
               background: #0056b3;
          }

          #status {
               margin-top: 15px;
               font-weight: bold;
          }

          .error {
               border: 2px solid #e74c3c !important;
               background-color: #ffe6e6;
          }

          #formMessage {
               font-weight: bold;
               padding: 8px 10px;
               border-radius: 5px;
               margin-top: 10px;
               display: inline-block;
               transition: opacity 0.3s ease;
          }

          #formMessage.success {
               color: #155724;
               background: #d4edda;
               border: 1px solid #c3e6cb;
          }

          #formMessage.error {
               color: #721c24;
               background: #f8d7da;
               border: 1px solid #f5c6cb;
          }
     </style>
</head>

<body>

     <?php include('../includes/user_sidebar.php'); ?>
     <?php include('../includes/header.php'); ?>

     <div class="main-content">
          <div class="dashboard">
               <span class="icon"><i class="fa-solid fa-truck"></i>
               </span>
               <h3 class="slash">/</h3>
               <h2>Add Transportation details for WCSS/FPS</h2>
          </div>

          <div class="form">
               <form id="infoForm">
                    <section class="two cal">
                         <div class="sec">
                              <input type="hidden" id="districtId" name="districtId">
                              <label>District</label>
                              <input type="text" id="district" name="district" />
                         </div>

                         <div class="sec selInpu">
                              <label for="warehouseLst">Warehouse Name</label>
                              <select name="warehouseName" id="warehouseLst">
                                   <option value="">Select Warehouse</option>
                              </select>
                         </div>

                         <div class="sec">
                              <label for="wholesalerLst">WCSS Name</label>
                              <select name="wholesalerName" id="wholesalerLst">
                                   <option value="">Select Wholesaler</option>
                              </select>
                         </div>

                         <div class="sec">
                              <label for="fpsLst">FPS Name</label>
                              <select name="fpsName" id="fpsLst">
                                   <option value="">Select Wholesaler</option>
                              </select>
                         </div>

                         <div class="sec">
                              <label>Contractor Name</label>
                              <input type="text" id="contractor" name="contractor" />
                         </div>
                    </section>

                    <section class="one">
                         <div class="heading"><span>Allotment</span></div>
                         <div class="allotment">
                              <div class="comoditi">
                                   <label>Commodities</label>
                                   <select name="commodityA" id="como" class="select-style">
                                        <option class="option-style" value="select">Select</option>
                                        <option class="option-style" value="AAY Rice">AAY Rice</option>
                                        <option class="option-style" value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Quantity</label>
                                   <input type="number" id="quantity" name="quantity" />

                                   <label>Sub-Commodities</label>
                                   <select name="subCommodityA" id="comos">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Additional Quantity</label>
                                   <input type="number" id="additionalQuantity" name="additionalQuantity" />
                              </div>

                              <div class="comoditi">
                                   <label>Commodities</label>
                                   <select name="commodityB" id="comodo">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Quantity</label>
                                   <input type="number" id="quantity1" name="quantity1" />

                                   <label>Sub-Commodities</label>
                                   <select name="subCommodityB" id="comoditi">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Additional Quantity</label>
                                   <input type="number" id="additionalQuantity1" name="additionalQuantity1" />
                              </div>

                              <div class="total">
                                   <label for="totalQuantity">Total Quantity</label>
                                   <input type="number" id="totalQuantity" name="totalQuantity" />
                              </div>
                         </div>
                    </section>

                    <section class="one">
                         <div class="heading1">
                              <span>Lifting</span>
                              <div class="sameAS">
                                   <input type="checkbox" id="checkAllotment" name="checkAllotment" />
                                   <label for="checkAllotment">Same as Allotment</label>
                              </div>
                         </div>
                         <div class="allotment">
                              <div class="comoditi">
                                   <label>Commodities</label>
                                   <select name="liftingCommodityA" id="como1">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Quantity</label>
                                   <input type="number" id="liftingQuantity" name="liftingQuantity" />

                                   <label>Sub-Commodities</label>
                                   <select name="liftingSubCommodityA" id="comos1">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Additional Quantity</label>
                                   <input type="number" id="liftingQuantityAdditional"
                                        name="liftingQuantityAdditional" />
                              </div>

                              <div class="comoditi">
                                   <label>Commodities</label>
                                   <select name="liftingCommodityB" id="comodo1">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Quantity</label>
                                   <input type="number" id="liftingQuantity1" name="liftingQuantity1" />

                                   <label>Sub-Commodities</label>
                                   <select name="liftingSubCommodityB" id="comoditi1">
                                        <option value="select">Select</option>
                                        <option value="AAY Rice">AAY Rice</option>
                                        <option value="PH Rice">PH Rice</option>
                                   </select>

                                   <label>Additional Quantity</label>
                                   <input type="number" id="liftingQuantityAdditional1"
                                        name="liftingQuantityAdditional1" />
                              </div>

                              <div class="total">
                                   <label>Total Quantity</label>
                                   <input type="number" id="liftingTotalQuantity" name="liftingTotalQuantity" />
                              </div>
                         </div>
                    </section>

                    <section class="cal">
                         <div class="sec">
                              <label for="tier">Tier-1 @ </label>
                              <input type="number" id="tier" name="tier" readonly />
                         </div>
                         <div class="sec">
                              <label for="tier2">Tier-2 @ </label>
                              <input type="number" id="tier2" name="tier2" readonly />
                         </div>
                         <div class="sec">
                              <label for="totalRateTier1AndTier2">Total Rate (Tier 1 & Tier 2)</label>
                              <input type="number" id="totalRateTier1AndTier2" name="totalRateTier1AndTier2" readonly />
                         </div>
                         <div class="sec">
                              <label for="centralShare">Central Share</label>
                              <input type="number" id="centralShare" name="centralShare" />
                         </div>
                         <div class="sec">
                              <label for="totalAmount">Total Bill Amount</label>
                              <input type="number" id="totalAmount" name="totalAmount" />
                         </div>
                         <div class="sec">
                              <label for="stateShare">State Share</label>
                              <input type="number" id="stateShare" name="stateShare" readonly />
                         </div>
                         <div class="sec">
                              <label for="avgGovtRate">Avg Govt Rate</label>
                              <input type="number" id="avgGovtRate" name="avgGovtRate" />
                         </div>
                         <div class="sec">
                              <label for="totalAvgAmt">Total Govt Amt @</label>
                              <input type="number" id="totalAvgAmt" name="totalAvgAmt" readonly />
                         </div>
                         <div class="sec">
                              <label for="avgStateShare">Avg State share rate</label>
                              <input type="number" id="avgStateShare" name="avgStateShare" />
                         </div>
                         <div class="sec">
                              <label for="avgStateShareToBePaid">State Share to be paid @</label>
                              <input type="number" id="avgStateShareToBePaid" name="avgStateShareToBePaid" readonly />
                         </div>
                         <div class="sec">
                              <label for="totalNetPay">Total Payable (central + state)</label>
                              <input type="number" id="totalNetPay" name="totalNetPay" readonly />
                         </div>
                         <div class="sec">
                              <label for="stateShareDue">State Share Due</label>
                              <input type="number" id="stateShareDue" name="stateShareDue" readonly />
                         </div>
                    </section>

                    <button type="button" name="submit" onclick="saveToPHP()">💾 Save</button>
                    <div id="formMessage" style="margin-top:10px;"></div>

               </form>
          </div>
     </div>

     <!-- <p id="status"></p> -->

     <script>

          window.onload = async function () {
               console.log("onloading...........");
               // for (let key in formData) {
               //     const el = document.getElementById(key);
               //     if (el) el.value = formData[key];
               // }
               // for (const key in formData) {
               //      const el = document.getElementById(key);
               //      if (el) el.value = formData[key];
               // }

               try {
                    const res = await fetch("get_details.php");
                    const data = await res.json();

                    console.log(data.district_id);
                    console.log(data.district);


                    document.getElementById('districtId').value = data.district_id;
                    document.getElementById('district').value = data.district;

                    //for warehouse data fetching
                    const warehouseList = document.getElementById('warehouseLst');
                    data.warehouse.forEach(wh => {
                         const opt = document.createElement('option');
                         opt.value = wh.name;
                         opt.textContent = wh.name;
                         warehouseList.appendChild(opt);
                    });

               } catch (error) {
                    console.log("Error fetching data of wholesaler:", error);
               }

               //fetching and filling tieer1 rate 
               document.getElementById('wholesalerLst').addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const tierRate = parseFloat(selectedOption.dataset.tier1Rtae) || 0;
                    document.getElementById('tier').value = tierRate;
                    console.log(tierRate);

                    calculateTierTotal();
               })

               document.getElementById('fpsLst').addEventListener('change', function () {
                    const selectOption = this.options[this.selectedIndex];
                    const tier2Rate = parseFloat(selectOption.dataset.tier2Rate) || 0;
                    console.log(tier2Rate);

                    document.getElementById('tier2').value = tier2Rate;

                    calculateTierTotal();
               })

          };

          //district reloaidng after reste of form is submitted
          async function reloadDistrict() {
               try {
                    const res = await fetch("get_details.php");
                    const data = await res.json();

                    document.getElementById('districtId').value = data.district_id;
                    document.getElementById('district').value = data.district;
               } catch (error) {
                    console.error("Error reloading district:", error);
               }
          }

          //posting the value of warehouse name for backend query to api
          document.getElementById('warehouseLst').addEventListener('change', async function () {
               const selectedOption = this.options[this.selectedIndex];
               const selectName = selectedOption.text;
               console.log("Selected Warehouse index:", selectedOption);
               console.log("Selected Warehouse:", selectName);

               if (!selectName) return;

               try {
                    const res = await fetch('get_details.php', {
                         method: "POST",
                         headers: { "Content-Type": "application/x-www-form-urlencoded" },
                         body: new URLSearchParams({ warehouseName: selectName })
                    });

                    const data = await res.json();
                    console.log("Response's data:", data);

                    const wholesalerList = document.getElementById('wholesalerLst');
                    wholesalerList.innerHTML = '<option value="">Select Wholesaler</option>';

                    if (data.wholesaler && data.wholesaler.length > 0) {
                         data.wholesaler.forEach(we => {
                              const opt = document.createElement('option');
                              // opt.value = we.transport_rate;
                              opt.value = we.wholesaler_name;
                              opt.textContent = we.wholesaler_name;
                              opt.dataset.tier1Rtae = we.transport_rate;
                              wholesalerList.appendChild(opt);
                         });
                    } else {
                         wholesalerList.innerHTML += '<option value="">No Wholesalers Available</option>';
                         console.log("No wholesalers returned for this warehouse.");
                    }

               } catch (error) {
                    console.error("Error fetching data of wholesaler:", error);
               }
          });

          //posting wholesaler name for backend query in api
          document.getElementById('wholesalerLst').addEventListener('change', async function () {
               const selectedOption = this.options[this.selectedIndex];
               const selectName = selectedOption.text.trim();

               console.log(`[${selectName}]`, selectName.length)
               console.log("selected item:", selectedOption);
               console.log("selected wholesaler:", selectName);

               if (!selectName) return;

               try {
                    const re = await fetch('get_details.php', {
                         method: "POST",
                         headers: { "Content-Type": "application/x-www-form-urlencoded" },
                         body: new URLSearchParams({ wholesalerName: selectName })
                    })

                    if (!re.ok) {
                         throw new Error('Network response was not ok');
                    }

                    const data = await re.json();
                    console.log('Data:', data);


                    const retailerLst = document.getElementById('fpsLst');
                    retailerLst.innerHTML = '<option value="">Select Retailer</option>';

                    if (data.retailer && data.retailer.length > 0) {
                         data.retailer.forEach(re => {
                              const opt = document.createElement('option');

                              // opt.value = re.transport_rate;
                              opt.value = re.retailer_name;
                              opt.textContent = re.retailer_name;
                              opt.dataset.tier2Rate = re.transport_rate;
                              retailerLst.appendChild(opt)
                         });
                    } else {
                         retailerLst.innerHTML += ' <option value="">No retailer avilable</option>';
                         console.log("no data found!");
                         // alert("Something went wrong while fetching retailer data. Please try again.");
                    }
               } catch (error) {
                    console.log("Error details:", error);
               }
          });


          function calculateAllotment() {
               const qty =
                    parseFloat(document.getElementById("quantity").value) || 0;
               const addQty =
                    parseFloat(
                         document.getElementById("additionalQuantity").value
                    ) || 0;
               const qty1 =
                    parseFloat(document.getElementById("quantity1").value) || 0;
               const additionalQuantity1 =
                    parseFloat(
                         document.getElementById("additionalQuantity1").value
                    ) || 0;

               const total = qty + addQty + qty1 + additionalQuantity1;

               document.getElementById("totalQuantity").value = total.toFixed(2);

               // calCentralShare();
          }

          document
               .getElementById("quantity")
               .addEventListener("input", calculateAllotment);
          document
               .getElementById("additionalQuantity")
               .addEventListener("input", calculateAllotment);
          document
               .getElementById("quantity1")
               .addEventListener("input", calculateAllotment);
          document
               .getElementById("additionalQuantity1")
               .addEventListener("input", calculateAllotment);

          function calculateLifting() {
               const qty =
                    parseFloat(
                         document.getElementById("liftingQuantity").value
                    ) || 0;
               const addQty =
                    parseFloat(
                         document.getElementById("liftingQuantityAdditional")
                              .value
                    ) || 0;
               const qty1 =
                    parseFloat(
                         document.getElementById("liftingQuantity1").value
                    ) || 0;
               const additionalQuantity1 =
                    parseFloat(
                         document.getElementById("liftingQuantityAdditional1")
                              .value
                    ) || 0;

               const total = qty + addQty + qty1 + additionalQuantity1;

               document.getElementById("liftingTotalQuantity").value = total.toFixed(2);

               calCentralShare();
               totalBillAmount();
               stateShare();
               avgStateShareToBePaid();
          }

          document
               .getElementById("liftingQuantity")
               .addEventListener("input", calculateLifting);
          document
               .getElementById("liftingQuantityAdditional")
               .addEventListener("input", calculateLifting);
          document
               .getElementById("liftingQuantity1")
               .addEventListener("input", calculateLifting);
          document
               .getElementById("liftingQuantityAdditional1")
               .addEventListener("input", calculateLifting);

          //copying allotment to lifting if same
          document
               .getElementById("checkAllotment")
               .addEventListener("change", function () {
                    if (this.checked) {
                         document.getElementById("liftingQuantity").value =
                              document.getElementById("quantity").value;
                         document.getElementById(
                              "liftingQuantityAdditional"
                         ).value =
                              document.getElementById("additionalQuantity").value;
                         document.getElementById("liftingQuantity1").value =
                              document.getElementById("quantity1").value;
                         document.getElementById(
                              "liftingQuantityAdditional1"
                         ).value = document.getElementById(
                              "additionalQuantity1"
                         ).value;
                         document.getElementById("liftingTotalQuantity").value =
                              document.getElementById("totalQuantity").value;
                         document.getElementById("como1").value =
                              document.getElementById("como").value;
                         document.getElementById("comos1").value =
                              document.getElementById("comos").value;
                         document.getElementById("comodo1").value =
                              document.getElementById("comodo").value;
                         document.getElementById("comoditi1").value =
                              document.getElementById("comoditi").value;

                         calCentralShare();
                         totalBillAmount();
                         stateShare();
                         avgStateShareToBePaid();
                    } else {
                         document.getElementById("liftingQuantity").value = " ";
                         document.getElementById(
                              "liftingQuantityAdditional"
                         ).value = " ";
                         document.getElementById("liftingQuantity1").value = " ";
                         document.getElementById(
                              "liftingQuantityAdditional1"
                         ).value = " ";
                         document.getElementById("liftingTotalQuantity").value =
                              " ";
                         document.getElementById("como1").value = " ";
                         document.getElementById("comos1").value = " ";
                         document.getElementById("comodo1").value = " ";
                         document.getElementById("comoditi1").value = " ";

                         calCentralShare();
                         totalBillAmount();
                         stateShare();
                         avgStateShareToBePaid();
                    }
               });

          function syncIfCheck() {
               if (document.getElementById("checkAllotment").checked) {
                    document.getElementById("liftingQuantity").value =
                         document.getElementById("quantity").value;
                    document.getElementById("liftingQuantityAdditional").value =
                         document.getElementById("additionalQuantity").value;
                    document.getElementById("liftingQuantity1").value =
                         document.getElementById("quantity1").value;
                    document.getElementById(
                         "liftingQuantityAdditional1"
                    ).value = document.getElementById(
                         "additionalQuantity1"
                    ).value;
                    document.getElementById("liftingTotalQuantity").value =
                         document.getElementById("totalQuantity").value;
                    document.getElementById("como1").value =
                         document.getElementById("como").value;
                    document.getElementById("comos1").value =
                         document.getElementById("comos").value;
                    document.getElementById("comodo1").value =
                         document.getElementById("comodo").value;
                    document.getElementById("comoditi1").value =
                         document.getElementById("comoditi").value;
               }
          }

          [
               "quantity",
               "additionalQuantity",
               "quantity1",
               "additionalQuantity1",
               "totalQuantity",
               "como",
               "comos",
               "comodo",
               "comoditi",
          ].forEach((id) =>
               document
                    .getElementById(id)
                    .addEventListener("input", syncIfCheck)
          );

          function calculateTierTotal() {
               const tier1 =
                    parseFloat(document.getElementById("tier").value) || 0;
               const tier2 =
                    parseFloat(document.getElementById("tier2").value) || 0;

               const total = tier1 + tier2;

               document.getElementById("totalRateTier1AndTier2").value = total.toFixed(2);
          }

          function calCentralShare() {
               console.log("Central share function triggered");
               const totalCentral =
                    parseFloat(
                         document.getElementById("liftingTotalQuantity").value
                    ) || 0;

               document.getElementById("centralShare").value = (
                    totalCentral * 75
               ).toFixed(2);

               totalPayble();
          }

          document
               .getElementById("liftingTotalQuantity")
               .addEventListener("input", calCentralShare);

          function totalBillAmount() {
               console.log("efhyf");
               const totalBill =
                    parseFloat(
                         document.getElementById("liftingTotalQuantity").value
                    ) || 0;
               const totalRate =
                    parseFloat(
                         document.getElementById("totalRateTier1AndTier2").value
                    ) || 0;
               console.log(`THE rate is ${totalRate}`);
               document.getElementById("totalAmount").value =
                    totalBill * totalRate;

               balanceStateShareToBeRecivedFromGovt();
          }

          document
               .getElementById("liftingTotalQuantity")
               .addEventListener("input", totalBillAmount);

          function stateShare() {
               const totalBill =
                    parseFloat(document.getElementById("totalAmount").value) ||
                    0;
               const centralShare =
                    parseFloat(document.getElementById("centralShare").value) ||
                    0;

               document.getElementById("stateShare").value = (
                    totalBill - centralShare
               ).toFixed(2);
          }

          document
               .getElementById("totalAmount")
               .addEventListener("input", stateShare);

          const govtRate = document.getElementById("avgGovtRate");
          const totalAv = document.querySelector('label[for="totalAvgAmt"]');

          govtRate.addEventListener("input", function () {
               const value = this.value;

               if (value) {
                    totalAv.textContent = `Total Govt Amt @${value}%`;
               } else {
                    totalAv.textContent = "Total Govt Amt @";
               }
          });

          function avgGovtRate() {
               const govtRate =
                    parseFloat(document.getElementById("avgGovtRate").value) ||
                    0;

               const liftQnty =
                    parseFloat(
                         document.getElementById("liftingTotalQuantity").value
                    ) || 0;

               const avgRate = govtRate * liftQnty;

               document.getElementById("totalAvgAmt").value =
                    avgRate.toFixed(2);
          }

          document
               .getElementById("avgGovtRate")
               .addEventListener("input", avgGovtRate);


          const avgState = document.getElementById('avgStateShare')
          const avgPaid = document.querySelector('label[for="avgStateShareToBePaid"]')

          avgState.addEventListener("input", function () {
               const value = this.value;

               if (value) {
                    avgPaid.textContent = `State Share to be paid @${value}%`;
               } else {
                    avgPaid.textContent = "State Share to be paid";
               }
          })


          function avgStateShareToBePaid() {
               const stateAVgShare = parseFloat(document.getElementById('avgStateShare').value) || 0;
               const liftingTotal = parseFloat(document.getElementById('liftingTotalQuantity').value) || 0;

               const total = (stateAVgShare * liftingTotal).toFixed(2);

               document.getElementById('avgStateShareToBePaid').value = total;

               totalPayble();
          }

          document
               .getElementById("avgStateShare")
               .addEventListener("input", avgStateShareToBePaid);


          function totalPayble() {
               console.log('Net pay is loading')
               const centralTotal = parseFloat(document.getElementById('centralShare').value) || 0;
               const stateShare = parseFloat(document.getElementById('avgStateShareToBePaid').value) || 0;

               const netPay = centralTotal + stateShare;

               document.getElementById('totalNetPay').value = Math.round(netPay);

               balanceStateShareToBeRecivedFromGovt();
          }

          document.getElementById('avgStateShareToBePaid').addEventListener("input", totalPayble)

          function balanceStateShareToBeRecivedFromGovt() {
               console.log("Sate Share due is loading......... ")
               const totalBill = parseFloat(document.getElementById('totalAmount').value) || 0;
               const netPay = parseFloat(document.getElementById('totalNetPay').value) || 0;

               const balaceToGet = Math.round(totalBill - netPay);

               document.getElementById('stateShareDue').value = balaceToGet
          }
     </script>
     <script src="../js/validationForm.js"></script>
</body>

</html>