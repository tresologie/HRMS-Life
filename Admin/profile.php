<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

$statusMsg = "";

// ID admin connecté
$Id = $_SESSION['userId'];

// récupérer les informations
$query = mysqli_query($conn, "SELECT * FROM tbladmin WHERE Id='$Id'");
$row = mysqli_fetch_assoc($query);


//--------------------UPDATE PROFILE----------------------------

if (isset($_POST['update'])) {

  $firstName = trim($_POST['firstName']);
  $lastName  = trim($_POST['lastName']);
  $emailAddress = trim($_POST['emailAddress']);
  $poste = trim($_POST['poste']);
  $password = trim($_POST['password']);
  $cpassword = trim($_POST['cpassword']);

  $canUpdate = true;

  if (!empty($password)) {

    if ($password != $cpassword) {

      $statusMsg = "<div class='alert alert-danger'>
      Les mots de passe ne correspondent pas !
      </div>";

      $canUpdate = false;
    } else {

      $password = md5($password);
    }
  }

  if ($canUpdate) {

    if (!empty($password)) {

      $update = mysqli_query($conn, "UPDATE tbladmin SET
      firstName='$firstName',
      lastName='$lastName',
      emailAddress='$emailAddress',
      poste='$poste',
      password='$password'
      WHERE Id='$Id'");
    } else {

      $update = mysqli_query($conn, "UPDATE tbladmin SET
      firstName='$firstName',
      lastName='$lastName',
      emailAddress='$emailAddress',
      poste='$poste'
      WHERE Id='$Id'");
    }

    if ($update) {

      $statusMsg = "<div class='alert alert-success'>
      Profil modifié avec succès
      </div>";

      $query = mysqli_query($conn, "SELECT * FROM tbladmin WHERE Id='$Id'");
      $row = mysqli_fetch_assoc($query);
    } else {

      $statusMsg = "<div class='alert alert-danger'>
      Erreur lors de la mise à jour
      </div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="img/logo/life.jpg" rel="icon">

  <title>Profil</title>

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
            Profil Administrateur
          </h6>

          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">Profil</li>
          </ol>

        </div>
        <hr class="sidebar-divider mt-0">
        <div class="container-fluid" id="container-wrapper">

          <?php echo $statusMsg; ?>



          <!-- ===================== MODE AFFICHAGE ===================== -->

          <?php if (!isset($_GET['action'])) { ?>

            <div class="row justify-content-center">

              <div class="col-lg-4">

                <div class="card shadow">

                  <div class="card-body text-center">
                    <h5 class="font-weight-bold text-primary">
                      Life Company
                    </h5>

                    <i class="fas fa-user-circle fa-5x text-primary"></i>

                    <h4 class="mt-3">
                      <?php echo $row['firstName'] . " " . $row['lastName']; ?>
                    </h4>

                    <hr>

                    <p><b>Email :</b> <?php echo $row['emailAddress']; ?></p>

                    <p><b>Poste :</b> <?php echo $row['poste']; ?></p>

                    <a href="profile.php?action=edit" class="btn btn-warning">

                      <i class="fas fa-edit"></i> Modifier le profil

                    </a>

                  </div>

                </div>

              </div>

            </div>

          <?php } ?>


          <!-- ===================== MODE EDIT ===================== -->

          <?php if (isset($_GET['action']) && $_GET['action'] == "edit") { ?>

            <div class="row justify-content-center">

              <div class="col-lg-8">

                <div class="card shadow">

                  <div class="card-body">

                    <h5 class="text-primary text-center mb-4">
                      Modifier le profil
                    </h5>

                    <form method="POST">

                      <div class="form-row">

                        <div class="form-group col-md-6">

                          <label>Nom</label>

                          <input type="text"
                            class="form-control"
                            name="firstName"
                            value="<?php echo $row['firstName']; ?>"
                            required>

                        </div>

                        <div class="form-group col-md-6">

                          <label>Prénom</label>

                          <input type="text"
                            class="form-control"
                            name="lastName"
                            value="<?php echo $row['lastName']; ?>"
                            required>

                        </div>

                      </div>


                      <div class="form-group">

                        <label>Email</label>

                        <input type="email"
                          class="form-control"
                          name="emailAddress"
                          value="<?php echo $row['emailAddress']; ?>"
                          required>

                      </div>


                      <div class="form-group">

                        <label>Poste</label>

                        <input type="text"
                          class="form-control"
                          name="poste"
                          value="<?php echo $row['poste']; ?>">

                      </div>

                      <hr>

                      <h6 class="text-primary">Changer le mot de passe</h6>

                      <div class="form-row">

                        <div class="form-group col-md-6">

                          <label>Nouveau mot de passe</label>

                          <input type="password"
                            class="form-control"
                            name="password">

                        </div>

                        <div class="form-group col-md-6">

                          <label>Confirmer mot de passe</label>

                          <input type="password"
                            class="form-control"
                            name="cpassword">

                        </div>

                      </div>

                      <div class="text-center">

                        <button type="submit"
                          name="update"
                          class="btn btn-primary">

                          <i class="fas fa-save"></i> Enregistrer

                        </button>

                        <a href="profile.php"
                          class="btn btn-secondary">

                          Annuler

                        </a>

                      </div>

                    </form>

                  </div>

                </div>

              </div>

            </div>

          <?php } ?>

        </div>

      </div>

    </div>

  </div>

  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>

</body>

</html>