<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// --- Déterminer période par défaut 28->28 ---
$today = date('Y-m-d');
$day = date('d');
$month = date('m');
$year = date('Y');

if(isset($_POST['view'])){
    // Si formulaire soumis
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];

    // Limiter à 30 jours max
    $diff = (strtotime($toDate) - strtotime($fromDate)) / (60*60*24) + 1;
    if($diff > 30){
        $toDate = date('Y-m-d', strtotime($fromDate. ' +29 days'));
    }

} else {
    // Par défaut du 28 du mois précédent au 28 du mois courant
    if($day >= 28){
        $fromDate = date('Y-m-28');
        $toDate = date('Y-m-d', strtotime("$fromDate +1 month -1 day"));
    } else {
        $fromDate = date('Y-m-d', strtotime('28 last month'));
        $toDate = date('Y-m-28');
    }
}

// --- Récupérer les présences ---
$query = "SELECT 
    tblstudents.admissionNumber,
    tblstudents.firstName,
    tblstudents.lastName,
    tblstudents.poste,
    tblstudents.identite,
    tblclass.className,
    tblattendance.dateTaken,
    tblattendance.status
FROM tblstudents
INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
LEFT JOIN tblattendance ON tblattendance.admissionNumber = tblstudents.admissionNumber
    AND tblattendance.dateTaken BETWEEN '$fromDate' AND '$toDate'
ORDER BY tblclass.className ASC, tblstudents.firstName ASC, tblattendance.dateTaken ASC";

$rs = $conn->query($query);

// --- Préparer tableau pivot ---
$dates = [];
$data = [];
while($row = $rs->fetch_assoc()){
    $emp = $row['admissionNumber'];
    $date = $row['dateTaken'] ?? null;
    $status = $row['status'] ?? 'A';

    if($date) $dates[$date] = $date;

    $data[$emp]['name'] = $row['firstName'].' '.$row['lastName'].'<br>'.$row['identite'];
    $data[$emp]['badge'] = $row['admissionNumber'];
    $data[$emp]['usine'] = $row['className'];
    $data[$emp]['poste'] = $row['poste'];

    if($date){
        $data[$emp]['values'][$date] = ($status == 1) ? 'P' : 'A';
    }
}

ksort($dates); // Trier les dates

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
  <title>Présences employés</title>
</head>
<body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php"; ?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php"; ?>

      <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">Présences employés</h1>
        </div>

        <!-- Formulaire filtre -->
        <div class="card mb-4">
          <div class="card-header">Filtrer par période</div>
          <div class="card-body">
            <form method="post" class="row g-3">
              <div class="col-md-3">
                <label>De</label>
                <input type="date" name="fromDate" class="form-control" value="<?php echo $fromDate; ?>" required>
              </div>
              <div class="col-md-3">
                <label>À</label>
                <input type="date" name="toDate" class="form-control" value="<?php echo $toDate; ?>" required>
              </div>
              <div class="col-md-3 align-self-end">
                <button type="submit" name="view" class="btn btn-primary">Afficher</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Tableau pivot -->
        <div class="card mb-4">
          <div class="card-header">Tableau des présences</div>
          <div class="table-responsive p-3">
            <table class="table table-hover table-bordered" id="dataTableHover">
              <?php
              if(!empty($data)){
                  echo "<thead class='thead-light'><tr>";
                  echo "<th>Nom & Prénom</th><th>Badge</th><th>Usine</th><th>Poste</th>";
                  foreach($dates as $date){
                      echo "<th>".date('d/m', strtotime($date))."</th>";
                  }
                  echo "<th style='background:#d4edda;'>TOTAL P</th>";
                  echo "</tr></thead><tbody>";

                  $currentClass = '';
                  foreach($data as $emp => $info){
                      if($currentClass != $info['usine']){
                          echo "<tr style='background:#f8f9fc; font-weight:bold;'>
                                  <td colspan='".(count($dates)+5)."'>".$info['usine']."</td>
                                </tr>";
                          $currentClass = $info['usine'];
                      }

                      echo "<tr>";
                      echo "<td>".$info['name']."</td>";
                      echo "<td>".$info['badge']."</td>";
                      echo "<td>".$info['usine']."</td>";
                      echo "<td>".$info['poste']."</td>";

                      $totalP = 0;
                      foreach($dates as $date){
                          $value = isset($info['values'][$date]) ? $info['values'][$date] : 'A';
                          if($value=='P') $totalP++;
                          echo "<td style='font-weight:bold;'>".$value."</td>";
                      }
                      echo "<td style='font-weight:bold; background:#d4edda;'>".$totalP."</td>";
                      echo "</tr>";
                  }
                  echo "</tbody>";
              } else {
                  echo "<tr><td colspan='".(count($dates)+5)."'><div class='alert alert-danger'>Non trouvés!</div></td></tr>";
              }
              ?>
            </table>
          </div>
        </div>

      </div>
    </div>
    <?php include "Includes/footer.php"; ?>
  </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
    $('#dataTableHover').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        },
        scrollX: true,
        ordering: false
    });
});
</script>
</body>
</html>