<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');
$dateSQL    = date("Y-m-d");


$fromDate = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$toDate   = $_GET['to'] ?? date('Y-m-d');

// Récupérer toutes les usines (classes) qui ont au moins un employé avec leur nom
$queryClasses = mysqli_query($conn, "
    SELECT DISTINCT s.classId, c.serviceName
    FROM tblemployees s
    INNER JOIN tblservice c ON s.classId = c.Id
    ORDER BY s.classId ASC
");


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Heures Supplémentaires par Usine</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">
  <?php include "Includes/sidebar.php";?>
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <?php include "Includes/topbar.php";?>

      <div class="d-sm-flex align-items-center justify-content-between mb-0">
      <h6 class="font-weight-bold text-primary" style="margin-left:30px">Heures Supplémentaires du <?php echo date("d-m-Y");?></h6>
          <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="downloadSuppl.php?from=<?php echo $fromDate;?>
              &to=<?php echo $toDate;?>" >Exporter</a>(Exel)</li>
              <li class="breadcrumb-item"><a href="printSuppl.php?from=<?php echo $fromDate;?>
              &to=<?php echo $toDate;?>" >Imprimer</a>(PDF)</li>
              
            </ol>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tableau</li>
          </ol>
      </div>

      <div class="container-fluid" id="container-wrapper">
        <div class="row mb-3">
        
          <?php 
          // Date du jour
          $todaysDate = date("d-m-Y");
          $dateTaken = date("Y-m-d"); // pour les requêtes SQL
          
          while ($class = mysqli_fetch_assoc($queryClasses)) {
              $classId = $class['classId'];
              $serviceName = $class['serviceName'];

             /* ===================== TOTAL ÉTUDIANTS PAR CLASSE ===================== */
$studentsData = [];
$resultStudents = mysqli_query($conn,"
SELECT classId, COUNT(*) as total
FROM tblemployees
GROUP BY classId
");
while($row = mysqli_fetch_assoc($resultStudents)){
    $studentsData[$row['classId']] = $row['total'];
}

              // Montant payé
              $resultPayer = mysqli_query($conn,
               "SELECT SUM(FLOOR(tblsupp.montant / 100) * 100) as total
                 FROM tblsupp
                 WHERE classId = '$classId' AND tblsupp.dateTimeTaken = '$dateTaken' ");
              $rowPayer = mysqli_fetch_assoc($resultPayer);
              $payer = floor(($rowPayer['total'] ?? 0) / 100) * 100;

              // Present
              $queryCarot = mysqli_query($conn,"SELECT * FROM tblsupp WHERE classId='$classId' AND montant!='0' AND DATE(dateTimeTaken)=CURDATE()");
              $present = mysqli_num_rows($queryCarot);

              // Employés carotés
              $queryCarot = mysqli_query($conn,"SELECT * FROM tblsupp WHERE classId='$classId' AND montant='0' AND DATE(dateTimeTaken)=CURDATE()");
              $carot = mysqli_num_rows($queryCarot);

              $absent= $present-$carot;

              
          ?>

          <!-- Montant prévu -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">À payer-<?php echo $serviceName; ?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($payer,0,',',' '); ?> Fbu</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-money-bill fa-2x" style="color: blue;"></i>
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
<div class="text-xs font-weight-bold text-uppercase mb-1">
Présents - <?php echo $serviceName;?>
</div>
<div class="h5 mb-0 font-weight-bold text-gray-800">
<?php echo $present;?>
</div>
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
<div class="text-xs font-weight-bold text-uppercase mb-1">
Absents - <?php echo $serviceName;?>
</div>
<div class="h5 mb-0 font-weight-bold text-gray-800">
<?php echo $absent;?>
</div>
</div>
<div class="col-auto">
<i class="fas fa-user-times fa-2x text-danger"></i>
</div>
</div>
</div>
</div>
</div>

          <!-- Employés carotés -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Carrotés - <?php echo $serviceName; ?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $carot; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fa fa-times fa-2x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php } // fin while ?>
        </div>
      </div>
    </div>
  </div>

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>
</body>
</html>