<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');
$dateTaken = date('Y-m-d');

// Protection session
$userId   = isset($_SESSION['userId'])   ? $_SESSION['userId']   : 0;
$classId  = isset($_SESSION['classId'])  ? $_SESSION['classId']  : 0;

if (!$userId || !$classId) {
  die("<div class='alert alert-danger'>Session invalide. Veuillez vous reconnecter.</div>");
}

// Nom du service
$query = "SELECT tblservice.serviceName
          FROM tblchef
          INNER JOIN tblservice ON tblservice.Id = tblchef.classId
          WHERE tblchef.Id = '" . mysqli_real_escape_string($conn, $userId) . "'";
$rs = $conn->query($query);
$rrw = $rs->fetch_assoc();
$serviceName = $rrw['serviceName'] ?? '(service inconnu)';

// Vérifier si CE SERVICE a déjà des données aujourd'hui
$qurty = $conn->query("
    SELECT COUNT(*) as cnt 
    FROM tblsupp 
    WHERE classId = '" . mysqli_real_escape_string($conn, $classId) . "'
      AND dateTimeTaken = '$dateTaken'
");
$rowCheck = $qurty->fetch_assoc();
$count = $rowCheck['cnt'] ?? 0;

$alreadyRecorded = ($count > 0);
$statusMsg = '';

// Création lignes vides si nécessaire
if (!$alreadyRecorded) {
  $qus = $conn->query("
        SELECT admissionNumber 
        FROM tblemployees 
        WHERE classId = '" . mysqli_real_escape_string($conn, $classId) . "'
    ");

  while ($ros = $qus->fetch_assoc()) {
    $adNo    = mysqli_real_escape_string($conn, $ros['admissionNumber']);
    $class   = mysqli_real_escape_string($conn, $classId);

    $insert = $conn->query("
            INSERT INTO tblsupp 
            (admissionNo, classId, heureDebut, heureFin, heures, montant, dateTimeTaken)
            VALUES ('$adNo', '$class', '00:00', '00:00', 0, 0, '$dateTaken')
        ");

    if (!$insert) {
      $statusMsg .= "<div class='alert alert-danger'>Erreur création employé $adNo</div>";
    }
  }
}

// Traitement formulaire
if (isset($_POST['save'])) {

  if ($alreadyRecorded) {
    $statusMsg = "<div class='alert alert-danger'>
            Les heures supplémentaires pour aujourd'hui sont déjà enregistrées.
        </div>";
  } else {
    $admissionNo = $_POST['admissionNo']   ?? [];
    $heureDebut  = $_POST['heureDebut']    ?? [];
    $heureFin    = $_POST['heureFin']      ?? [];
    $heures      = $_POST['heures']        ?? [];
    $check       = $_POST['check']         ?? [];

    $erreur = false;

    foreach ($admissionNo as $i => $adNoRaw) {
      $adNo = mysqli_real_escape_string($conn, $adNoRaw);

      if (!in_array($adNo, $check)) {
        continue; // on n'enregistre que les cochés
      }

      $hd = mysqli_real_escape_string($conn, $heureDebut[$i] ?? '00:00');
      $hf = mysqli_real_escape_string($conn, $heureFin[$i]   ?? '00:00');
      $h  = (int)($heures[$i] ?? 0);

      // Salaire
      $rs = $conn->query("SELECT salaire FROM tblemployees WHERE admissionNumber='$adNo' LIMIT 1");
      $row = $rs->fetch_assoc();

      if (!$row || empty($row['salaire'])) {
        $statusMsg = "<div class='alert alert-danger'>Salaire introuvable pour $adNo</div>";
        $erreur = true;
        break;
      }

      $salaire = (float)$row['salaire'];
      $montant = ($salaire * $h) / 240;

      // INSERT (pas UPDATE)
      $insert = $conn->query("
                INSERT INTO tblsupp 
                (admissionNo, classId, heureDebut, heureFin, heures, montant, dateTimeTaken)
                VALUES ('$adNo', '$classId', '$hd', '$hf', '$h', '$montant', '$dateTaken')
                ON DUPLICATE KEY UPDATE 
                    heureDebut = VALUES(heureDebut),
                    heureFin   = VALUES(heureFin),
                    heures     = VALUES(heures),
                    montant    = VALUES(montant)
            ");

      if (!$insert) {
        $statusMsg = "<div class='alert alert-danger'>Erreur enregistrement $adNo</div>";
        $erreur = true;
        break;
      }
    }

    if (!$erreur) {
      $statusMsg = "<div class='alert alert-success'>
                Enregistrement effectué avec succès !
            </div>";

      // Redirection
      header("Location: index.php");
      exit;
    }
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
  <title>Supplémentaires</title>
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


        <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">Ajouter les heures supplémentaires <b>Le <?php echo $todaysDate = date("d-m-Y"); ?></b></h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Tous les employés d'usine</li>
            </ol>
        </div>


        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">


          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->


              <!-- Input Group -->
              <form method="post">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="card mb-4">
                      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary" style="margin-left:30px">Tous les employés de <b><?php echo $rrw['serviceName']; ?></b></h6>
                        <h6 class="m-0 font-weight-bold text-danger">Note: <i>Cochez dans la case pour marquer les heures supplémentaires!</i></h6>
                      </div>
                      <div class="table-responsive p-3 ">
                        <?php echo $statusMsg; ?>
                        <table class="table align-items-center table-flush table-hover">
                          <thead class="thead-light">
                            <tr>
                              <th>#</th>
                              <th>Nom & Prenom</th>
                              <th>Badge</th>
                              <th>Poste</th>
                              <th>Debut</th>
                              <th>Fin</th>
                              <th>Heures</th>
                              <th>Cocher</th>
                            </tr>
                          </thead>

                          <tbody>

                            <?php
                            $query = "SELECT tblemployees.Id,tblemployees.admissionNumber,tblservice.serviceName,tblservice.Id As classId,tblemployees.firstName,
                      tblemployees.lastName,tblemployees.poste
                      FROM tblemployees
                      INNER JOIN tblservice ON tblservice.Id = tblemployees.classId
                      where tblemployees.classId = '$_SESSION[classId]'
                      ORDER BY tblemployees.firstName ASC ";
                            $rs = $conn->query($query);
                            $num = $rs->num_rows;
                            $sn = 0;
                            if ($num > 0) {
                              while ($rows = $rs->fetch_assoc()) {
                                $sn = $sn + 1;
                                echo "
                              <tr>
                                <td>" . $sn . "</td>
                                <td>" . $rows['firstName'] . "  " . $rows['lastName'] . "</td>
                                <td>" . $rows['admissionNumber'] . "</td>
                                <td>" . $rows['poste'] . "</td>
                                <td><input class='heureDebut' name='heureDebut[]' type='time' value='01:00' style='width:80px;color:#6e707e'></td>
                                <td><input class='heureFin'   name='heureFin[]'   type='time' value='08:00' style='width:80px;color:#6e707e'></td>
                                <td><input class='duree'      name='heures[]'     type='number' value='7' style='width:58px;color:#6e707e'></td>

                              
                                <td><input name='check[]' type='checkbox' value=" . $rows['admissionNumber'] . " class='form-control'></td>
                              </tr>";
                                echo "<input name='admissionNo[]' value=" . $rows['admissionNumber'] . " type='hidden' class='form-control'>";
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
                        <br>
                        <button type="submit" name="save" class="btn btn-primary">Ajouter</button>
              </form>
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

    function formatDureeHeures(duree) {
      // Prendre uniquement la partie entière
      return Math.floor(duree).toString();
    }

    function calculerDuree(ligne) {
      const debut = ligne.querySelector(".heureDebut").value;
      const fin = ligne.querySelector(".heureFin").value;
      const duree = ligne.querySelector(".duree");

      if (!debut || !fin) return;

      const [hd, md] = debut.split(":").map(Number);
      const [hf, mf] = fin.split(":").map(Number);

      let debutMinutes = hd * 60 + md;
      let finMinutes = hf * 60 + mf;

      // Passage au lendemain
      if (finMinutes < debutMinutes) finMinutes += 24 * 60;

      let dureeHeures = (finMinutes - debutMinutes) / 60;

      duree.value = formatDureeHeures(dureeHeures);
    }

    function calculerHeureFin(ligne) {
      const debut = ligne.querySelector(".heureDebut").value;
      const duree = parseFloat(ligne.querySelector(".duree").value);
      const fin = ligne.querySelector(".heureFin");

      if (!debut || isNaN(duree)) return;

      const [hd, md] = debut.split(":").map(Number);

      let finMinutes = hd * 60 + md + duree * 60;

      // Passage au lendemain
      if (finMinutes >= 24 * 60) finMinutes -= 24 * 60;

      const hf = Math.floor(finMinutes / 60);
      const mf = Math.floor(finMinutes % 60);

      fin.value = `${hf.toString().padStart(2,'0')}:${mf.toString().padStart(2,'0')}`;
    }

    // Appliquer à chaque ligne
    document.querySelectorAll("table tbody tr").forEach(ligne => {
      const debutInput = ligne.querySelector(".heureDebut");
      const finInput = ligne.querySelector(".heureFin");
      const dureeInput = ligne.querySelector(".duree");

      // Modifier début ou fin → recalculer durée
      debutInput.addEventListener("change", () => calculerDuree(ligne));
      finInput.addEventListener("change", () => calculerDuree(ligne));

      // Modifier durée → recalculer heureFin
      dureeInput.addEventListener("change", () => calculerHeureFin(ligne));
    });
  </script>



</body>

</html>