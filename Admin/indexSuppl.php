<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Récupérer toutes les usines (classId) qui ont des employés
$queryClasses = mysqli_query($conn, "SELECT DISTINCT classId FROM tblstudents ORDER BY classId ASC");
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
      <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h1 class="h3 mb-0 text-gray-800">Heures Supplémentaires du <?php echo date("d-m-Y");?></h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tableau</li>
          </ol>
        </div>

        <div class="row mb-3">
          <?php
          while ($class = mysqli_fetch_assoc($queryClasses)) {
              $classId = $class['classId'];

              // Nom de l'usine
              $queryClassName = mysqli_query($conn, "SELECT className FROM tblclass WHERE Id = '$classId'");
              $classNameRow = mysqli_fetch_assoc($queryClassName);
              $className = $classNameRow['className'];

              // Montant total prévu
              $resultTotal = mysqli_query($conn,"SELECT SUM(salaire * 7 / 240) AS total_montant
              FROM tblstudents WHERE classId = '$classId' ");
              $rowTotal = mysqli_fetch_assoc($resultTotal);
              $total = $rowTotal['total_montant'];
              $total = floor($total / 100) * 100;

              // Montant payé (ici je suppose que c'est le même que prévu, sinon adapter)
              $resultPayer = mysqli_query($conn, "SELECT SUM(montant) AS total_payer 
                                                  FROM tblsupp 
                                                  WHERE classId = '$classId' AND DATE(dateTimeTaken) = CURDATE()");
              $rowPayer = mysqli_fetch_assoc($resultPayer);
              $payer = $rowPayer['total_payer'];
              $payer = floor($payer / 100) * 100;

              // À retourner
              $retour = $total - $payer;

              // Employés carotés
              $queryCarot = mysqli_query($conn,"SELECT * FROM tblsupp WHERE classId='$classId' AND montant='0' AND DATE(dateTimeTaken)=CURDATE()");
              $carot = mysqli_num_rows($queryCarot);
          ?>

          <!-- Card Montant prévu -->
          <div class="col-xl-3 col-md-6 mb-4" >
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Montant prévu</br><?php echo $className; ?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total,0,',',' '); ?> Fbu</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-money-bill fa-2x" style="color: blue;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card À payer -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">À payer</br><?php echo $className; ?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($payer,0,',',' '); ?> Fbu</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card À retourner -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">À retourner</br><?php echo $className; ?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($retour,0,',',' '); ?> Fbu</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-money-bill-wave fa-2x text-secondary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Employés carotés -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Carrotés </br><?php echo $className; ?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $carot; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fa fa-times fa-2x" style="color: red;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php } // fin while ?>

        </div> <!-- row -->
      </div>
      <?php include 'includes/footer.php';?>
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
