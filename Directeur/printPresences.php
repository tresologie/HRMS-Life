<?php
include '../Includes/dbcon.php';
require_once '../vendor/autoload.php'; // si installé via composer

date_default_timezone_set('Africa/Bujumbura');

use Dompdf\Dompdf;
use Dompdf\Options;



if(isset($_GET['fromDate']) && isset($_GET['toDate'])){
    $fromDate = $_GET['fromDate'];
    $toDate   = $_GET['toDate'];
} else {
    die("Periode invalide !");
}

$query = "SELECT 
    tblemployees.admissionNumber,
    tblemployees.firstName,
    tblemployees.lastName,
    tblemployees.poste,
    tblservice.serviceName,
    DATE(tblattendance.dateTimeTaken) as dateTaken,
    tblattendance.status
FROM tblattendance
INNER JOIN tblservice ON tblservice.Id = tblattendance.classId
INNER JOIN tblemployees ON tblemployees.admissionNumber = tblattendance.admissionNo
WHERE DATE(tblattendance.dateTimeTaken) 
BETWEEN '$fromDate' AND '$toDate'
ORDER BY tblservice.serviceName, tblemployees.firstName ASC";

$rs = $conn->query($query);

$dates = [];
$data  = [];

while ($row = $rs->fetch_assoc()) {

    $emp   = $row['admissionNumber'];
    $date  = $row['dateTaken'];
    $status= $row['status'];

    $dates[$date] = $date;

    $data[$emp]['name']  = $row['firstName'].' '.$row['lastName'];
    $data[$emp]['badge'] = $row['admissionNumber'];
    $data[$emp]['usine'] = $row['serviceName'];
    $data[$emp]['poste'] = $row['poste'];

    $data[$emp]['values'][$date] = ($status == 1) ? 'P' : 'A';
}

ksort($dates);

// ===== HTML POUR PDF =====
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
<div class="title">Liste de Presences du '.$fromDate.' au '.$toDate.'</div>
</div>

<table>
<tr>
<th>#</th>
<th>Nom & Prenom</th>
<th>Badge</th>
<th>Usine</th>
<th>Poste</th>';

foreach($dates as $date){
    $html .= '<th>'.date("d/m", strtotime($date)).'</th>';
}

$html .= '<th>TOTAL</th></tr>';

$cnt = 1;

foreach($data as $emp => $info){

    $totalP = 0;

    $html .= '<tr>';
    $html .= '<td>'.$cnt.'</td>';
    $html .= '<td>'.$info['name'].'</td>';
    $html .= '<td>'.$info['badge'].'</td>';
    $html .= '<td>'.$info['usine'].'</td>';
    $html .= '<td>'.$info['poste'].'</td>';

    foreach($dates as $date){

        $value = isset($info['values'][$date]) ? $info['values'][$date] : '';

        if($value == 'P'){
            $totalP++;
        }

        $html .= '<td>'.$value.'</td>';
    }

    $html .= '<td><b>'.$totalP.'</b></td>';
    $html .= '</tr>';
    $cnt++;
}

$html .= '</table>
<div class="footer">
        Good Life Service<br>
        Cartier Industriel – Chausse d\'Uvira – Tel: +257 68 50 50 50<br>
        Burundi – Bujumbura 
    </div>
</body>
</html>';

// ===== GENERATION PDF =====
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // paysage
$dompdf->render();
$dompdf->stream("Liste de presence.pdf", array("Attachment" => true));
exit;
?>