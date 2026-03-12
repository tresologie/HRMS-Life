<?php
require '../vendor/autoload.php'; // DOMPDF
include '../Includes/dbcon.php';
include '../Includes/session.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ===== Infos classe =====
$query = "SELECT tblservice.serviceName
FROM tblchef
INNER JOIN tblservice ON tblservice.Id = tblchef.classId
WHERE tblchef.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

// ===== Date du jour =====
date_default_timezone_set('Africa/Bujumbura');
$todaysDate = date("d-m-Y");

// ===== Récupération des présences du jour =====
$dateTaken = date("Y-m-d");
$cnt = 1;

$ret = mysqli_query($conn,"SELECT tblattendance.Id,
        tblattendance.status,
        tblattendance.dateTimeTaken,
        tblservice.serviceName,
        tblemployees.firstName,
        tblemployees.lastName,
        tblemployees.admissionNumber,
        tblemployees.poste
        FROM tblattendance
        INNER JOIN tblservice ON tblservice.Id = tblattendance.classId
        INNER JOIN tblemployees ON tblemployees.admissionNumber = tblattendance.admissionNo
        WHERE tblattendance.dateTimeTaken = '$dateTaken' 
        AND tblattendance.classId = '$_SESSION[classId]' 
        ORDER BY tblemployees.firstName ASC");

// ===== Générer le HTML pour PDF =====
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
<strong>Usine: '.$rrw['serviceName'].'</strong>
</div><div>
<strong>Le ' . date("d/m/Y") . '</strong>
</div>
</div>
<div class="title">Liste des présences</div>

<table>
<thead>
<tr>
<th>#</th>
<th>Nom & Prenom</th>
<th>Badge</th>
<th>Poste</th>
<th>Date</th>
<th>Status</th>
</tr>
</thead>
<tbody>
';

if(mysqli_num_rows($ret) > 0){
    while ($row = mysqli_fetch_array($ret)){
        $status = ($row['status'] == 1) ? "Présent" : "Absent";
        $class = ($row['status'] == 1) ? "present" : "absent";

        $html .= '<tr>
        <td>'.$cnt.'</td>
        <td>'.$row['firstName'].' '.$row['lastName'].'</td>
        <td>'.$row['admissionNumber'].'</td>
        <td>'.$row['poste'].'</td>
        <td>'.date("d/m/Y", strtotime($row['dateTimeTaken'])).'</td>
        <td class="'.$class.'">'.$status.'</td>
        </tr>';

        $cnt++;
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align:center;">Aucune présence enregistrée pour aujourd\'hui</td></tr>';
}

$html .= '</tbody></table>';

// ===== Génération PDF =====
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Appel.pdf", ["Attachment" => true]);
exit;
?>