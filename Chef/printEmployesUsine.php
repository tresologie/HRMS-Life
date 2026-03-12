<?php
// IMPORTANT : RIEN AVANT ÇA (pas d'espace, pas d'echo, pas de HTML)
require_once '../vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// Récupération du service/usine du chef connecté
$query = "SELECT tblservice.serviceName
          FROM tblchef
          INNER JOIN tblservice ON tblservice.Id = tblchef.classId
          WHERE tblchef.Id = '" . mysqli_real_escape_string($conn, $_SESSION['userId']) . "'";

$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();
$usineName = $rrw['serviceName'] ?? 'Usine inconnue';

// Date du jour
$todaysDate = date("d/m/Y");

// Requête des employés de cette usine
$ret = mysqli_query($conn, "
    SELECT 
        tblemployees.firstName,
        tblemployees.lastName,
        tblemployees.identite,
        tblemployees.admissionNumber,
        tblemployees.poste,
        tblemployees.dateCreated
    FROM tblemployees 
    INNER JOIN tblservice ON tblservice.Id = tblemployees.classId
    WHERE tblemployees.classId = '" . mysqli_real_escape_string($conn, $_SESSION['classId']) . "'
    ORDER BY tblemployees.firstName ASC
");

// Génération du HTML pour le PDF
$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            margin: 15mm 12mm;
            color: #1f2937;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
        }
        .logo {
            font-size: 18pt;
            font-weight: bold;
            color: #4f46e5;
        }
        h2 {
            text-align: center;
            color: #1e40af;
            margin: 20px 0 25px 0;
            font-size: 15pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background-color: #e0e7ff;
            font-weight: bold;
            color: #1e40af;
        }
        .left {
            text-align: left;
        }
        .right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9pt;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">Life Company</div>
    <div><strong>Le ' . $todaysDate . '</strong></div>
</div>

<h2>Liste des employés de l\'usine : ' . htmlspecialchars($usineName) . '</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th class="left">Nom & Prénom</th>
            <th>Identité</th>
            <th>Badge</th>
            <th>Poste</th>
            <th>Date d\'ajout</th>
        </tr>
    </thead>
    <tbody>';

$cnt = 1;
if (mysqli_num_rows($ret) > 0) {
    while ($row = mysqli_fetch_assoc($ret)) {
        $html .= '
        <tr>
            <td>' . $cnt . '</td>
            <td class="left">' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '</td>
            <td>' . htmlspecialchars($row['identite'] ?: '-') . '</td>
            <td>' . htmlspecialchars($row['admissionNumber']) . '</td>
            <td>' . htmlspecialchars($row['poste'] ?: '-') . '</td>
            <td>' . ($row['dateCreated'] ? date('d/m/Y', strtotime($row['dateCreated'])) : '-') . '</td>
        </tr>';
        $cnt++;
    }
} else {
    $html .= '
    <tr>
        <td colspan="6" style="text-align:center; padding:20px; color:#666;">
            Aucun employé trouvé pour cette usine.
        </td>
    </tr>';
}

$html .= '
    </tbody>
</table>

<div class="footer">
    Good Life Service – Cartier Industriel – Chaussée d\'Uvira<br>
    Tél : +257 68 50 50 50 – Bujumbura, Burundi
</div>

</body>
</html>';

// Génération du PDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // ou 'landscape' si tu préfères plus large

$filename = "Liste_Employes_" . str_replace(' ', '_', $usineName) . "_" . date("d-m-Y") . ".pdf";

$dompdf->render();
$dompdf->stream($filename, ["Attachment" => true]);

exit;
