<?php
// === AUCUN espace, aucun texte, aucun echo AVANT cette ligne ===

include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// Récupérer les dates disponibles (DESC = plus récente en premier)
$datesResult = $conn->query("
    SELECT DISTINCT DATE(date_validation) AS dateOnly 
    FROM tblsalaire_resume 
    ORDER BY dateOnly DESC
");

$availableDates = [];
while ($row = $datesResult->fetch_assoc()) {
    $availableDates[] = $row['dateOnly'];
}

// Déterminer la date à exporter
if (isset($_POST['view']) && !empty($_POST['selectedDate'])) {
    $selectedDate = $_POST['selectedDate'];
} else {
    $selectedDate = $availableDates[0] ?? null;
}

if (!$selectedDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
    die("Aucune date valide disponible.");
}

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

// === Headers pour fichier Excel (.xls) ===
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"Historique_Salaires_" . str_replace('-', '', $selectedDate) . ".xls\"");
header("Pragma: no-cache");
header("Expires: 0");

// Début contenu
echo '<html>';
echo '<head>';
echo '<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8">';
echo '</head>';
echo '<body>';

echo '<table border="0" cellpadding="3" cellspacing="0">';

// En-tête document
echo '<tr>';
echo '<td colspan="4">Life Company</td>';
echo '<td colspan="5" style="text-align:right;">Le ' . $today . '</td>';
echo '</tr>';

echo '<tr><td colspan="9">&nbsp;</td></tr>';

echo '<tr>';
echo '<td colspan="9" style="font-weight:bold; text-decoration: underline; text-align:center; font-size:14pt;">';
echo 'Historique des salaires validés le ' . $dateAffichee;
echo '</td>';
echo '</tr>';

echo '<tr><td colspan="9">&nbsp;</td></tr>';
echo '</table>';
echo '<table border="1">';

// En-têtes colonnes
echo '<tr style="font-weight:bold;">';
echo '<td>#</td>';
echo '<td>Nom & Prénom</td>';
echo '<td>Identité</td>';
echo '<td>Prés</td>';
echo '<td>Abs</td>';
echo '<td>Salaire base (Fbu)</td>';
echo '<td>Salaire recevable (Fbu)</td>';
echo '<td>N° Compte</td>';
echo '<td>Banque / MF</td>';
echo '<td>Validé le</td>';
echo '</tr>';


// Données
$cnt = 1;

if ($rs->num_rows > 0) {
    while ($row = $rs->fetch_assoc()) {
        $nomComplet = $row['nom'] . ' ' . $row['prenom'];
        $identite   = $row['identite'] ?: '-';
        $compte     = $row['compte_bancaire'] ?: '-';
        $banque     = $row['banque'] ?: '-';
        $validation = date("d/m/Y H:i", strtotime($row['date_validation']));

        echo '<tr>';
        echo '<td>' . $cnt . '</td>';
        echo '<td>' . $nomComplet . '</td>';
        echo '<td>' . $identite . '</td>';
        echo '<td>' . $row['presences'] . '</td>';
        echo '<td>' . $row['absences'] . '</td>';
        echo '<td>' . number_format($row['salaire_base'], 0, ',', ' ') . '</td>';
        echo '<td><b>' . number_format($row['salaire_receivable'], 0, ',', ' ') . '</b></td>';
        echo '<td>' . $compte . '</td>';
        echo '<td>' . $banque . '</td>';
        echo '<td>' . $validation . '</td>';
        echo '</tr>';

        $cnt++;
    }
} else {
    echo '<tr>';
    echo '<td colspan="10" style="text-align:center; padding:20px;">Aucun salaire validé à cette date</td>';
    echo '</tr>';
}

echo '</table>';

echo '</body>';
echo '</html>';

$stmt->close();
$conn->close();

exit;
