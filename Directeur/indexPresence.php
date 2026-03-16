<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');
$today = date('Y-m-d');

$fromDate = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$toDate   = $_GET['to'] ?? date('Y-m-d');

/* ===================== CLASSES ===================== */
$queryClasses = mysqli_query($conn, "
SELECT DISTINCT c.Id as classId, c.serviceName
FROM tblservice c
INNER JOIN tblemployees s ON s.classId = c.Id
ORDER BY c.Id ASC
");

/* ===================== TOTAL ÉTUDIANTS PAR CLASSE ===================== */
$studentsData = [];
$resultStudents = mysqli_query($conn, "
SELECT classId, COUNT(*) as total
FROM tblemployees
GROUP BY classId
");
while ($row = mysqli_fetch_assoc($resultStudents)) {
    $studentsData[$row['classId']] = $row['total'];
}

/* ===================== PRÉSENCES DU JOUR PAR CLASSE ===================== */
$presenceData = [];
$resultPresence = mysqli_query($conn, "
SELECT classId, COUNT(*) as total
FROM tblattendance
WHERE status='1' AND DATE(dateTimeTaken)=CURDATE()
GROUP BY classId
");
while ($row = mysqli_fetch_assoc($resultPresence)) {
    $presenceData[$row['classId']] = $row['total'];
}

/* ===================== ABANDONS PAR CLASSE ===================== */
$abandonData = [];
$resultAbandon = mysqli_query($conn, "
SELECT classId, admissionNo
FROM tblattendance
WHERE status='0'
GROUP BY classId, admissionNo
HAVING COUNT(*) >= 5
");

while ($row = mysqli_fetch_assoc($resultAbandon)) {
    $classId = $row['classId'];
    if (!isset($abandonData[$classId])) {
        $abandonData[$classId] = 0;
    }
    $abandonData[$classId]++;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="img/logo/life.jpg" rel="icon">
    <title>Tableau de bord</title>
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

                <div class="d-sm-flex align-items-center justify-content-between mb-0">
                    <h6 class="font-weight-bold text-primary" style="margin-left:30px">
                        Statistiques de présences du <?php echo date("d-m-Y"); ?>
                    </h6>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="downloadPresences.php?fromDate=<?php echo $fromDate; ?>
              &toDate=<?php echo $toDate; ?>"> Exporter</a>(Exel)
                        </li>
                        <li class="breadcrumb-item">
                            <a href="printPresences.php?fromDate=<?php echo $fromDate; ?>
              &toDate=<?php echo $toDate; ?>">Imprimer</a>(PDF)
                        </li>

                    </ol>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="./">Accueil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tableau</li>
                    </ol>
                </div>

                <div class="container-fluid" id="container-wrapper">

                    <?php while ($class = mysqli_fetch_assoc($queryClasses)) {

                        $classId   = $class['classId'];
                        $serviceName = $class['serviceName'];

                        $students      = $studentsData[$classId]  ?? 0;
                        $totAttendance = $presenceData[$classId] ?? 0;
                        $abandon       = $abandonData[$classId]  ?? 0;
                        $absent        = $students - $totAttendance;
                    ?>

                        <div class="row mb-4">

                            <!-- Tous -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                    Tous les employés - <?php echo $serviceName; ?>
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $students; ?>
                                                </div>
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
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                    Présents - <?php echo $serviceName; ?>
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $totAttendance; ?>
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
                                                    Absents - <?php echo $serviceName; ?>
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $absent; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="text-danger fas fa-user-times fa-2x text-secondary"></i>
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
                                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                                    Abandons - <?php echo $serviceName; ?>
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $abandon; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-cut fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    <?php } ?>

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