<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// Date d'aujourd'hui au format YYYY-MM-DD
$today = date('Y-m-d');
$dateTaken = date("Y-m-d"); // pour les requêtes SQL

// Date à afficher par défaut
$dateToShow = $today;
$filterMessage = "Liste d'appel du jour (aujourd'hui) – " . date('d/m/Y');

// Récupérer le nombre total d'étudiants/employés
$query1 = mysqli_query($conn, "SELECT * FROM tblemployees WHERE classId = '$_SESSION[classId]'");
$students = $query1 ? mysqli_num_rows($query1) : 0;

// Présents aujourd'hui
$query2 = mysqli_query($conn, "SELECT * FROM tblattendance 
                              WHERE status = '1' 
                              AND classId = '$_SESSION[classId]' 
                              AND DATE(dateTimeTaken) = '$dateTaken'");
$totAttendance = $query2 ? mysqli_num_rows($query2) : 0;

// Absents
$absent = $students - $totAttendance;

// Si l'utilisateur a soumis le formulaire avec une date
if (isset($_POST['view']) && !empty($_POST['dateTaken'])) {
  $dateToShow = $_POST['dateTaken'];
}

$queryClass = "SELECT tblservice.serviceName
               FROM tblchef
               INNER JOIN tblservice ON tblservice.Id = tblchef.classId
               WHERE tblchef.Id = '$_SESSION[userId]'";
$rsClass = $conn->query($queryClass);
$rrw = $rsClass->fetch_assoc();
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
  <title>Voir l'appel</title>
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
          <h6 class=" font-weight-bold text-primary" style="margin-left:30px">
            Liste d'appel <b>Le <?php echo date('d-m-Y', strtotime($dateToShow)) . ' ' . $rrw['serviceName']; ?></b>
          </h6>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="downloadRecord.php">Exporter</a> (Excel)</li>
            <li class="breadcrumb-item"><a href="printRecord.php">Imprimer</a> (PDF)</li>
          </ol>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Voir la liste d'appel</li>
          </ol>
        </div>

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">

          <div class="row">
            <div class="col-lg-12">

              <!-- Formulaire -->
              <div class="card mb-4">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">
                          Sélectionner la date <span class="text-danger ml-2">*</span>
                        </label>
                        <input type="date" class="form-control" name="dateTaken"
                          value="<?php echo htmlspecialchars($today); ?>"
                          max="<?php echo htmlspecialchars($today); ?>">
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
                          <?php echo $totAttendance; ?>
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


              <!-- Tableau -->
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="table-responsive p-3">
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Nom & Prénom</th>
                            <th>Badge</th>
                            <th>Poste</th>
                            <th>Date</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $query = "SELECT tblattendance.Id, tblattendance.status, tblattendance.dateTimeTaken,
                                           tblservice.serviceName, tblemployees.firstName, tblemployees.lastName,
                                           tblemployees.admissionNumber, tblemployees.poste
                                    FROM tblattendance
                                    INNER JOIN tblservice ON tblservice.Id = tblattendance.classId
                                    INNER JOIN tblemployees ON tblemployees.admissionNumber = tblattendance.admissionNo
                                    WHERE tblattendance.dateTimeTaken LIKE '$dateToShow%'
                                      AND tblattendance.classId = '$_SESSION[classId]'
                                    ORDER BY tblemployees.firstName ASC";

                          $rs = $conn->query($query);
                          $num = $rs->num_rows;
                          $sn = 0;

                          if ($num > 0) {
                            while ($rows = $rs->fetch_assoc()) {
                              $status = ($rows['status'] == '1') ? "Présent" : "Absent";
                              $colour = ($rows['status'] == '1') ? "#00FF00" : "#FF0000";
                              $sn++;

                              echo "
                              <tr>
                                <td>$sn</td>
                                <td>{$rows['firstName']} {$rows['lastName']}</td>
                                <td>{$rows['admissionNumber']}</td>
                                <td>{$rows['poste']}</td>
                                <td>{$rows['dateTimeTaken']}</td>
                                <td style='color:$colour;'><strong>$status</strong></td>
                              </tr>";
                            }
                          } else {
                            echo "
                            <tr>
                              <td colspan='6' class='text-center'>
                                <div class='alert alert-danger'>
                                  Aucune présence enregistrée pour cette date.
                                </div>
                              </td>
                            </tr>";
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
        <!-- Container Fluid -->

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