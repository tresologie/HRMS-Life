<?php
require '../vendor/autoload.php'; // chemin vers dompdf/autoload.php
use Dompdf\Dompdf;
use Dompdf\Options;

include '../Includes/dbcon.php';
include '../Includes/session.php';

// Date du jour
date_default_timezone_set('Africa/Bujumbura');
$todaysDate = date("d-m-Y");
$dateTaken = date("Y-m-d");

// Récupérer le nom de la classe
$query = "SELECT tblservice.serviceName
FROM tblchef
INNER JOIN tblservice ON tblservice.Id = tblchef.classId
WHERE tblchef.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

// Récupérer les heures supplémentaires d'aujourd'hui
$ret = mysqli_query($conn,"SELECT tblsupp.Id, tblsupp.dateTimeTaken, tblemployees.identite,
DATE_FORMAT(tblsupp.heureDebut, '%H:%i') AS heureDebut, 
DATE_FORMAT(tblsupp.heureFin, '%H:%i') AS heureFin,
tblsupp.heures, tblservice.serviceName, FLOOR(tblsupp.montant / 100) * 100 AS montant,        
tblemployees.firstName, tblemployees.lastName, tblemployees.admissionNumber, tblemployees.poste
FROM tblsupp
INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
WHERE tblsupp.dateTimeTaken = '$dateTaken' 
AND tblsupp.classId = '$_SESSION[classId]' 
ORDER BY tblemployees.firstName ASC");

// Récupérer le nom du professeur
$teacherQuery = mysqli_query($conn, "
    SELECT firstName, lastName 
    FROM tblchef 
    WHERE classId = '".$_SESSION['classId']."'
");
$teacher = mysqli_fetch_assoc($teacherQuery);

// Construire le HTML pour le PDF
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

<div><b>Usine: '.$rrw['serviceName'].'</b></div>
<div class="title">Liste des heures supplémentaires</div>

<table>
<thead>
<tr>
<th>#</th>
<th>Nom & Prénom</th>
<th>No. d\'identité</th>
<th>Badge</th>
<th>Poste</th>
<th>Début</th>
<th>Fin</th>
<th>Heures</th>
<th>Montant</th>
<th>Signature</th>
</tr>
</thead>
<tbody>';

$cnt = 1;
$totalMontant = 0;

if(mysqli_num_rows($ret) > 0){
    while($row = mysqli_fetch_assoc($ret)){
        $html .= '<tr>
        <td>'.$cnt.'</td>
        <td>'.$row['firstName'].' '.$row['lastName'].'</td>
        <td>'.$row['identite'].'</td>
        <td>'.$row['admissionNumber'].'</td>
        <td>'.$row['poste'].'</td>
        <td>'.$row['heureDebut'].'</td>
        <td>'.$row['heureFin'].'</td>
        <td>'.$row['heures'].'</td>
        <td style="font-weight:bold;">'.number_format($row['montant'],0,',',' ').' Fbu</td>
        <td></td>
        </tr>';
        $totalMontant += $row['montant'];
        $cnt++;
    }

    // Total
    $html .= '<tr style="font-weight:bold;">
        <td colspan="8">Total</td>
        <td>'.number_format($totalMontant,0,',',' ').' Fbu</td>
        <td></td>
    </tr>';
}

// Ligne des signatures
$html .= '</tbody></table>
<br>
<table>
<tr style="font-weight:bold;">
<td colspan="4" style="text-align:left;border:0;">Chef d\'usine : '.$teacher['firstName'].' '.$teacher['lastName'].'</td>
<td colspan="6" style="text-align:right;border:0;">Approuvée par l\'assistant du Directeur Général</td>
</tr>
</table>';

// ===== Génération du PDF =====
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // format paysage
$dompdf->render();
$dompdf->stream("Heures_supplementaires.pdf", ["Attachment" => true]);
exit;
?>