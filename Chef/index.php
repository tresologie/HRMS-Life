<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';


date_default_timezone_set('Africa/Bujumbura');


// Récupérer le nom de la classe de l'enseignant
$query = "SELECT tblservice.serviceName
          FROM tblchef
          INNER JOIN tblservice ON tblservice.Id = tblchef.classId
          WHERE tblchef.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

// Date du jour
$todaysDate = date("d-m-Y");
$dateTaken = date("Y-m-d"); // pour les requêtes SQL

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

// Abandons (plus de 5 absences)
$query3 = mysqli_query($conn, "SELECT admissionNo FROM tblattendance 
                              WHERE classId = '$_SESSION[classId]' AND status = '0'
                              GROUP BY admissionNo 
                              HAVING COUNT(*) >= 5");
$abandon = $query3 ? mysqli_num_rows($query3) : 0;


// Montant payé aujourd'hui
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

$query4 = mysqli_query($conn, "SELECT * FROM tblsupp 
                               WHERE montant != 0 
                               AND classId = '$_SESSION[classId]' 
                               AND DATE(dateTimeTaken) = '$dateTaken'");
$suppl = $query4 ? mysqli_num_rows($query4) : 0;
$absentSuppl = $students - $suppl;

// Carotés (montant = 0 aujourd'hui)
$query4 = mysqli_query($conn, "SELECT * FROM tblsupp 
                               WHERE montant = 0 
                               AND classId = '$_SESSION[classId]' 
                               AND DATE(dateTimeTaken) = '$dateTaken'");
$carro = $query4 ? mysqli_num_rows($query4) : 0;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Tableau de bord</title>
  <link href="img/logo/life.jpg" rel="icon">
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">
    <?php include "Includes/sidebar.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include "Includes/topbar.php"; ?>

        <div class="d-sm-flex align-items-center justify-content-between mt-3">
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">
            Statistiques du <?php echo $todaysDate; ?> <b><?php echo $rrw['serviceName']; ?></b>
          </h6>
        </div>

        <div class="container-fluid" id="container-wrapper">

          <hr class="sidebar-divider">
          <div class="row mb-3">

            <!-- Tous les Employés -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Tous les Employés</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $students; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x" style="color: blue;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Présents -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Présents</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totAttendance; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Absents -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Absents</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $absent; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class=" text-danger fas fa-user-times fa-2x text-secondary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Abandons -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Abandons</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $abandon; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fa fa-cut fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="row mb-3">

            <!-- A payer aujourd'hui -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">A payer</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalGeneral, 0, ',', ' ') ?> Fbu</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-money-bill fa-2x" style="color: blue;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- A payer aujourd'hui -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Présents</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $suppl; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- A retourner -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Absents</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $absentSuppl; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class=" text-danger fas fa-user-times fa-2x text-secondary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Carotés -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Carotés</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $carro; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fa fa-times fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <?php include "Includes/footer.php"; ?>
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
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>
</body>

</html>