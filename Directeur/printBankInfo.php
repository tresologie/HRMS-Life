<?php
require '../vendor/autoload.php'; 
include '../Includes/dbcon.php';

date_default_timezone_set('Africa/Bujumbura');

use Dompdf\Dompdf;
use Dompdf\Options;

// Requête pour récupérer toutes les informations bancaires
$query = "
    SELECT 
        b.Id,
        b.identite,
        b.bankName,
        b.bankNumber,
        b.dateAdded,
        s.firstName,
        s.lastName,
        s.admissionNumber,
        s.poste
    FROM tblBankInfo b
    INNER JOIN tblemployees s ON s.admissionNumber = b.admissionNo
    ORDER BY s.firstName ASC, s.lastName ASC
";

$result = $conn->query($query);
$totalRecords = $result->num_rows;

// Préparer les données
$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}

// === HTML pour le PDF ===
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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

    <div class="title">LISTE DES INFORMATIONS BANCAIRES DES EMPLOYÉS</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom & Prénom</th>
                <th>N° Identité</th>
                <th>Banque</th>
                <th>N° Compte</th>
                <th>Date d\'enregistrement</th>
            </tr>
        </thead>
        <tbody>';

$sn = 0;
foreach ($employees as $row) {
    $sn++;
    $dateAdded = date('d/m/Y', strtotime($row['dateAdded']));

    $html .= '
            <tr>
                <td class="center">' . $sn . '</td>
                <td>' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '</td>
                <td class="center">' . htmlspecialchars($row['identite']) . '</td>
                <td>' . htmlspecialchars($row['bankName']) . '</td>
                <td class="center font-monospace">' . htmlspecialchars($row['bankNumber']) . '</td>
                <td class="center">' . $dateAdded . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        Good Life Service<br>
        Cartier Industriel – Chausse d\'Uvira – Tel: +257 68 50 50 50<br>
        Burundi – Bujumbura 
    </div>

</body>
</html>';

// === Génération du PDF ===
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Téléchargement direct du PDF
$dompdf->stream("informations_bancaires_employes_" . date("Y-m-d") . ".pdf", ["Attachment" => true]);

exit;
?>