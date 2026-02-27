<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

// Récupérer toutes les classes qui ont des étudiants
$queryClasses = mysqli_query($conn, "SELECT DISTINCT classId FROM tblstudents ORDER BY classId ASC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Tableau de bord</title>
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
        <div class="d-sm-flex align-items-center justify-content-between mb-4" >
          <h1 class="h3 mb-0 text-gray-800">Statistiques de présences du <?php echo date("d-m-Y");?></h1>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tableau</li>
          </ol>
        </div>

        <?php
        // Parcourir chaque classe
        while ($class = mysqli_fetch_assoc($queryClasses)) {
            $classId = $class['classId'];

            // Nom de la classe
            $queryClassName = mysqli_query($conn, "SELECT className FROM tblclass WHERE Id = '$classId'");
            $classNameRow = mysqli_fetch_assoc($queryClassName);
            $className = $classNameRow['className'];

            // Total employés
            $queryStudents = mysqli_query($conn, "SELECT * FROM tblstudents WHERE classId = '$classId'");
            $students = mysqli_num_rows($queryStudents);

            // Présents aujourd'hui
            $queryPresent = mysqli_query($conn, "SELECT * FROM tblattendance WHERE classId = '$classId' 
            AND status = '1' AND DATE(dateTimeTaken) = CURDATE()");
            $totAttendance = mysqli_num_rows($queryPresent);

            // Absents
            $absent = $students - $totAttendance;

            // Abandons
            $queryAbandon = mysqli_query($conn, "SELECT admissionNo, COUNT(*) FROM tblattendance 
            WHERE classId = '$classId' AND status = '0'
            GROUP BY admissionNo HAVING COUNT(*) >= 5 "); 
            $abandon = mysqli_num_rows($queryAbandon);
        ?>

        <!-- Ligne pour cette classe -->
        <div class="row mb-4" >
          <!-- Total employés -->
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Tous les employés - <?php echo $className;?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $students;?></div>
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
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Présents - <?php echo $className;?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totAttendance;?></div>
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
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Absents - <?php echo $className;?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $absent;?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-user-times fa-2x text-secondary"></i>
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
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Abandons - <?php echo $className;?></div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $abandon;?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-cut fa-2x text-danger"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> <!-- fin ligne classe -->

        <?php } // fin while classes ?>

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
