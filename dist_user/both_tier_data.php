<?php
ob_start();
session_start();
include("../includes/dbconnection.php"); // $conn = pg_connect(...);


if (!isset($_SESSION['login'])) {
     echo json_encode(["status" => "error", "message" => "Session expired. Please log in again."]);
     exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     echo json_encode(["status" => "error", "message" => "Invalid request method."]);
     exit();
}


if (!isset($_POST['submit']) && empty($_POST)) {
     echo json_encode(["status" => "error", "message" => "No form data received."]);
     exit();
}


function clean($data)
{
     return htmlspecialchars(trim($data));
}


$districtId = clean($_POST['districtId'] ?? '');
$district = clean($_POST['district'] ?? '');
$warehouseName = clean($_POST['warehouseName'] ?? '');
$wholesalerName = clean($_POST['wholesalerName'] ?? '');
$fpsName = clean($_POST['fpsName'] ?? '');
$contractor = clean($_POST['contractor'] ?? '');

$commodityA = clean($_POST['commodityA'] ?? '');
$quantity = floatval($_POST['quantity'] ?? 0);
$subCommodityA = clean($_POST['subCommodityA'] ?? '');
$additionalQuantity = floatval($_POST['additionalQuantity'] ?? 0);

$commodityB = clean($_POST['commodityB'] ?? '');
$quantity1 = floatval($_POST['quantity1'] ?? 0);
$subCommodityB = clean($_POST['subCommodityB'] ?? '');
$additionalQuantity1 = floatval($_POST['additionalQuantity1'] ?? 0);
$totalQuantity = floatval($_POST['totalQuantity'] ?? 0);

$liftingCommodityA = clean($_POST['liftingCommodityA'] ?? '');
$liftingQuantity = floatval($_POST['liftingQuantity'] ?? 0);
$liftingSubCommodityA = clean($_POST['liftingSubCommodityA'] ?? '');
$liftingQuantityAdditional = floatval($_POST['liftingQuantityAdditional'] ?? 0);

$liftingCommodityB = clean($_POST['liftingCommodityB'] ?? '');
$liftingQuantity1 = floatval($_POST['liftingQuantity1'] ?? 0);
$liftingSubCommodityB = clean($_POST['liftingSubCommodityB'] ?? '');
$liftingQuantityAdditional1 = floatval($_POST['liftingQuantityAdditional1'] ?? 0);
$liftingTotalQuantity = floatval($_POST['liftingTotalQuantity'] ?? 0);

$tier = floatval($_POST['tier'] ?? 0);
$tier2 = floatval($_POST['tier2'] ?? 0);
$totalRateTier1AndTier2 = floatval($_POST['totalRateTier1AndTier2'] ?? 0);
$centralShare = floatval($_POST['centralShare'] ?? 0);
$totalAmount = floatval($_POST['totalAmount'] ?? 0);
$stateShare = floatval($_POST['stateShare'] ?? 0);
$avgGovtRate = floatval($_POST['avgGovtRate'] ?? 0);
$totalAvgAmt = floatval($_POST['totalAvgAmt'] ?? 0);
$avgStateShare = floatval($_POST['avgStateShare'] ?? 0);
$avgStateShareToBePaid = floatval($_POST['avgStateShareToBePaid'] ?? 0);
$totalNetPay = floatval($_POST['totalNetPay'] ?? 0);
$stateShareDue = floatval($_POST['stateShareDue'] ?? 0);


if (empty($district) || empty($warehouseName) || empty($wholesalerName) || empty($fpsName) || empty($contractor)) {
     echo json_encode(["status" => "error", "message" => "Please fill all required fields."]);
     exit();
}


$query = "
INSERT INTO transport_report (
    district_id, district_name, warehouse_name, wholesaler_name, fps_name, contractor_name,
    allot_commodity, allot_quantity, allot_sub_commodity, allot_sub_quantity,
    allot_commodity_1, allot_quantity_1, allot_sub_commodity_1, allot_sub_quantity_1, allotment_total_quantity,
    lifting_commodity, lifting_quantity, lifting_sub_commodity, lifting_sub_quantity,
    lifting_commodity_1, lifting_quantity_1, lifting_sub_commodity_1, lifting_sub_quantity_1, total_lifting_quantity,
    tier1_rate, tier2_rate, total_tier1_and_tier2_rate, central_share, total_bill_amt, state_share,
    avg_govt_rate, total_govt_amt, avg_state_share_rate, state_share_to_be_paid, total_net_pay, state_share_due
) VALUES (
    $1,$2,$3,$4,$5,$6,
    $7,$8,$9,$10,
    $11,$12,$13,$14,$15,
    $16,$17,$18,$19,
    $20,$21,$22,$23,$24,
    $25,$26,$27,$28,$29,$30,
    $31,$32,$33,$34,$35,$36
);
";

$params = [
    
     $districtId,        
     $district,            
     $warehouseName,        
     $wholesalerName,        
     $fpsName,        
     $contractor,            

     
     $commodityA,     
     $quantity,       
     $subCommodityA,     
     $additionalQuantity,

     $commodityB,      
     $quantity1,       
     $subCommodityB,      
     $additionalQuantity1,
     $totalQuantity,         

   
     $liftingCommodityA,
     $liftingQuantity,  
     $liftingSubCommodityA,
     $liftingQuantityAdditional,

     $liftingCommodityB, 
     $liftingQuantity1,  
     $liftingSubCommodityB, 
     $liftingQuantityAdditional1,
     $liftingTotalQuantity,  

    
     $tier,         
     $tier2,        
     $totalRateTier1AndTier2,

     
     $centralShare,    
     $totalAmount,     
     $stateShare,    

     
     $avgGovtRate,   
     $totalAvgAmt,     
     $avgStateShare,        
     $avgStateShareToBePaid, 
     $totalNetPay,    
     $stateShareDue     
];



$result = pg_query_params($master_conn, $query, $params);

if ($result) {
     echo json_encode(["status" => "success", "message" => "Data saved successfully."]);
} else {
     echo json_encode([
          "status" => "error",
          "message" => "Database insert failed: " . pg_last_error($conn)
     ]);
}
?>