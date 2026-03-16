<?php
error_reporting();
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// ===== Récupérer toutes les dates d'enregistrement disponibles =====
$datesResult = $conn->query("
    SELECT DISTINCT DATE(dateEnregistrement) AS dateOnly 
    FROM tblsupp_resume 
    ORDER BY dateOnly DESC
");

$availableDates = [];
while ($row = $datesResult->fetch_assoc()) {
  $availableDates[] = $row['dateOnly'];
}

// ===== Date sélectionnée par défaut (dernière date) =====
$selectedDate = $availableDates[0] ?? date('d-m-Y');

// ===== Si l'utilisateur a filtré =====
if (isset($_POST['view']) && !empty($_POST['selectedDate'])) {
  $selectedDate = $_POST['selectedDate'];
}

// ===== Sécuriser la variable pour la requête =====
$selectedDate = $conn->real_escape_string($selectedDate);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Historique Heures Supplémentaires</title>
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

        <div class="d-sm-flex align-items-center justify-content-between">
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">
            Historique des heures supplémentaires
          </h6>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="downloadHistoriSupp.php">Exporter</a>(Excel)</li>
            <li class="breadcrumb-item"><a href="printHistoriSupp.php">Imprimer</a>(PDF)</li>
          </ol>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active">Heures enregistrées</li>
          </ol>

        </div>

        <div class="container-fluid" id="container-wrapper">
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-4">
                        <label>Selectionner la date d'enregistrement <span class="text-danger">*</span></label>
                        <select name="selectedDate" id="selectedDate" class="form-control" required>
                          <?php foreach ($availableDates as $date): ?>
                            <option value="<?= $date ?>" <?= $date == $selectedDate ? 'selected' : '' ?>>
                              <?= date("d/m/Y", strtotime($date)) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <button type="submit" name="view" class="btn btn-primary">Afficher</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-bordered table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Nom & Prénom</th>
                        <th>Badge</th>
                        <th>Usine</th>
                        <th>Poste</th>
                        <th>De</th>
                        <th>Au</th>
                        <th>Jours</th>
                        <th>Total payé</th>
                        <th>Date d'enregistrement</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php

                      // ===== Requête pour afficher les données de la date sélectionnée =====
                      $query = " SELECT id, admissionNumber, nom,prenom,identite, usine, poste, dateDebut, dateFin, nombreJours, totalPaye, dateEnregistrement
    FROM tblsupp_resume
    ORDER BY nom ASC ";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn = 0;
                      if ($num > 0) {
                        while ($rows = $rs->fetch_assoc()) {
                          $sn = $sn + 1;
                          echo "
        <tr>
          <td>" . $sn . "</td>
          <td><b>" . $rows['nom'] . " " . $rows['prenom'] . "</b> </br> " . $rows['identite'] . "</td>
          <td>" . $rows['admissionNumber'] . "</td>
          <td>" . $rows['usine'] . "</td>
          <td>" . $rows['poste'] . "</td>
          <td>" . date("d/m", strtotime($rows['dateDebut'])) . "</td>
          <td>" . date("d/m", strtotime($rows['dateFin'])) . "</td>
          <td>" . $rows['nombreJours'] . "</td>
          <td style='font-weight:bold;'>" . number_format($rows['totalPaye'], 0, ',', ' ') . " Fbu</td>
          <td>" . date("d-m-Y H:i", strtotime($rows['dateEnregistrement'])) . "</td>
         
        </tr>";
                        }
                      } else {
                        echo
                        "<div class='alert alert-danger' role='alert'>
      Non trouvés!
      </div>";
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
      <!---Container Fluid-->
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


</body>

</html>