<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

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

// Nombre de jours calendaires dans la période (approximation)
$joursPeriode = (strtotime($toDate) - strtotime($fromDate)) / 86400 + 1;

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

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h6 class="font-weight-bold text-primary">Détail des présences et salaires</h6>
          
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active">Salaire / Présences</li>
          </ol>
        </div>


        <!-- Tableau -->
        <div class="card shadow mb-4">
          
          <div class="card-body">
            <div class="table-responsive">
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
                  // Requête principale
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

                      // Calcul salaire : proportion des présences
                      $tauxPresence = $joursPeriode > 0 ? $pres / $joursPeriode : 0;
                      $salaireCalcule = round($salaireBase * $tauxPresence);
                      $salaireCalcule = floor($salaireCalcule / 100) * 100;

                      echo "<tr>";
                      echo "<td class='text-center'>$sn</td>";
                      echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
                      echo "<td class='text-center'>" . htmlspecialchars($row['identite'] ?: '-') . "</td>";
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
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>


 <!-- Scroll to top -->
 <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>


  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
   <!-- Page level plugins -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script>
$(document).ready(function () {
  $('#dataTableHover').DataTable({
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