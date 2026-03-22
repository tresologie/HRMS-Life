<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

//------------------------SAVE--------------------------------------------------

if (isset($_POST['save'])) {

  $firstName = trim($_POST['firstName']);
  $lastName  = trim($_POST['lastName']);
  $emailAddress = trim($_POST['emailAddress']);
  $phoneNo = trim($_POST['phoneNo']);
  $password = trim($_POST['password']);
  $cpassword = trim($_POST['cpassword']);
  $classId = $_POST['classId'];
  $dateCreated = date("Y-m-d");

  // Vérifier si les mots de passe correspondent
  if ($password !== $cpassword) {
    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
          Les mots de passe ne correspondent pas !
      </div>";
  } else {
    // Vérifier si l'email existe déjà
    $query = mysqli_query($conn, "SELECT * FROM tblchef WHERE emailAddress ='$emailAddress'");
    $ret = mysqli_fetch_array($query);

    if ($ret > 0) {
      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
              Son Email existe déjà !
          </div>";
    } else {
      // Hachage MD5 du mot de passe
      $md5Password = md5($password);

      // Insertion dans la BDD
      $query = mysqli_query($conn, "INSERT INTO tblchef 
              (firstName, lastName, emailAddress, password, phoneNo, classId, dateCreated) 
              VALUES ('$firstName', '$lastName', '$emailAddress', '$md5Password', '$phoneNo', '$classId', '$dateCreated')");

      if ($query) {
        $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>
                  Le chef d'usine ajouté avec succès !
              </div>";
      } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
                  Erreur lors de l'ajout !
              </div>";
      }
    }
  }
}

//--------------------EDIT------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit") {

  $Id = $_GET['Id'];

  // Récupérer les données existantes pour pré-remplir le formulaire
  $query = mysqli_query($conn, "SELECT * FROM tblchef WHERE Id ='$Id'");
  $row = mysqli_fetch_array($query);

  if (isset($_POST['update'])) {

    $firstName = trim($_POST['firstName']);
    $lastName  = trim($_POST['lastName']);
    $emailAddress = trim($_POST['emailAddress']);
    $phoneNo = trim($_POST['phoneNo']);
    $classId = $_POST['classId'];

    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);

    // Flag pour savoir si on peut mettre à jour
    $canUpdate = true;

    // Vérification du mot de passe seulement si rempli
    if (!empty($password)) {
      if ($password !== $cpassword) {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
                  Les mots de passe ne correspondent pas !
              </div>";
        $canUpdate = false; // empêche la mise à jour
      } else {
        $md5Password = md5($password);
      }
    }

    // Effectuer la mise à jour uniquement si $canUpdate = true
    if ($canUpdate) {
      if (!empty($password)) {
        $query = mysqli_query($conn, "UPDATE tblchef SET 
                  firstName='$firstName',
                  lastName='$lastName',
                  emailAddress='$emailAddress',
                  phoneNo='$phoneNo',
                  classId='$classId',
                  password='$md5Password'
                  WHERE Id='$Id'");
      } else {
        $query = mysqli_query($conn, "UPDATE tblchef SET 
                  firstName='$firstName',
                  lastName='$lastName',
                  emailAddress='$emailAddress',
                  phoneNo='$phoneNo',
                  classId='$classId'
                  WHERE Id='$Id'");
      }

      if ($query) {
        echo "<script type = \"text/javascript\">
            window.location = (\"createChef.php\")
            </script>";
      } else {
        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
                  Erreur lors de la mise à jour !
              </div>";
      }
    }
  }
}
//--------------------------------DELETE------------------------------------------------------------------

if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete") {
  $Id = $_GET['Id'];

  $query = mysqli_query($conn, "DELETE FROM tblchef WHERE Id='$Id'");

  if ($query) {

    echo "<script type = \"text/javascript\">
          window.location = (\"createChef.php\")
          </script>";
  } else {

    $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Erreur!</div>";
  }
}


?>

<!DOCTYPE html>
<html lang="en">

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
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">Ajouter un chef d'usine</h6>

          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="downloadUsines.php">Exporter</a>(Exel)</li>
            <li class="breadcrumb-item"><a href="#">Imprimer</a>(PDF)</li>

          </ol>

          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ajouter un chef</li>
          </ol>
        </div>


        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">


          <div class="row">
            <div class="col-lg-12">

              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3">
                      <div class="col-xl-4">
                        <label class="form-control-label">Nom<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" required name="firstName" value="<?php echo $row['firstName']; ?>" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-4">
                        <label class="form-control-label">Prénom<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" required name="lastName" value="<?php echo $row['lastName']; ?>" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-4">
                        <label class="form-control-label">Email<span class="text-danger ml-2">*</span></label>
                        <input type="email" class="form-control" required name="emailAddress" value="<?php echo $row['emailAddress']; ?>" id="exampleInputFirstName">
                      </div>
                    </div>
                    <div class="form-group row mb-3">

                      <div class="col-xl-4">
                        <label class="form-control-label">Numéro de Tel<span class="text-danger ml-2">*</span></label>
                        <input type="text" class="form-control" name="phoneNo" value="<?php echo $row['phoneNo']; ?>" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-4">
                        <label class="form-control-label">Mot de passe<span class="text-danger ml-2">*</span></label>
                        <input type="password" class="form-control" required name="password" id="exampleInputFirstName">
                      </div>
                      <div class="col-xl-4">
                        <label class="form-control-label">Comfirmer le mot de passe<span class="text-danger ml-2">*</span></label>
                        <input type="password" class="form-control" name="cpassword" id="exampleInputFirstName">
                      </div>
                    </div>
                    <div class="form-group row mb-3">
                      <div class="col-xl-4">
                        <label>Selectionner une usine<span class="text-danger ml-2">*</span></label>
                        <?php
                        // Récupération des classes
                        $qry = "SELECT * FROM tblservice ORDER BY serviceName ASC";
                        $result = $conn->query($qry);

                        if ($result->num_rows > 0) {
                          echo '<select required name="classId" class="form-control mb-3">';
                          echo '<option value="">--Usine--</option>';
                          while ($rowsClass = $result->fetch_assoc()) {
                            // Si on est en édition, sélection de l’usine déjà attribuée
                            $selected = (isset($row['classId']) && $rowsClass['Id'] == $row['classId']) ? 'selected' : '';
                            echo '<option value="' . $rowsClass['Id'] . '" ' . $selected . '>' . $rowsClass['serviceName'] . '</option>';
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
                      <a href="createChef.php" class="btn btn-secondary">Annuler</a>
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                    } else {
                    ?>
                      <button type="submit" name="save" class="btn btn-primary">Ajouter</button>
                    <?php
                    }
                    ?>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
              <div class="row">
                <div class="col-lg-12">
                  <div class="card mb-4">
                    <div class="table-responsive p-3">
                      <?php echo $statusMsg; ?>
                      <h6 class="font-weight-bold text-primary">Tous les chefs et leurs usines assignées</h1>
                        <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                          <thead class="thead-light">
                            <tr>
                              <th>#</th>
                              <th>Nom & Prenom</th>
                              <th>Email</th>
                              <th>Tel</th>
                              <th>Usine</th>
                              <th><i class='fas fa-fw fa-edit'></i></th>
                              <th><i class='fas fa-fw fa-trash'></i></th>
                            </tr>
                          </thead>

                          <tbody>

                            <?php
                            $query = "SELECT tblchef.Id,tblservice.serviceName,tblchef.firstName,
                      tblchef.lastName,tblchef.emailAddress,tblchef.phoneNo,tblchef.dateCreated
                      FROM tblchef
                      INNER JOIN tblservice ON tblservice.Id = tblchef.classId";
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
                                <td>" . $rows['firstName'] . '  ' . $rows['lastName'] . "</td>
                                <td>" . $rows['emailAddress'] . "</td>
                                <td>" . $rows['phoneNo'] . "</td>
                                <td>" . $rows['serviceName'] . "</td>
                                <td><a href='?action=edit&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-edit'></i></a></td>
                                <td><a href='?action=delete&Id=" . $rows['Id'] . " ']'><i class='fas fa-fw fa-trash'></i></a></td>
                              </tr>";
                              }
                            } else {
                              echo
                              "<div class='alert alert-danger' role='alert'>
                            Non trouvé!
                            </div>";
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

</body>

</html>