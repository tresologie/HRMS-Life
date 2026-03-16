<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Date par défaut = aujourd'hui
date_default_timezone_set('Africa/Bujumbura');
$dateTaken = date('Y-m-d');
$totalGeneral = 0;

// Classe de l'enseignant
$query = "SELECT tblservice.serviceName
FROM tblchef
INNER JOIN tblservice ON tblservice.Id = tblchef.classId
Where tblchef.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

// Récupérer le nombre total d'étudiants/employés
$query1 = mysqli_query($conn, "SELECT * FROM tblemployees WHERE classId = '$_SESSION[classId]'");
$students = $query1 ? mysqli_num_rows($query1) : 0;

// Si l'utilisateur choisit une date
if (isset($_POST['view'])) {
  $dateTaken = $_POST['dateTaken']; // remplace la valeur par la sélection

  // Calculer la somme
  $queryTotal = "SELECT SUM(FLOOR(tblsupp.montant / 100) * 100) as total
                 FROM tblsupp
                 WHERE tblsupp.dateTimeTaken = '$dateTaken' AND tblsupp.classId = '$_SESSION[classId]'";

  $resultTotal = $conn->query($queryTotal);
  $rowTotal = $resultTotal->fetch_assoc();
  $totalGeneral = $rowTotal['total'] ?? 0;
} else {
  // Si formulaire pas soumis, calculer quand même le total d'aujourd'hui
  $queryTotal = "SELECT SUM(FLOOR(tblsupp.montant / 100) * 100) as total
                 FROM tblsupp
                 WHERE tblsupp.dateTimeTaken = '$dateTaken' AND tblsupp.classId = '$_SESSION[classId]'";
  $resultTotal = $conn->query($queryTotal);
  $rowTotal = $resultTotal->fetch_assoc();
  $totalGeneral = $rowTotal['total'] ?? 0;
}
// présent (montant aujourd'hui)
$query4 = mysqli_query($conn, "SELECT * FROM tblsupp 
                               WHERE montant != 0 
                               AND classId = '$_SESSION[classId]' 
                               AND DATE(dateTimeTaken) = '$dateTaken'");
$present = $query4 ? mysqli_num_rows($query4) : 0;
$absent = $students - $present;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Supplémentaire</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php"; ?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php"; ?>
        <!-- Topbar -->
        <div class="d-sm-flex align-items-center justify-content-between">
          <h6 class=" font-weight-bold text-primary" style="margin-left:30px">Heures supplementaires <b><?php echo $rrw['serviceName']; ?></b></h6>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="downloadSupp.php">Exporter</a>(Exel)</li>
            <li class="breadcrumb-item"><a href="printSuppl.php">Imprimer</a>(PDF)</li>

          </ol>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Voir la liste des supp</li>
          </ol>
        </div>



        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">


          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-3">
                        <label class="form-control-label">Selectionner la date<span class="text-danger ml-2">*</span></label>
                        <input type="date" class="form-control" name="dateTaken" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-3">
                        <h4 class="form-control-label">Somme</h4>
                        <?php if ($totalGeneral > 0) { ?>
                          <h1 class="form-control font-weight-bold" style="height:40px;font-size:20px;color:#00FF00">
                            <?php echo number_format($totalGeneral, 0, ',', ' ') . " Fbu"; ?>
                          </h1>
                        <?php } else { ?>
                          <h4 class="text-danger form-control font-weight-bold" style="height:40px;font-size:20px;">0 Fbu</h4>
                        <?php } ?>

                      </div>
                      <div class="col-xl-2">
                        <h4 class="form-control-label">Effectif</h4>
                        <h1 class="text-success form-control font-weight-bold" style="height:40px;font-size:20px;">
                          <?php echo $students; ?>
                        </h1>

                      </div>
                      <div class="col-xl-2">
                        <h4 class="form-control-label">Présents</h4>
                        <h1 class="form-control font-weight-bold" style="height:40px;font-size:20px;color:#00FF00">
                          <?php echo $present; ?>
                        </h1>

                      </div>
                      <div class="col-xl-2">
                        <h4 class="form-control-label">Absents</h4>
                        <h1 class="text-danger form-control font-weight-bold" style="height:40px;font-size:20px;">
                          <?php echo $absent; ?>
                        </h1>

                      </div>

                    </div>
                    <button type="submit" name="view" class="btn btn-primary">Afficher</button>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="table-responsive p-3 ">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Nom & Prénom</th>
                            <th>Badge</th>
                            <th>Poste</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Heures</th>
                            <th>Date</th>
                            <th>Montant</th>

                          </tr>
                        </thead>

                        <tbody>

                          <?php





                          $query = "SELECT tblsupp.Id,tblsupp.dateTimeTaken,tblemployees.identite,
                      DATE_FORMAT(tblsupp.heureDebut, '%H:%i') AS heureDebut, 
                      DATE_FORMAT(tblsupp.heureFin, '%H:%i') AS heureFin,
                      tblsupp.heures, FLOOR(tblsupp.montant / 100) * 100 AS montant,        
                      tblemployees.firstName,tblemployees.lastName,tblemployees.admissionNumber,tblemployees.poste
                      FROM tblsupp
                      INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
                      INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
                      where tblsupp.dateTimeTaken = '$dateTaken' and tblsupp.classId = '$_SESSION[classId]' 
                      ORDER BY tblemployees.firstName ASC";


                          $rs = $conn->query($query);
                          $num = $rs->num_rows;

                          $sn = 0;
                          if ($num > 0) {
                            while ($rows = $rs->fetch_assoc()) {
                              $sn = $sn + 1;
                              echo "
                              <tr>
                              <td>" . $sn . "</td>
                              <td>" . $rows['firstName'] . " " . $rows['lastName'] . "</td>
                              <td>" . $rows['admissionNumber'] . "</td>
                              <td>" . $rows['poste'] . "</td>
                              <td>" . $rows['heureDebut'] . "</td>
                              <td>" . $rows['heureFin'] . "</td>
                              <td>" . $rows['heures'] . "</td>
                              <td>" . $rows['dateTimeTaken'] . "</td>
                              <td style='font-weight:bold;'>" . number_format($rows['montant'], 0, ',', ' ') . " Fbu</td>
                            
                              </tr>";
                            }
                          } else {
                            echo
                            "<div class='alert alert-danger' role='alert'>
                            Entrez la date valide SVP!
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