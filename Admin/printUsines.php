<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

date_default_timezone_set('Africa/Bujumbura');

$todaysDate = date("d-m-Y");
$filename = "Liste_chefs_usines.pdf";

// Récupérer tous les chefs et leurs usines
$ret = mysqli_query($conn,"SELECT t.Id, t.dateCreated, c.serviceName, 
    t.firstName, t.lastName, t.emailAddress, t.phoneNo
    FROM tblchef t
    INNER JOIN tblservice c ON c.Id = t.classId
    ORDER BY c.serviceName, t.firstName ASC");

// Construire le HTML
$html = '<!DOCTYPE html>
<html>
<head>
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

<div class="title">Liste de tous les usines et chefs de Life Company</div>
';

// Tableau principal
$html .= '
<table>
<thead>
<tr>
<th>#</th>
<th>Nom & Prénom</th>
<th>Email</th>
<th>Téléphone</th>
<th>Usine</th>
<th>Date Création</th>
</tr>
</thead>
<tbody>
';

$cnt = 1;

if(mysqli_num_rows($ret) > 0){
    while ($row = mysqli_fetch_assoc($ret)) {
        $html .= '<tr>
        <td>'.$cnt.'</td>
        <td>'.$row['firstName'].' '.$row['lastName'].'</td>
        <td>'.$row['emailAddress'].'</td>
        <td>'.$row['phoneNo'].'</td>
        <td>'.$row['serviceName'].'</td>
        <td>'.$row['dateCreated'].'</td>
        </tr>';
        $cnt++;
    }
} else {
    $html .= '<tr>
    <td colspan="6" style="text-align:center;">Aucun chef ou usine trouvé</td>
    </tr>';
}

$html .= '</tbody></table></body></html>';

// Générer le PDF
$dompdf = new Dompdf();
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html);
$dompdf->render();

// Télécharger
$dompdf->stream($filename, array("Attachment" => true));
exit();
?>