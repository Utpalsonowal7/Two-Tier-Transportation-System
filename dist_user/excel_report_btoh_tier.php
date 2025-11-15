<?php
ob_start();

ini_set('default_charset', 'UTF-8');
header('Content-Type: text/html; charset=utf-8');

session_start();
include("../includes/dbconnection.php");
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

if (empty($start_date) || empty($end_date)) {
     die("Please select a valid date range.");
}

$start_date_time = $start_date . ' 00:00:00';
$end_date_time = $end_date . ' 23:59:59';
$adminId = $_SESSION['adminid'];

$distQuery = "SELECT district_id FROM district_users WHERE id = $1";
$distResult = pg_query_params($fsms_conn, $distQuery, [$adminId]);

if ($distResult && pg_num_rows($distResult) > 0) {
     $distRow = pg_fetch_assoc($distResult);
     $districtId = $distRow['district_id'];
} else {
     die("Unable to determine district for the user.");
}

//taking month name from selected start date
$monthName = date('F Y', strtotime($start_date));


// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data');

//Excel Heading
$title = "MONTHLY STATEMENT OF TRANSPORTATION BILL OF GPSS/FPS OF FOOD GRAINS UNDER NFSA OF LAKHIMPUR DISTRICT FOR THE MONTH OF " . strtoupper($monthName);
$sheet->mergeCells('A1:W1');
$sheet->setCellValue('A1', $title);

$sheet->getStyle('A1')->applyFromArray([
     'font' => ['bold' => true, 'size' => 16],
     'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical' => Alignment::VERTICAL_CENTER,
          'wrapText' => true,

     ],
     'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => ['argb' => 'FFFFFF99'],
     ],
]);
$sheet->getRowDimension(1)->setRowHeight(50);

$rateQuery = "SELECT  avg_govt_rate, avg_state_share_rate
              FROM transport_report
              WHERE report_added_at BETWEEN $1 AND $2
              AND district_id = $3";

$rateResult = pg_query_params($master_conn, $rateQuery, [$start_date_time, $end_date_time, $districtId]);
$rateRow = pg_fetch_assoc($rateResult);
$avg_govt_rate = $rateRow['avg_govt_rate'];
$avg_state_share_rate = $rateRow['avg_state_share_rate'];

// Headers
$headers = [
     [
          'Sl. No',
          'NAME OF TRANSPORTER',
          'NAME OF GPSS/WCSS',
          'ALLOTMENT',
          '',
          '',
          '',
          '',
          'LIFTING',
          '',
          '',
          '',
          '',
          'TIER - 1 Rate @',
          'TIER - 2 Rate @',
          'TOTAL RATE OF TIER - 1 & TIER - 2 (₹)',
          'CENTRAL SHARE@ 75/-',
          'STATE SHARE',
          'TOTAL BILL AMOUNT',
          "Total amount recived from Govt at an avf rate {$avg_govt_rate}%",
          "State Share to be paid @ {$avg_state_share_rate}%",
          'Total Net Payable Central + State Share',
          'Balance State Share to be received from Govt'
     ],
     [
          '',
          '',
          '',
          'AAY Rice',
          'ADL AAY',
          'PH Rice',
          'ADL PH',
          'TOTAL',
          'AAY Rice',
          'ADL AAY',
          'PH Rice',
          'ADL PH',
          'TOTAL',
          '',
          '',
          '',
          '',
          '',
          '',
          '',
          ''
     ]
];

// to write headres
foreach ($headers as $r => $row) {
     $excelRow = $r + 2;
     foreach ($row as $c => $value) {
          $colLetter = Coordinate::stringFromColumnIndex($c + 1);
          $sheet->setCellValue("{$colLetter}{$excelRow}", $value);
     }
}

$mergeRanges = [
     'A2:A3',
     'B2:B3',
     'C2:C3',
     'D2:H2',
     'I2:M2',
     'N2:N3',
     'O2:O3',
     'P2:P3',
     'Q2:Q3',
     'R2:R3',
     'S2:S3',
     'T2:T3',
     'U2:U3',
     'V2:V3',
     'W2:W3'
];

foreach ($mergeRanges as $range) {
     $sheet->mergeCells($range);
     $sheet->getStyle($range)->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setWrapText(true);
}

// data from DB
$query = "SELECT  district_name, wholesaler_name,  contractor_name,
        allot_commodity, allot_quantity, allot_sub_commodity, allot_sub_quantity,
        allot_commodity_1, allot_quantity_1, allot_sub_commodity_1, allot_sub_quantity_1,
        allotment_total_quantity, lifting_commodity, lifting_quantity,
        lifting_sub_commodity, lifting_sub_quantity, lifting_commodity_1, lifting_quantity_1,
        lifting_sub_commodity_1, lifting_sub_quantity_1, total_lifting_quantity,
        tier1_rate, tier2_rate, total_tier1_and_tier2_rate, central_share,
        total_bill_amt, state_share, avg_govt_rate, total_govt_amt, 
        avg_state_share_rate, state_share_to_be_paid, total_net_pay, 
        state_share_due, report_added_at
              FROM transport_report
              WHERE report_added_at BETWEEN $1 AND $2
              AND district_id = $3
              ORDER BY wholesaler_name, report_added_at ASC";

$result = pg_query_params($master_conn, $query, [$start_date_time, $end_date_time, $districtId]);
$data = [];
$serial = 1;
while ($row = pg_fetch_assoc($result)) {

     // $value = $row['allot_sub_quantity']; 

     // if (is_numeric($value)) {
     //      echo "$value is numeric\n";
     // } else {
     //      echo "$value is text\n";
     // }

     $data[] = [
          $serial++,
          html_entity_decode($row['contractor_name'], ENT_QUOTES, 'UTF-8'),
          trim($row['wholesaler_name']),
          trim($row['allot_quantity']),
          trim($row['allot_sub_quantity']),
          trim($row['allot_quantity_1']),
          trim($row['allot_sub_quantity_1']),
          trim($row['allotment_total_quantity']),
          trim($row['lifting_quantity']),
          trim($row['lifting_sub_quantity']),
          trim($row['lifting_quantity_1']),
          trim($row['lifting_sub_quantity_1']),
          trim($row['total_lifting_quantity']),
          trim($row['tier1_rate']),
          trim($row['tier2_rate']),
          trim($row['total_tier1_and_tier2_rate']),
          trim($row['central_share']),
          trim($row['state_share']),
          trim($row['total_bill_amt']),
          trim($row['total_govt_amt']),
          trim($row['state_share_to_be_paid']),
          trim($row['total_net_pay']),
          trim($row['state_share_due'])
     ];
}


if (empty($data)) {
     die("No records found for the selected date range ($start_date to $end_date).");
}

// Write data rows
$rowNum = 4;
foreach ($data as $row) {
     foreach ($row as $colNum => $value) {
          $colLetter = Coordinate::stringFromColumnIndex($colNum + 1);
          $sheet->setCellValue("{$colLetter}{$rowNum}", $value);
     }
     $rowNum++;
}

$startRow = 4;
$endRow = $sheet->getHighestRow();
$contractorNames = array_column($data, 1);
$uniqueNames = array_unique($contractorNames);

$row = $startRow;
while ($row <= $endRow) {
     $currentName = $sheet->getCell("B{$row}")->getValue();
     if (!$currentName) {
          $row++;
          continue;
     }

     $mergeStart = $row;
     while ($row <= $endRow && $sheet->getCell("B{$row}")->getValue() === $currentName) {
          $row++;
     }
     $mergeEnd = $row - 1;

     if ($mergeEnd > $mergeStart) {
          // Repeated contractor show vertically once
          $sheet->mergeCells("B{$mergeStart}:B{$mergeEnd}");
          $sheet->setCellValue("B{$mergeStart}", strtoupper($currentName));
          $sheet->getStyle("B{$mergeStart}:B{$mergeEnd}")->applyFromArray([
               'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'textRotation' => 90, // vertical for repeating ones
                    'wrapText' => false,
               ],
               'font' => ['bold' => true, 'size' => 11],
          ]);
     } else {
          // Appears only once do nothng special
          $sheet->setCellValue("B{$mergeStart}", strtoupper($currentName));
          $sheet->getStyle("B{$mergeStart}")->applyFromArray([
               'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                    'textRotation' => 0, // horizontal
               ],
               'font' => ['bold' => true, 'size' => 11],
          ]);
     }
}

// header row height
$sheet->getRowDimension(1)->setRowHeight(60);
$sheet->getRowDimension(2)->setRowHeight(80);
$sheet->getRowDimension(3)->setRowHeight(100);


// Apply borders and alignment
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

$sheet->getStyle("A1:{$highestColumn}{$highestRow}")
     ->getBorders()->getAllBorders()
     ->setBorderStyle(Border::BORDER_THIN);

$sheet->getStyle("A1:{$highestColumn}{$highestRow}")
     ->getAlignment()
     ->setHorizontal(Alignment::HORIZONTAL_CENTER)
     ->setVertical(Alignment::VERTICAL_CENTER)
     ->setWrapText(true);

// Header styling
$headerStyle = [
     'font' => ['bold' => true, 'color' => ['argb' => Color::COLOR_BLACK]],
     'fill' => [
          'fillType' => Fill::FILL_SOLID,
          'startColor' => ['argb' => 'FFD9D9D9']
     ],
];
$sheet->getStyle('A2:W3')->applyFromArray($headerStyle);

//rotaateing headers except allotment and lifting
$highestColumn = $sheet->getHighestColumn();
$highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

// Loop through header area
for ($row = 2; $row <= 2; $row++) {
     for ($col = 1; $col <= $highestColumnIndex; $col++) {
          $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
          $value = $sheet->getCell($cell)->getValue();

          // Skip ALLOTMENT and LIFTING (keep them horizontal)
          if (
               in_array(trim(strtoupper($value)), [
                    'ALLOTMENT',
                    'LIFTING'
               ])
          ) {
               continue;
          }

          // Rotate all other headings vertically
          $sheet->getStyle($cell)->getAlignment()
               ->setTextRotation(90)
               ->setHorizontal(Alignment::HORIZONTAL_CENTER)
               ->setVertical(Alignment::VERTICAL_CENTER)
               ->setWrapText(true);
     }
}

//  column widths
$widths = [6, 18, 22, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10,10, 10];
foreach ($widths as $i => $width) {
     $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 1))->setWidth($width);
}

function addSignatureSection($sheet, $startRow)
{
     $startRow += 8;   

     $sheet->getStyle("A{$startRow}:Z" . ($startRow + 5))
          ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

     $signatureBlocks = [
          [
               'startCol' => 'B',
               'endCol' => 'F',
               'label' => "District Commissioner"
          ],
          [
               'startCol' => 'G',
               'endCol' => 'K',
               'label' => "Food & Civil Supplies Officer"
          ],
          [
               'startCol' => 'L',
               'endCol' => 'P',
               'label' => "Supply Inspector"
          ],
     ];

     foreach ($signatureBlocks as $b) {

          $range = "{$b['startCol']}{$startRow}:{$b['endCol']}{$startRow}";

          $sheet->mergeCells($range);
          $sheet->setCellValue("{$b['startCol']}{$startRow}", $b['label']);

          $sheet->getStyle($range)->applyFromArray([
               'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
               ],
               'font' => [
                    'bold' => true,
                    'size' => 12,
               ],
          ]);
     }

     $sheet->getRowDimension($startRow)->setRowHeight(50);
}


$lastRow = $sheet->getHighestRow();
addSignatureSection($sheet, $lastRow);


if (ob_get_length())
     ob_end_clean();

$filename = "MONTHLY STATEMENT OF TRANSPORTATION BILL OF GPSS/FPS OF FOOD GRAINS UNDER NFSA OF LAKHIMPUR DISTRICT FOR THE MONTH OF" . date('Y-m-d_H-i-s') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
