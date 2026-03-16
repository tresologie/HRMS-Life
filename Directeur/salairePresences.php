<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

require_once '../vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Africa/Bujumbura');

$today = date('Y-m-d');
$currentDay = (int)date('d');

if ($currentDay >= 28) {
  $fromDate = date('Y-m-28');
  $toDate   = date('Y-m-28', strtotime('+1 month -1 day')); // jusqu'au 27 du mois suivant
} else {
  $fromDate = date('Y-m-28', strtotime('-1 month'));
  $toDate   = date('Y-m-28', strtotime('-1 day')); // jusqu'au 27 du mois en cours
}

// Nombre de jours calendaires dans la période
$joursPeriode = (strtotime($toDate) - strtotime($fromDate)) / 86400 + 1;

// =============================================
//   TRAITEMENT VALIDATION + PDF
// =============================================
$statusMsg = '';

if (isset($_POST['save'])) {

  // 1. Vérifier si cette période a déjà été validée (même pour un seul employé)
  $checkSql = "SELECT COUNT(*) as nb FROM tblsalaire_resume 
                 WHERE periode_debut = ? AND periode_fin = ?";
  $checkStmt = $conn->prepare($checkSql);
  $checkStmt->bind_param("ss", $fromDate, $toDate);
  $checkStmt->execute();
  $checkResult = $checkStmt->get_result();
  $rowCheck = $checkResult->fetch_assoc();
  $alreadyValidated = $rowCheck['nb'] > 0;
  $checkStmt->close();

  if ($alreadyValidated) {
    $statusMsg = "<div class='alert alert-warning mt-3'>
            <strong>Validation impossible :</strong><br>
            Cette période du " . date('d/m/Y', strtotime($fromDate)) . " au " . date('d/m/Y', strtotime($toDate)) . "<br>
            a déjà été validée. Impossible de la valider à nouveau.
        </div>";
  } else {
    // 2. Pas encore validé → on traite
    $successCount = 0;

    $sql = "
            SELECT 
                s.admissionNumber,
                s.firstName,
                s.lastName,
                s.identite,
                s.salaire,
                COALESCE(b.bankName, '-') AS bankName,
                COALESCE(b.bankNumber, '-') AS bankNumber,
                COUNT(CASE WHEN a.status = 1 THEN 1 END) AS nbPresences,
                COUNT(CASE WHEN a.status = 0 THEN 1 END) AS nbAbsences
            FROM tblemployees s
            LEFT JOIN tblattendance a ON a.admissionNo = s.admissionNumber 
                AND a.dateTimeTaken BETWEEN ? AND ?
            LEFT JOIN tblBankInfo b ON b.admissionNo = s.admissionNumber
            GROUP BY s.admissionNumber
            ORDER BY s.firstName, s.lastName";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fromDate, $toDate);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $admissionNo   = $row['admissionNumber'];
      $pres          = (int)$row['nbPresences'];
      $abs           = (int)$row['nbAbsences'];
      $salaireBase   = (float)$row['salaire'];
      $tauxPresence  = $joursPeriode > 0 ? $pres / $joursPeriode : 0;
      $salaireCalc   = floor(($salaireBase * $tauxPresence) / 100) * 100;

      // Insertion
      $insertSql = "
                INSERT INTO tblsalaire_resume (
                    admissionNumber, nom, prenom, identite,
                    salaire_base, presences, absences, salaire_receivable,
                    periode_debut, periode_fin,
                    compte_bancaire, banque
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

      $insertStmt = $conn->prepare($insertSql);
      $insertStmt->bind_param(
        "ssssdiiissss",
        $admissionNo,
        $row['firstName'],
        $row['lastName'],
        $row['identite'],
        $salaireBase,
        $pres,
        $abs,
        $salaireCalc,
        $fromDate,
        $toDate,
        $row['bankNumber'],
        $row['bankName']
      );

      if ($insertStmt->execute()) {
        $successCount++;
      }
      $insertStmt->close();
    }
    $stmt->close();

    if ($successCount > 0) {
      $statusMsg = "<div class='alert alert-success mt-3'>
                $successCount employé(s) validé(s) avec succès.<br>
                Le PDF va se télécharger automatiquement...
            </div>";

      // ───────────────────────────────────────────────
      // Génération PDF – style nettoyé (sans soulignement parasite)
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
                    body { 
                        font-family: Arial, Helvetica, sans-serif; 
                        font-size: 10pt; 
                        margin: 15mm 12mm; 
                        color: #1f2937; 
                    }
                    h2 { 
                        text-align: center; 
                        color: #1e40af; 
                        margin: 0 0 25px 0; 
                        font-size: 16pt; 
                    }
                    .header { 
                        display: flex; 
                        justify-content: space-between; 
                        align-items: center; 
                        margin-bottom: 20px; 
                        padding-bottom: 10px; 
                        border-bottom: 2px solid #6366f1; 
                    }
                    .logo { 
                        font-size: 18pt; 
                        font-weight: bold; 
                        color: #4f46e5; 
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
                    .left { text-align: left; }
                    .right { text-align: right; }
                    .total-row { 
                        font-weight: bold; 
                        background-color: #dbeafe; 
                    }
                    .footer { 
                        margin-top: 40px; 
                        text-align: center; 
                        font-size: 9pt; 
                        color: #64748b; 
                    }
                    /* Suppression forcée des soulignements indésirables */
                    a, u, .title { text-decoration: none !important; }
                </style>
            </head>
            <body>

            <div class="header">
                <div class="logo">Life Company</div>
                <div><strong>Le ' . date("d/m/Y") . '</strong></div>
            </div>

            <h2>Salaire des employés – Période du ' . date('d/m/Y', strtotime($fromDate)) . ' au ' . date('d/m/Y', strtotime($toDate)) . '</h2>

            <table>
                <tr>
                    <th>#</th>
                    <th class="left">Nom & Prénom</th>
                    <th>Identité</th>
                    <th>Présences</th>
                    <th>Absences</th>
                    <th>Salaire base</th>
                    <th class="right">Recevable</th>
                    <th>N° Compte</th>
                    <th class="left">Banque</th>
                </tr>';

      // Recharger les données pour le PDF
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $fromDate, $toDate);
      $stmt->execute();
      $resultPDF = $stmt->get_result();
      $sn = 0;

      while ($row = $resultPDF->fetch_assoc()) {
        $sn++;
        $pres = (int)$row['nbPresences'];
        $abs  = (int)$row['nbAbsences'];
        $salaireBase = (float)$row['salaire'];
        $taux = $joursPeriode > 0 ? $pres / $joursPeriode : 0;
        $recevable = floor(($salaireBase * $taux) / 100) * 100;

        $html .= "
                <tr>
                    <td>$sn</td>
                    <td class='left'>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>
                    <td>" . htmlspecialchars($row['identite'] ?: '-') . "</td>
                    <td>$pres</td>
                    <td>$abs</td>
                    <td class='right'>" . number_format($salaireBase, 0, ',', ' ') . "</td>
                    <td class='right total-row'>" . number_format($recevable, 0, ',', ' ') . "</td>
                    <td>" . htmlspecialchars($row['bankNumber']) . "</td>
                    <td class='left'>" . htmlspecialchars($row['bankName']) . "</td>
                </tr>";
      }

      $html .= '
            </table>

            <div class="footer">
                Good Life Service<br>
                Cartier Industriel – Chaussée d\'Uvira – Tél: +257 68 50 50 50<br>
                Burundi – Bujumbura 
            </div>

            </body>
            </html>';

      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'landscape');
      $dompdf->render();

      $filename = "Salaire_" . date("Y-m", strtotime($fromDate)) . ".pdf";
      $dompdf->stream($filename, ["Attachment" => true]);

      exit; // Arrêt après envoi du PDF
    } else {
      $statusMsg = "<div class='alert alert-warning mt-3'>
                Aucune nouvelle ligne enregistrée (peut-être déjà validée ?)
            </div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Salaire</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">

  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>

        <div class="container-fluid" id="container-wrapper">

          <div class="d-sm-flex align-items-center justify-content-between">
            <h6 class="font-weight-bold text-primary">Détail des présences et salaires</h6>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Salaire / Présences</li>
            </ol>
          </div>

          <?php if ($statusMsg) echo $statusMsg; ?>

          <!-- Tableau -->
          <div class="card shadow mb-4">
            <div class="card-body">
              <div class="table-responsive">
                <form method="post">
                  <table class="table table-bordered table-hover table-sm" id="dataTable">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Nom & Prénom</th>
                        <th>Identité</th>
                        <th>Présences</th>
                        <th>Absences</th>
                        <th>Salaire de base</th>
                        <th>Recevable</th>
                        <th>N° Compte</th>
                        <th>Banque</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $sql = "
                    SELECT 
                        s.admissionNumber,
                        s.firstName,
                        s.lastName,
                        s.identite,
                        s.salaire,
                        COALESCE(b.bankName, '-') AS bankName,
                        COALESCE(b.bankNumber, '-') AS bankNumber,
                        COUNT(CASE WHEN a.status = 1 THEN 1 END) AS nbPresences,
                        COUNT(CASE WHEN a.status = 0 THEN 1 END) AS nbAbsences
                    FROM tblemployees s
                    LEFT JOIN tblattendance a ON a.admissionNo = s.admissionNumber 
                        AND a.dateTimeTaken BETWEEN ? AND ?
                    LEFT JOIN tblBankInfo b ON b.admissionNo = s.admissionNumber
                    GROUP BY s.admissionNumber
                    ORDER BY s.firstName, s.lastName";

                      $stmt = $conn->prepare($sql);
                      $stmt->bind_param("ss", $fromDate, $toDate);
                      $stmt->execute();
                      $result = $stmt->get_result();

                      $sn = 0;
                      while ($row = $result->fetch_assoc()) {
                        $sn++;
                        $pres = (int)$row['nbPresences'];
                        $abs  = (int)$row['nbAbsences'];
                        $salaireBase = (float)($row['salaire'] ?? 0);
                        $tauxPresence = $joursPeriode > 0 ? $pres / $joursPeriode : 0;
                        $salaireCalcule = floor(($salaireBase * $tauxPresence) / 100) * 100;

                        echo "<tr>";
                        echo "<td class='text-center'>$sn</td>";
                        echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['identite'] ?: '-') . "</td>";
                        echo "<td class='text-center text-success font-weight-bold'>$pres</td>";
                        echo "<td class='text-center text-danger font-weight-bold'>$abs</td>";
                        echo "<td class='text-right'>" . number_format($salaireBase, 0, ',', ' ') . " Fbu</td>";
                        echo "<td class='text-right font-weight-bold'>" . number_format($salaireCalcule, 0, ',', ' ') . " Fbu</td>";
                        echo "<td class='font-monospace text-center'>" . htmlspecialchars($row['bankNumber']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['bankName']) . "</td>";
                        echo "</tr>";
                      }

                      if ($sn == 0) {
                        echo "<tr><td colspan='9' class='text-center py-4 text-muted'>Aucune donnée pour cette période</td></tr>";
                      }
                      $stmt->close();
                      ?>
                    </tbody>
                  </table>

                  <?php if ($sn > 0): ?>
                    <div class="mt-4 text-center">
                      <button type="submit" name="save" class="btn btn-success btn-lg px-5">
                        Valider & Télécharger PDF
                      </button>
                    </div>
                  <?php endif; ?>
                </form>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#dataTable').DataTable({
        scrollX: true,
        autoWidth: false,
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
      });
    });
  </script>
</body>

</html>