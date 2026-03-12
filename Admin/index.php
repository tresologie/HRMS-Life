<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

$todaysDate = date("d-m-Y");
$dateSQL    = date("Y-m-d");

$statsQuery = mysqli_query($conn,"
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN genre='M' THEN 1 ELSE 0 END) as hommes,
    SUM(CASE WHEN genre='F' THEN 1 ELSE 0 END) as femmes
FROM tblemployees
");

$stats = mysqli_fetch_assoc($statsQuery);

$students = $stats['total'] ?? 0;
$hommes   = $stats['hommes'] ?? 0;
$femmes   = $stats['femmes'] ?? 0;


$abandonQuery = mysqli_query($conn,"
SELECT admissionNo
FROM tblattendance
WHERE status='0'
GROUP BY admissionNo
HAVING COUNT(*) >= 5
");

$abandon = mysqli_num_rows($abandonQuery);

$presenceQuery = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM tblattendance 
WHERE status='1' 
AND DATE(dateTimeTaken)='$dateSQL'
");

$rowPresence = mysqli_fetch_assoc($presenceQuery);
$totAttendance = $rowPresence['total'] ?? 0;

$absent = $students - $totAttendance;


$payerQuery = mysqli_query($conn,"
SELECT SUM(FLOOR(montant/100)*100) as total
FROM tblsupp
WHERE DATE(dateTimeTaken)='$dateSQL'
");

$rowPayer = mysqli_fetch_assoc($payerQuery);
$payer = $rowPayer['total'] ?? 0;


$query1=mysqli_query($conn,"SELECT * from tblsupp where montant='0'  and DATE(dateTimeTaken) = CURDATE()");                       
            $carot = mysqli_num_rows($query1);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Tableau de bord</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">

<?php include "Includes/sidebar.php";?>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<?php include "Includes/topbar.php";?>

<div class="container-fluid" id="container-wrapper">
<h6 class=" font-weight-bold text-primary">
 <b>Directeur Général</b> - Le <?php echo $todaysDate; ?> 
    </h6>
<hr class="sidebar-divider">

<div class="row mb-3">

<!-- Tous les employés -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card h-100">
<div class="card-body">
<div class="row no-gutters align-items-center">
<div class="col mr-2">
<div class="text-xs font-weight-bold text-uppercase mb-1">Tous les Employés</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $students; ?></div>
</div>
<div class="col-auto">
<i class="fas fa-users fa-3x" style="color: blue;"></i>
</div>
</div>
</div>
</div>
</div>

<!-- Hommes -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card h-100">
<div class="card-body">
<div class="row no-gutters align-items-center">
<div class="col mr-2">
<div class="text-xs font-weight-bold text-uppercase mb-1">Les hommes</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $hommes; ?></div>
</div>
<div class="col-auto">
<i class="fas fa-male fa-4x text-success"></i>
</div>
</div>
</div>
</div>
</div>

<!-- Femmes -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card h-100">
<div class="card-body">
<div class="row no-gutters align-items-center">
<div class="col mr-2">
<div class="text-xs font-weight-bold text-uppercase mb-1">Les femmes</div>
<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $femmes; ?></div>
</div>
<div class="col-auto">
<i class=" text-danger fas fa-female fa-4x"></i>
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

<!-- Montant à payer -->
<div class="col-xl-3 col-md-6 mb-4">
<div class="card h-100">
<div class="card-body">
<div class="row no-gutters align-items-center">
<div class="col mr-2">
<div class="text-xs font-weight-bold text-uppercase mb-1">A payer</div>
<div class="h5 mb-0 font-weight-bold text-gray-800">
<?php echo number_format($payer,0,',',' '); ?> Fbu
</div>
</div>
<div class="col-auto">
<i class="fas fa-money-bill fa-3x" style="color: blue;"></i>
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
<i class="fas fa-calendar-check fa-3x text-success"></i>
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
<i class="fas fa-user-times fa-3x text-danger"></i>
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
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Carrotés</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $carot; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fa fa-times fa-3x"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

</div>
</div>
<?php include "Includes/footer.php";?>
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