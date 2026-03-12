<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

require_once '../vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

// ===== Dates par défaut (7 derniers jours) =====
$fromDate = date('Y-m-d', strtotime('-6 days'));
$toDate   = date('Y-m-d');

if (isset($_POST['view']) || isset($_POST['save'])) {
    $type = $_POST['type'] ?? '1';
    $fromDate = $_POST['fromDate'] ?? $fromDate;
    $toDate   = $_POST['toDate'] ?? $toDate;

    if ($type == "2" && isset($_POST['singleDate'])) {
        $fromDate = $toDate = $_POST['singleDate'];
    } elseif ($type == "3" && isset($_POST['fromDate']) && isset($_POST['toDate'])) {
        $fromDate = $_POST['fromDate'];
        $toDate   = $_POST['toDate'];
        $diff = (strtotime($toDate) - strtotime($fromDate)) / (60 * 60 * 24) + 1;
        if ($diff > 7) {
            $toDate = date('Y-m-d', strtotime($fromDate . ' + 6 days'));
        }
    }
}

// ===== Requête pour récupérer les données non encore enregistrées =====
$query = "SELECT 
    s.admissionNumber,
    s.firstName,
    s.lastName,
    s.identite,
    s.poste,
    c.serviceName,
    sp.dateTimeTaken,
    FLOOR(sp.montant / 100) * 100 AS montant
FROM tblsupp sp
INNER JOIN tblservice c ON c.Id = sp.classId
INNER JOIN tblemployees s ON s.admissionNumber = sp.admissionNo
WHERE sp.dateTimeTaken BETWEEN '$fromDate' AND '$toDate'
AND NOT EXISTS (
    SELECT 1 FROM tblsupp_resume r
    WHERE r.admissionNumber = s.admissionNumber
    AND r.dateDebut = '$fromDate'
    AND r.dateFin = '$toDate'
)
ORDER BY c.serviceName ASC, s.firstName ASC";

$rs = $conn->query($query);

// ===== Préparer le tableau et total =====
$dates = [];
$data  = [];
$totalGeneral = 0;

while ($row = $rs->fetch_assoc()) {
    $emp = $row['admissionNumber'];
    $date = date('Y-m-d', strtotime($row['dateTimeTaken']));
    $montant = (float)$row['montant'];

    $dates[$date] = $date;

    $data[$emp]['firstName']    = $row['firstName'];
    $data[$emp]['lastName']     = $row['lastName'];
    $data[$emp]['identite']     = $row['identite'];
    $data[$emp]['badge']        = $emp;
    $data[$emp]['usine']        = $row['serviceName'];
    $data[$emp]['poste']        = $row['poste'];
    $data[$emp]['values'][$date] = $montant;

    $totalGeneral += $montant;
}

// Trier et limiter à 7 dates max
ksort($dates);
$dates = array_slice($dates, 0, 7, true);

// ===== Enregistrement + Génération PDF =====
$statusMsg = "";

if (isset($_POST['save']) && !empty($data)) {

    $successCount = 0;

    foreach ($data as $emp => $info) {
        $totalEmploye = array_sum($info['values'] ?? []);
        $jours = count(array_filter($info['values'] ?? [], fn($v) => $v > 0));

        $empEsc   = mysqli_real_escape_string($conn, $emp);
        $firstName = mysqli_real_escape_string($conn, $info['firstName']);
        $lastName  = mysqli_real_escape_string($conn, $info['lastName']);
        $identite  = mysqli_real_escape_string($conn, $info['identite']);
        $usine     = mysqli_real_escape_string($conn, $info['usine']);
        $poste     = mysqli_real_escape_string($conn, $info['poste']);

        // Vérifier si déjà enregistré pour cet employé et cette période
        $check = mysqli_query($conn, "
            SELECT id FROM tblsupp_resume 
            WHERE admissionNumber = '$empEsc' 
              AND dateDebut = '$fromDate' 
              AND dateFin = '$toDate'
        ");

        if (mysqli_num_rows($check) == 0) {
            $insertQuery = "
                INSERT INTO tblsupp_resume (
                    admissionNumber, nom, prenom, identite, usine, poste, 
                    dateDebut, dateFin, nombreJours, totalPaye
                ) VALUES (
                    '$empEsc', '$firstName', '$lastName', '$identite', '$usine', '$poste',
                    '$fromDate', '$toDate', $jours, $totalEmploye
                )
            ";

            if (mysqli_query($conn, $insertQuery)) {
                $successCount++;
            }
        }
    }

    if ($successCount > 0) {
        $statusMsg = "<div class='alert alert-success mt-2'>
            $successCount employé(s) enregistré(s) avec succès !<br>
            Téléchargement du PDF en cours...
        </div>";

        // ───────────────────────────────────────────────
        // Génération et téléchargement du PDF
        // ───────────────────────────────────────────────
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <style>
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
<div class="title">Heures supplementaires du ' . date("d/m/Y", strtotime($fromDate)) . ' au ' . date("d/m/Y", strtotime($toDate)) . '</h2>
</div>
            <table>
                <tr>
                    <th>Nom & Prénom</th>
                    <th>Usine</th>
                    <th>Poste</th>';

        foreach ($dates as $date) {
            $html .= '<th>' . date("d/m", strtotime($date)) . '</th>';
        }

        $html .= '<th>Total</th></tr>';

        foreach ($data as $emp => $info) {
            $totalEmp = array_sum($info['values'] ?? []);
            $html .= '
                <tr>
                    <td class="left"><b>' . htmlspecialchars($info['firstName'] . ' ' . $info['lastName']) . '</b><br>
                        <small>' . htmlspecialchars($info['identite']) . '</small></td>
                    <td class="left">' . htmlspecialchars($info['usine']) . '</td>
                    <td class="left">' . htmlspecialchars($info['poste']) . '</td>';

            foreach ($dates as $date) {
                $val = $info['values'][$date] ?? 0;
                $html .= '<td class="right">' . ($val > 0 ? number_format($val, 0, ',', ' ') : '-') . '</td>';
            }

            $html .= '<td class="right">' . number_format($totalEmp, 0, ',', ' ') . ' Fbu</td></tr>';
        }

        $html .= '
                <tr class="total-row">
                    <td colspan="' . (count($dates) + 3) . '" class="right">TOTAL GÉNÉRAL</td>
                    <td class="right">' . number_format($totalGeneral, 0, ',', ' ') . ' Fbu</td>
                </tr>
            </table>
            <div class="footer">
                Good Life Service – Chaussée d’Uvira – Tél: +257 68 50 50 50<br>
                Bujumbura – Burundi – ' . date("d/m/Y") . '
            </div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = "Heures_Supp_" . date("Ymd", strtotime($fromDate)) . "_au_" . date("Ymd", strtotime($toDate)) . ".pdf";
        $dompdf->stream($filename, ["Attachment" => true]);

        exit; // Arrêt après envoi du PDF
    } else {
        $statusMsg = "<div class='alert alert-warning mt-2'>
            Aucune nouvelle donnée enregistrée (déjà validée ?)
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/logo/life.jpg" rel="icon">
    <title>Heures supplémentaires</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/ruang-admin.min.css" rel="stylesheet">

    <script>
        function typeDropDown(str) {
            if (str == "") {
                document.getElementById("txtHint").innerHTML = "";
                return;
            }
            var xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            }
            xmlhttp.open("GET", "ajaxCallTypes.php?tid=" + str, true);
            xmlhttp.send();
        }
    </script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include "Includes/sidebar.php"; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include "Includes/topbar.php"; ?>

                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h6 class="font-weight-bold text-primary" style="margin-left:30px">
                        <?php if ($fromDate == $toDate): ?>
                            Heures supplémentaires du <?= date("d/m/Y", strtotime($fromDate)) ?>
                        <?php else: ?>
                            Heures supplémentaires du <?= date("d/m/Y", strtotime($fromDate)) ?> au <?= date("d/m/Y", strtotime($toDate)) ?>
                        <?php endif; ?>
                    </h6>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="downloadSuppl.php?from=<?= $fromDate ?>&to=<?= $toDate ?>">Exporter</a>(Excel)</li>
                        <li class="breadcrumb-item"><a href="printSuppl.php?from=<?= $fromDate ?>&to=<?= $toDate ?>">Imprimer</a>(PDF)</li>
                    </ol>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="./">Accueil</a></li>
                        <li class="breadcrumb-item active">Heures suppl</li>
                    </ol>
                </div>

                <div class="container-fluid" id="container-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-6">
                                                <label>Un jour/7jours/De ...à...<span class="text-danger">*</span></label>
                                                <select required name="type" onchange="typeDropDown(this.value)" class="form-control mb-3">
                                                    <option value="">--Choisir--</option>
                                                    <option value="2" <?= (isset($_POST['type']) && $_POST['type'] == "2") ? 'selected' : '' ?>>Un jour</option>
                                                    <option value="1" <?= (isset($_POST['type']) && $_POST['type'] == "1") ? 'selected' : '' ?>>Cette semaine</option>
                                                    <option value="3" <?= (isset($_POST['type']) && $_POST['type'] == "3") ? 'selected' : '' ?>>De ...à...</option>
                                                </select>
                                            </div>
                                            <div class="col-xl-4">
                                                <h4>Somme</h4>
                                                <h1 class="form-control font-weight-bold" style="color:#6777EF;height:40px;font-size:20px;">
                                                    <?= number_format($totalGeneral, 0, ',', ' ') ?> Fbu
                                                </h1>
                                            </div>
                                        </div>
                                        <div id="txtHint"></div>

                                        <input type="hidden" name="fromDate" value="<?= $fromDate ?>">
                                        <input type="hidden" name="toDate" value="<?= $toDate ?>">

                                        <button type="submit" name="view" class="btn btn-primary">Afficher</button>
                                        <button type="submit" name="save" class="btn btn-success">Valider et Enregistrer</button>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card mb-4">
                                        <div class="table-responsive p-3">
                                            <table class="table align-items-center table-bordered table-hover" id="dataTableHover">
                                                <thead class="thead-light">
                                                    <?= $statusMsg ?>
                                                    <?php if (!empty($data)) { ?>
                                                        <table class="table table-bordered table-hover" id="dataTableHover">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Nom & Prénom</th>
                                                                    <th>Usine</th>
                                                                    <th>Poste</th>
                                                                    <?php foreach ($dates as $date): ?>
                                                                        <th><?= date("d/m", strtotime($date)) ?></th>
                                                                    <?php endforeach; ?>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($data as $emp => $info):
                                                                    $totalEmploye = array_sum($info['values'] ?? []); ?>
                                                                    <tr>
                                                                        <td><b><?= htmlspecialchars($info['firstName']) ?> <?= htmlspecialchars($info['lastName']) ?></b><br><?= htmlspecialchars($info['identite']) ?></td>
                                                                        <td><?= htmlspecialchars($info['usine']) ?></td>
                                                                        <td><?= htmlspecialchars($info['poste']) ?></td>
                                                                        <?php foreach ($dates as $date):
                                                                            $value = $info['values'][$date] ?? 0; ?>
                                                                            <td class="text-right"><?= number_format($value, 0, ',', ' ') ?></td>
                                                                        <?php endforeach; ?>
                                                                        <td class="text-right font-weight-bold"><?= number_format($totalEmploye, 0, ',', ' ') ?> Fbu</td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php } else { ?>
                                                        <div class='alert alert-danger mt-2'>Aucune donnée à afficher ou déjà validée pour cette période.</div>
                                                    <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
                <script src="../vendor/jquery/jquery.min.js"></script>
                <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
                <script src="js/ruang-admin.min.js"></script>

                <script>
                    $(document).ready(function() {
                        $('#dataTableHover').DataTable({
                            scrollX: true,
                            autoWidth: false,
                            language: {
                                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</body>

</html>