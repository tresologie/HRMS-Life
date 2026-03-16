<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');
//------------------------SAVE--------------------------------------------------

if (isset($_POST['save'])) {

  $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
  $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
  $identite = mysqli_real_escape_string($conn, $_POST['identite']);
  $ddn = mysqli_real_escape_string($conn, $_POST['ddn']);
  $tel = mysqli_real_escape_string($conn, $_POST['tel']);
  $genre = mysqli_real_escape_string($conn, $_POST['genre']);
  $admissionNumber = mysqli_real_escape_string($conn, $_POST['admissionNumber']);
  $poste = mysqli_real_escape_string($conn, $_POST['poste']);
  $salaire = mysqli_real_escape_string($conn, $_POST['salaire']);
  $classId = mysqli_real_escape_string($conn, $_POST['classId']);
  $dateCreated = date("Y-m-d");

  // Vérifier si badge OU identite existe déjà
  $check = mysqli_query($conn, "SELECT Id FROM tblemployees 
                                  WHERE admissionNumber='$admissionNumber' 
                                  OR identite='$identite'");

  if (mysqli_num_rows($check) > 0) {

    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
                        L'employé existe déjà !
                      </div>";
  } else {

    $insert = mysqli_query($conn, "INSERT INTO tblemployees
      (firstName,lastName,identite,poste,admissionNumber,tel,salaire,ddn,genre,classId,dateCreated)
      VALUES
      ('$firstName','$lastName','$identite','$poste','$admissionNumber','$tel','$salaire','$ddn','$genre','$classId','$dateCreated')");
    if ($insert) {

      $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>
                            Employé ajouté avec succès !
                          </div>";
    } else {

      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
                            Erreur lors de l'insertion !
                          </div>";
    }
  }
}

//--------------------EDIT------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {
  $Id = $_GET['Id'];

  $query = mysqli_query($conn, "select * from tblemployees where Id ='$Id'");
  $row = mysqli_fetch_array($query);

  //------------UPDATE-----------------------------

  if (isset($_POST['update'])) {

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $identite = $_POST['identite'];
    $admissionNumber = $_POST['admissionNumber'];
    $ddn = $_POST['ddn'];
    $tel = $_POST['tel'];
    $genre = $_POST['genre'];
    $poste = $_POST['poste'];
    $salaire = $_POST['salaire'];
    $classId = $_POST['classId'];

    $dateCreated = date("Y-m-d");

    $query = mysqli_query($conn, "UPDATE tblemployees SET firstName='$firstName', lastName='$lastName', 
             identite='$identite',poste='$poste',admissionNumber='$admissionNumber',tel='$tel', 
             salaire='$salaire', ddn='$ddn',genre='$genre',classId='$classId'
             WHERE Id='$Id'");
    if ($query) {
      echo "<script type = \"text/javascript\">
                window.location = (\"createEmployees.php\")
                </script>";
    } else {
      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Erreur!</div>";
    }
  }
}


//--------------------------------DELETE------------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
  $Id = $_GET['Id'];
  $classArmId = $_GET['classId'];

  $query = mysqli_query($conn, "DELETE FROM tblemployees WHERE Id='$Id'");

  if ($query == TRUE) {

    echo "<script type = \"text/javascript\">
            window.location = (\"createEmployees.php\")
            </script>";
  } else {

    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Erreur!</div>";
  }
}


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
  <?php include 'includes/title.php'; ?>
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
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">Ajouter un employé</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Ajouter un employé</li>
            </ol>
        </div>




        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">


          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->

              <div class="card mb-4" style='padding:20px;'>
                <?php
                if (!isset($row)) {
                  $row = [
                    'firstName' => '',
                    'lastName' => '',
                    'identite' => '',
                    'poste' => '',
                    'admissionNumber' => '',
                    'tel' => '',
                    'salaire' => '',
                    'ddn' => '',
                    'genre' => '',
                    'classId' => ''
                  ];
                }
                ?>
                <form method="post">
                  <div class="form-group row mb-3">
                    <div class="col-xl-4">
                      <label class="form-control-label">Nom<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="firstName" value="<?php echo $row['firstName']; ?>" id="exampleInputFirstName">
                    </div>
                    <div class="col-xl-4">
                      <label class="form-control-label">Prénom<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="lastName" value="<?php echo $row['lastName']; ?>" id="exampleInputFirstName">
                    </div>
                    <div class="col-xl-4">
                      <label class="form-control-label">No. d'identité<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="identite" value="<?php echo $row['identite']; ?>" id="exampleInputFirstName">
                    </div>
                  </div>
                  <div class="form-group row mb-3">
                    <div class="col-xl-4">

                      <label class="form-control-label">Poste<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" required name="poste" value="<?php echo $row['poste']; ?>" id="exampleInputFirstName">
                    </div>
                    <div class="col-xl-4">
                      <label class="form-control-label">Numéro de la badge<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" required name="admissionNumber" value="<?php echo $row['admissionNumber']; ?>" id="exampleInputFirstName">
                    </div>
                    <div class="col-xl-4">
                      <label class="form-control-label">Telephone<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="tel" value="<?php echo $row['tel']; ?>" id="exampleInputFirstName">
                    </div>
                  </div>
                  <div class="form-group row mb-3">
                    <div class="col-xl-4">
                      <label class="form-control-label">Salaire<span class="text-danger ml-2">*</span></label>
                      <input type="number" class="form-control" required name="salaire" value="<?php echo $row['salaire']; ?>" id="exampleInputFirstName">
                    </div>
                    <div class="col-xl-4">
                      <label class="form-control-label">Date de naissance<span class="text-danger ml-2">*</span></label>
                      <input type="date" class="form-control" name="ddn" value="<?php echo $row['ddn']; ?>" id="exampleInputFirstName">
                    </div>

                    <div class="col-xl-4">
                      <label class="form-control-label d-block"> Genre <span class="text-danger ml-2">*</span></label>
                      <div class="d-flex justify-content-around align-items-center">
                        <div class="form-check">
                          <input class="form-control" type="radio" name="genre" value="M"
                            <?php if ($row['genre'] == 'M') echo 'checked'; ?>>
                          <label class="form-check-label">Masculin</label>
                        </div>
                        <div class="form-check">
                          <input class="form-control" type="radio" name="genre" value="F"
                            <?php if ($row['genre'] == 'F') echo 'checked'; ?>>
                          <label class="form-check-label">Féminin</label>
                        </div>

                      </div>
                    </div>
                  </div>
                  <div class="form-group row mb-3">
                    <div class="col-xl-4">
                      <label class="form-control-label">Usine<span class="text-danger ml-2">*</span></label>
                      <?php
                      $qry = "SELECT * FROM tblservice ORDER BY serviceName ASC";
                      $result = $conn->query($qry);
                      $num = $result->num_rows;
                      if ($num > 0) {
                        echo ' <select required name="classId" onchange="classArmDropdown(this.value)" class="form-control mb-3">';
                        echo '<option value="">--Usine--</option>';
                        while ($rows = $result->fetch_assoc()) {
                          $selected = (isset($row['classId']) && $row['classId'] == $rows['Id']) ? "selected" : "";
                          echo '<option value="' . $rows['Id'] . '" ' . $selected . '>' . $rows['serviceName'] . '</option>';
                        }
                        echo '</select>';
                      }
                      ?>
                    </div>

                  </div>
                  <?php
                  if (isset($Id)) {
                  ?>
                    <button type="submit" name="update" class="btn btn-warning">Modifier</button>
                    <a href="createEmployees.php" class="btn btn-secondary">Annuler</a>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <?php
                  } else {
                  ?>
                    <button type="submit" name="save" class="btn btn-primary">Enregistrer</button>
                  <?php
                  }
                  ?>
                </form>
              </div>

              <!-- Input Group -->

              <div class="card mb-4">
                <div class="table-responsive p-3">
                  <table class="table table-hover table-sm" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Nom & Prenom</th>
                        <th>Poste</th>
                        <th>Badge</th>
                        <th>No. de tel</th>
                        <th>Salaire</th>
                        <th>Usine</th>
                        <th>Date</th>
                        <th>Editer</th>
                        <th>Suppr</th>
                      </tr>
                    </thead>

                    <tbody>

                      <?php
                      $query = "SELECT tblemployees.Id,tblservice.serviceName,tblemployees.firstName,tblemployees.lastName,
                      tblemployees.identite,tblemployees.admissionNumber,tblemployees.poste,tblemployees.tel,
                      tblemployees.salaire,tblemployees.dateCreated
                      FROM tblemployees
                      INNER JOIN tblservice ON tblservice.Id = tblemployees.classId 
                      ORDER BY tblservice.serviceName, tblemployees.firstName ASC";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn = 0;
                      $status = "";
                      if ($num > 0) {
                        while ($rows = $rs->fetch_assoc()) {
                          $sn = $sn + 1;
                          echo "
                              <tr>
                                <td>" . $sn . "</td>
                                <td><b>" . $rows['firstName'] . " " . $rows['lastName'] . "</b> </br> " . $rows['identite'] . "</td>
                                <td>" . $rows['poste'] . "</td>
                                <td>" . $rows['admissionNumber'] . "</td>
                                <td>" . $rows['tel'] . "</td>
                                <td>" . $rows['salaire'] . " Fbu</td>
                                <td>" . $rows['serviceName'] . "</td>
                                 <td>" . $rows['dateCreated'] . "</td>
                                <td><a href='?action=edit&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-edit'></i></a></td>
                                <td><a href='?action=delete&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-trash'></i></a></td>
                              </tr>";
                        }
                      } else {
                        echo
                        "<div class='alert alert-danger' role='alert'>
                            Non trouvés!
                            </div>";
                      }

                      ?>
                    </tbody>
                  </table>
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