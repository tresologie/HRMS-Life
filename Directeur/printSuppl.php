<?php
require '../vendor/autoload.php'; 
include '../Includes/dbcon.php';

date_default_timezone_set('Africa/Bujumbura');

use Dompdf\Dompdf;
use Dompdf\Options;

// ===== Dates =====
$fromDate = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$toDate   = $_GET['to'] ?? date('Y-m-d');

// Limiter à 7 jours max
$diff = (strtotime($toDate) - strtotime($fromDate)) / (60*60*24) + 1;
if($diff > 7){
    $toDate = date('Y-m-d', strtotime($fromDate. ' + 6 days'));
}

// ===== Requête =====
$query = "SELECT 
    tblemployees.admissionNumber,
    tblemployees.firstName,
    tblemployees.lastName,
    tblemployees.identite,
    tblemployees.poste,
    tblservice.serviceName,
    tblsupp.dateTimeTaken,
    FLOOR(tblsupp.montant / 100) * 100 AS montant
FROM tblsupp
INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
WHERE tblsupp.dateTimeTaken BETWEEN '$fromDate' AND '$toDate'
ORDER BY tblservice.serviceName, tblemployees.firstName ASC";

$result = $conn->query($query);

// ===== Préparer les données =====
$dates = [];
$data  = [];

while($row = $result->fetch_assoc()){
    $emp = $row['admissionNumber'];
    $date = $row['dateTimeTaken'];
    $montant = $row['montant'];

    $dates[$date] = $date;

    $data[$emp]['name']  = $row['firstName'].' '.$row['lastName'];
    $data[$emp]['identite'] = $row['identite'];
    $data[$emp]['badge'] = $row['admissionNumber'];
    $data[$emp]['usine'] = $row['serviceName'];
    $data[$emp]['poste'] = $row['poste'];
    $data[$emp]['values'][$date] = $montant;
}

// Trier les dates et limiter à 7
ksort($dates);
$dates = array_slice($dates, 0, 7, true);

// ===== HTML =====
$html = '
<style>
body { 
    font-family: Arial, Helvetica, sans-serif; 
    font-size: 11px; 
    margin: 15px; 
}
.header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 20px; 
    border-bottom: 2px solid #6366f1; 
    padding-bottom: 10px; 
}
.logo { 
    font-size: 18px; 
    font-weight: bold; 
    color: #4f46e5; 
}
.title { 
    text-align: center; 
    text-decoration: underline;
    font-size: 16px; 
    font-weight: bold; 
    margin: 10px 0 20px; 
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-top: 10px; 
}
th, td { 
    border: 1px solid #000; 
    padding: 6px 8px; 
    text-align: left; 
}
th { 
    background-color: #e0e7ff; 
    font-weight: bold; 
    text-align: center; 
}
tr:nth-child(even) { 
    background-color: #f8fafc; 
}
.total-row { 
    font-weight: bold; 
    background-color: #e0e7ff !important; 
}
.center { text-align: center; }
.right { text-align: right; }
.footer { 
    margin-top: 30px; 
    text-align: center; 
    font-size: 10px; 
    color: #64748b; 
}
</style>
</head>
<body>

<div class="header">
<div class="logo">Life Company</div>
<div>
    <strong>Le ' . date("d/m/Y") . '</strong>
</div>
</div>
<div class="title">Heures supplementaires du '.$fromDate.' au '.$toDate.'</div>
</div>
<table>
<thead>
<tr>
<th>#</th>
<th>Nom & Prénom</th>
<th>No d_identite</th>
<th>Badge</th>
<th>Usine</th>
<th>Poste</th>';

foreach($dates as $date){
    $html .= '<th>'.date("d/m", strtotime($date)).'</th>';
}
$html .= '<th>Total</th>';
$html .= '<th>Signature</th></tr></thead><tbody>';

// ===== Lignes par employé =====
$totalGeneral = 0;
$cnt = 1;
foreach($data as $emp => $info){
    $totalEmp = 0;
    $html .= '<tr>';
    $html .= '<td>'.$cnt.'</td>';
    $html .= '<td>'.$info['name'].'</td>';
    $html .= '<td>'.$info['identite'].'</td>';
    $html .= '<td>'.$info['badge'].'</td>';
    $html .= '<td>'.$info['usine'].'</td>';
    $html .= '<td>'.$info['poste'].'</td>';

    foreach($dates as $date){
        $value = $info['values'][$date] ?? 0;
        $totalEmp += $value;
        $html .= '<td>'.number_format($value,0,',',' ').' Fbu</td>';
    }

    $html .= '<td><b>'.number_format($totalEmp,0,',',' ').' Fbu</b></td>';
    $html .= '<td></td></tr>';

    $totalGeneral += $totalEmp;
    $cnt++;
}

// ===== Total général =====
$html .= '<tr>
<td colspan="'.(6+count($dates)).'" style="text-align:right;font-weight:bold;">TOTAL GENERAL</td>
<td style="font-weight:bold;">'.number_format($totalGeneral,0,',',' ').' Fbu</td>
<td></td>
</tr>';

$html .= '</tbody></table>';

// ===== Génération PDF =====
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); 
$dompdf->render();
$dompdf->stream("Heures_supplementaires.pdf", ["Attachment" => true]);
exit;
?>