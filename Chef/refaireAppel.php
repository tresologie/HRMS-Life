<?php
error_reporting();
include '../Includes/dbcon.php';
include '../Includes/session.php';

$query = "SELECT tblservice.serviceName
FROM tblchef
INNER JOIN tblservice ON tblservice.Id = tblchef.classId

Where tblchef.Id = '$_SESSION[userId]'";
$rs = $conn->query($query);
$num = $rs->num_rows;
$rrw = $rs->fetch_assoc();

date_default_timezone_set('Africa/Bujumbura');
$dateToday = date("Y-m-d");
$timeNow = date("H:i:s");

// ------------------- UPDATE PRESENCES --------------------
$statusMsg = "";
if (isset($_POST['save'])) {
  $admissionNo = $_POST['admissionNo'];
  $check = isset($_POST['check']) ? $_POST['check'] : [];
  $N = count($admissionNo);

  for ($i = 0; $i < $N; $i++) {
    $statusVal = in_array($admissionNo[$i], $check) ? 1 : 0;

    mysqli_query($conn, "UPDATE tblattendance 
                          SET status='$statusVal' 
                          WHERE admissionNo='" . $admissionNo[$i] . "' 
                          AND classId='" . $_SESSION['classId'] . "' 
                          AND dateTimeTaken='$dateToday'");
  }

  // Redirection vers index
  header("Location: index.php?msg=success");
  exit();
}

// ------------------- BLOQUER L'APPEL SI +45 MIN -------------------
$appelBloque = false;
$messageBlocage = "";

$q = mysqli_query($conn, "SELECT MIN(timeTaken) AS startTime 
                         FROM tblattendance 
                         WHERE classId='$_SESSION[classId]' 
                         AND dateTimeTaken='$dateToday'");
$row = mysqli_fetch_assoc($q);

if ($row['startTime']) {
  $appelStartTime = $row['startTime'];
  $appelEndTime = date("H:i:s", strtotime($appelStartTime . ' +45 minutes'));

  if (strtotime($timeNow) > strtotime($appelEndTime)) {
    $appelBloque = true;
    $messageBlocage = "
        <div class='alert alert-danger text-center' style='margin:20px; font-size:18px;'>
            L'appel est déjà terminé !
        </div>";
  }
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
</head>

<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
    <?php include "Includes/sidebar.php"; ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
        <?php include "Includes/topbar.php"; ?>



        <div class="d-sm-flex align-items-center justify-content-between">
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">Refaire l'appel <b> Le <?php echo date('d-m-Y', strtotime($dateToday)) . ' ' . $rrw['serviceName']; ?></b></h6>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tous les employés d'usine</li>
          </ol>
        </div>


        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">


          <div class="row">
            <div class="col-lg-12">
              <form method="post">
                <div class="card mb-4">
                  <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tous les employés</h6>
                    <h6 class="m-0 font-weight-bold text-danger"><i>Note : Cochez la case pour marquer la présence ou décochez pour l'absence</i></h6>
                  </div>
                  <div class="table-responsive p-3">
                    <?php echo $statusMsg; ?>
                    <?php
                    if ($appelBloque) {
                      echo $messageBlocage;
                    } else {
                    ?>
                      <table class="table align-items-center table-flush table-hover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Badge</th>
                            <th>Poste</th>
                            <th>Cocher</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          $classId = $_SESSION['classId'];

                          $query = "SELECT s.Id, s.admissionNumber, c.serviceName, 
                                         s.firstName, s.lastName, s.poste,
                                         a.status
                                  FROM tblemployees s
                                  INNER JOIN tblservice c ON c.Id = s.classId
                                  LEFT JOIN tblattendance a 
                                       ON a.admissionNo = s.admissionNumber
                                       AND a.classId = s.classId
                                       AND a.dateTimeTaken = '$dateToday'
                                  WHERE s.classId='$classId'
                                  ORDER BY s.firstName ASC";

                          $rs = $conn->query($query);
                          $sn = 0;

                          while ($rows = $rs->fetch_assoc()) {
                            $sn++;

                            // Si déjà présent alors coché
                            $checked = ($rows['status'] == 1) ? "checked" : "";

                            echo "<tr>
                                  <td>" . $sn . "</td>
                                  <td>" . $rows['firstName'] . "</td>
                                  <td>" . $rows['lastName'] . "</td>
                                  <td>" . $rows['admissionNumber'] . "</td>
                                  <td>" . $rows['poste'] . "</td>
                      
                                  <td>
                                      <input name='check[]' 
                                             type='checkbox' 
                                             value='" . $rows['admissionNumber'] . "' 
                                             class='form-control'
                                             $checked>
                                  </td>
                                </tr>";

                            echo "<input name='admissionNo[]' 
                                value='" . $rows['admissionNumber'] . "' 
                                type='hidden'>";
                          }
                          ?>

                        </tbody>
                      </table>

                      <br>
                      <button type="submit" name="save" class="btn btn-primary">Refaire</button>
                      <a href="index.php" class="btn btn-secondary">Annuler</a>
                    <?php } ?>
                  </div>

                </div>
              </form>
            </div>
          </div>
        </div>
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