<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');
$dateToday = date("Y-m-d");
$timeNow = date("H:i:s"); // heure actuelle

// ------------------- BLOQUER LA PAGE SI +30 MIN -------------------
$q = mysqli_query($conn,"SELECT MIN(timeTaken) AS startTime 
                         FROM tblattendance 
                         WHERE classId='$_SESSION[classId]' 
                           AND dateTimeTaken='$dateToday'");
$row = mysqli_fetch_assoc($q);

if($row['startTime']){
    $appelStartTime = $row['startTime'];
    $appelEndTime = date("H:i:s", strtotime($appelStartTime . ' +30 minutes'));

    if(strtotime($timeNow) > strtotime($appelEndTime)){
        echo "<div class='alert alert-danger text-center' style='margin-top:50px; font-size:20px;'>
                L'appel est déjà terminé !</div>";
        exit(); // bloque le reste de la page
    }
}
// -------------------------------------------------------------------

// Récupérer le nom de l'usine du professeur
$query = "SELECT tblclass.className
          FROM tblclassteacher
          INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
          WHERE tblclassteacher.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();

// -------------------- UPDATE PRESENCES --------------------
if(isset($_POST['save'])){
    $admissionNo = $_POST['admissionNo'];
    $check = isset($_POST['check']) ? $_POST['check'] : [];
    $N = count($admissionNo);

    for($i = 0; $i < $N; $i++){
        $statusVal = in_array($admissionNo[$i], $check) ? 1 : 0;

        mysqli_query($conn,"UPDATE tblattendance 
                            SET status='$statusVal' 
                            WHERE admissionNo='".$admissionNo[$i]."' 
                            AND classId='".$_SESSION['classId']."' 
                            AND dateTimeTaken='$dateToday'");
    }

    $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>Présences mises à jour avec succès !</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Refaire l'appel - Life Company</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

  <style>
    /* Scroll uniquement sur la table */
    .table-responsive {
      max-height: 500px;
      overflow-y: auto;
    }
  </style>
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php";?>
    
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php";?>
        
        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Refaire l'appel (Aujourd'hui : <?php echo date("d-m-Y"); ?>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Tous les employés d'usine</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <form method="post">
                <div class="card mb-4">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tous les employés de (<?php echo $rrw['className']; ?>)</h6>
                    <h6 class="m-0 font-weight-bold text-danger"><i>Note : Cochez la case pour marquer la présence ou décochez pour l'absence</i></h6>
                  </div>
                  <div class="table-responsive p-3">
                    <?php echo isset($statusMsg) ? $statusMsg : ""; ?>
                    <table class="table align-items-center table-flush table-hover">
                      <thead class="thead-light">
                        <tr>
                          <th>#</th>
                          <th>Nom</th>
                          <th>Prénom</th>
                          <th>Badge</th>
                          <th>Poste</th>
                          <th>Usine</th>
                          <th>Cocher</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $query = "SELECT tblstudents.Id, tblstudents.admissionNumber, tblclass.className, tblstudents.firstName,
                                  tblstudents.lastName, tblstudents.poste
                                  FROM tblstudents
                                  INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                                  WHERE tblstudents.classId='$_SESSION[classId]'";
                        $rs = $conn->query($query);
                        $sn = 0;

                        while($rows = $rs->fetch_assoc()){
                            $sn++;

                            // Vérifier le status actuel
                            $attQuery = mysqli_query($conn,"SELECT status FROM tblattendance 
                                                            WHERE admissionNo='".$rows['admissionNumber']."' 
                                                            AND classId='".$_SESSION['classId']."' 
                                                            AND dateTimeTaken='$dateToday'");
                            $attRow = mysqli_fetch_assoc($attQuery);
                            $checked = (isset($attRow['status']) && $attRow['status']==1) ? "checked" : "";

                            echo "<tr>
                                    <td>".$sn."</td>
                                    <td>".$rows['firstName']."</td>
                                    <td>".$rows['lastName']."</td>
                                    <td>".$rows['admissionNumber']."</td>
                                    <td>".$rows['poste']."</td>
                                    <td>".$rows['className']."</td>
                                    <td><input name='check[]' type='checkbox' value='".$rows['admissionNumber']."' class='form-control' $checked></td>
                                  </tr>";
                            echo "<input name='admissionNo[]' value='".$rows['admissionNumber']."' type='hidden' class='form-control'>";
                        }
                        ?>
                      </tbody>
                    </table>
                    <br>
                    <button type="submit" name="save" class="btn btn-primary">Refaire</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <?php include "Includes/footer.php";?>
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
</body>
</html>
