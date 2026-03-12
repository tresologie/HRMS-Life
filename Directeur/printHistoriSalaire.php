<?php
// IMPORTANT : RIEN AVANT ÇA (pas d'espace, pas d'echo, pas de HTML)

require_once '../vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// Récupérer la liste des dates disponibles (la plus récente en premier)
$datesResult = $conn->query("
    SELECT DISTINCT DATE(date_validation) AS dateOnly 
    FROM tblsalaire_resume 
    ORDER BY dateOnly DESC
");

$availableDates = [];
while ($row = $datesResult->fetch_assoc()) {
    $availableDates[] = $row['dateOnly'];
}

// Déterminer la date à utiliser pour le PDF
if (isset($_POST['view']) && !empty($_POST['selectedDate'])) {
    $selectedDate = $_POST['selectedDate'];
} else {
    // Par défaut : la date la plus récente (si elle existe)
    $selectedDate = $availableDates[0] ?? null;
}

if (!$selectedDate) {
    die("Aucune validation de salaire enregistrée dans la base.");
}

// Sécurité : on vérifie le format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    die("Date invalide.");
}

$selectedDateEsc = $selectedDate; // on utilisera prepared statement

// Requête préparée
$query = "
    SELECT 
        nom, prenom, identite,
        presences, absences,
        salaire_base, salaire_receivable,
        compte_bancaire, banque,
        date_validation
    FROM tblsalaire_resume
    WHERE DATE(date_validation) = ?
    ORDER BY nom ASC, prenom ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$rs = $stmt->get_result();

$dateAffichee = date("d/m/Y", strtotime($selectedDate));
$today = date("d/m/Y");

// Début HTML pour PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVuSans, Arial, sans-serif;
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
            margin: 20px 0 25px;
            font-size: 15pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
            text-align: center;
            font-size: 9.8pt;
        }

        th {
            background-color: #e0e7ff;
            color: #1e40af;
            font-weight: bold;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .strong {
            font-weight: bold;
        }

        .small {
            font-size: 8.5pt;
            color: #4b5563;
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
        <div><strong><?= htmlspecialchars($today) ?></strong></div>
    </div>

    <h2>Historique des salaires validés le <?= htmlspecialchars($dateAffichee) ?></h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th class="left">Nom & Prénom</th>
                <th>Prés</th>
                <th>Abs</th>
                <th>Salaire base</th>
                <th>Salaire recevable</th>
                <th>N° Compte</th>
                <th>Banque / MF</th>
                <th>Validé le</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cnt = 1;
            if ($rs->num_rows > 0):
                while ($row = $rs->fetch_assoc()):
            ?>
                    <tr>
                        <td><?= $cnt++ ?></td>
                        <td class="left">
                            <span class="strong"><?= htmlspecialchars($row['nom'] . ' ' . $row['prenom']) ?></span><br>
                            <span class="small"><?= htmlspecialchars($row['identite'] ?: '—') ?></span>
                        </td>
                        <td><?= $row['presences'] ?></td>
                        <td><?= $row['absences'] ?></td>
                        <td class="right"><?= number_format($row['salaire_base'], 0, ',', ' ') ?> Fbu</td>
                        <td class="right strong"><?= number_format($row['salaire_receivable'], 0, ',', ' ') ?> Fbu</td>
                        <td><?= htmlspecialchars($row['compte_bancaire'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($row['banque'] ?: '—') ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($row['date_validation'])) ?></td>
                    </tr>
                <?php
                endwhile;
            else:
                ?>
                <tr>
                    <td colspan="9" style="padding:30px; color:#666; text-align:center;">
                        Aucun salaire validé à cette date.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Good Life Service – Cartier Industriel – Chaussée d'Uvira<br>
        Tél : +257 68 50 50 50 – Bujumbura, Burundi
    </div>

</body>

</html>
<?php
$html = ob_get_clean();

// Configuration dompdf
$options = new Options();
$options->set('defaultFont', 'DejaVuSans'); // accents + caractères africains
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);   // sécurité (pas d'image distante)

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');

$filename = "Historique_Salaires_" . str_replace('-', '', $selectedDate) . ".pdf";

$dompdf->render();
$dompdf->stream($filename, ["Attachment" => true]);

// Nettoyage
$stmt->close();
$conn->close();

exit;
