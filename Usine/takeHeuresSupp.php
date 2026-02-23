
<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

    $query = "SELECT tblclass.className
    FROM tblclassteacher
    INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId

    Where tblclassteacher.Id = '$_SESSION[userId]'";
    $rs = $conn->query($query);
    $num = $rs->num_rows;
    $rrw = $rs->fetch_assoc();


    
    date_default_timezone_set('Africa/Bujumbura');
    $dateTaken = date("Y-m-d");
    
    // Vérifier si des enregistrements existent déjà pour aujourd'hui
    $qurty = mysqli_query($conn, "SELECT * FROM tblsupp WHERE classId = '".$_SESSION['classId']."' AND dateTimeTaken='$dateTaken'");
    $count = mysqli_num_rows($qurty);
    
    // Si aucun enregistrement pour aujourd'hui, insérer automatiquement
    if($count == 0){
    
        $qus = mysqli_query($conn, "SELECT * FROM tblstudents WHERE classId = '".$_SESSION['classId']."'");
    
        while ($ros = $qus->fetch_assoc()) {
    
            $admissionNo = mysqli_real_escape_string($conn, $ros['admissionNumber']);
            $classId     = mysqli_real_escape_string($conn, $_SESSION['classId']);
    
            // Valeurs par défaut lors de l'insertion automatique
            $heureDebut  = '00:00';
            $heureFin    = '00:00';
            $heures      = 0;
            $montant     = 0;
    
            $insert = mysqli_query($conn, "
                INSERT INTO tblsupp (admissionNo, classId, heureDebut, heureFin, heures, montant, dateTimeTaken)
                VALUES ('$admissionNo', '$classId', '$heureDebut', '$heureFin', '$heures', '$montant', '$dateTaken')
            ");
    
            if(!$insert){
                echo "Erreur d'insertion: " . mysqli_error($conn);
            }
        }
    }
    
    // Mise à jour après soumission du formulaire
    if(isset($_POST['save'])){

      $admissionNo = $_POST['admissionNo'];
      $heureDebut  = $_POST['heureDebut'];
      $heureFin    = $_POST['heureFin'];
      $heures      = $_POST['heures'];
      $check       = isset($_POST['check']) ? $_POST['check'] : array();
      $N = count($admissionNo);
  
      $erreur = false;
  
      for($i = 0; $i < $N; $i++){
  
          $adNo = mysqli_real_escape_string($conn, $admissionNo[$i]);
  
          if(in_array($adNo, $check)){ 
  
              $hd = mysqli_real_escape_string($conn, $heureDebut[$i]);
              $hf = mysqli_real_escape_string($conn, $heureFin[$i]);
              $h  = mysqli_real_escape_string($conn, $heures[$i]);
  
              // Récupérer le salaire
              $rs = mysqli_query($conn, "SELECT salaire FROM tblstudents WHERE admissionNumber='$adNo' LIMIT 1");
  
              if(!$rs){
                  echo "Erreur récupération salaire pour $adNo : " . mysqli_error($conn);
                  $erreur = true;
                  break;
              }
  
              $row = mysqli_fetch_assoc($rs);
  
              if(!$row){
                  echo "Salaire introuvable pour $adNo";
                  $erreur = true;
                  break;
              }
  
              $salaire = $row['salaire'];
              $m = ($salaire * $h) / 240;
  
          } else {
  
              $hd = '00:00';
              $hf = '00:00';
              $h  = 0;
              $m  = 0;
          }

              // Vérifier si déjà enregistré
$verif = mysqli_query($conn, "
SELECT heures FROM tblsupp 
WHERE admissionNo='$adNo' 
AND dateTimeTaken='$dateTaken'
LIMIT 1
");

$rowVerif = mysqli_fetch_assoc($verif);

if($rowVerif && $rowVerif['heures'] > 0 && in_array($adNo, $check)){
  $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>
                  Les heures supplémentaires sont déjà marquées
                </div>";
  $erreur = true;
  break;
}
      
  
          $update = mysqli_query($conn, "UPDATE tblsupp 
              SET heureDebut='$hd',  heureFin='$hf', heures='$h',  montant='$m' 
              WHERE admissionNo='$adNo' AND dateTimeTaken='$dateTaken'
          ");
  
          if(!$update){
              echo "Erreur mise à jour pour $adNo : " . mysqli_error($conn);
              $erreur = true;
              break;
          }
      }
  
      if(!$erreur){
          $statusMsg = "<div class='alert alert-success' style='margin-right:700px;'>
                          Les heures supplémentaires ont été enregistrées avec succès!
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
      <?php include "Includes/sidebar.php";?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
       <?php include "Includes/topbar.php";?>
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ajouter les heures supplémentaires (Aujourd'hui Le <?php echo $todaysDate = date("d-m-Y");?>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Tous les employés d'usine</li>
            </ol>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->


              <!-- Input Group -->
        <form method="post"> 
            <div class="row">
              <div class="col-lg-12"> 
              <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Tous les employés de (<?php echo $rrw['className'];?>)</h6>
                  <h6 class="m-0 font-weight-bold text-danger">Note: <i>Cochez dans la case pour marquer les heures supplémentaires!</i></h6>
                </div>
                <div class="table-responsive p-3" style="max-height:500px; overflow-y:auto; ">
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
                      $query = "SELECT tblstudents.Id,tblstudents.admissionNumber,tblclass.className,tblclass.Id As classId,tblstudents.firstName,
                      tblstudents.lastName,tblstudents.poste
                      FROM tblstudents
                      INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                  
                      where tblstudents.classId = '$_SESSION[classId]' ";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn=0;
                      if($num > 0)
                      { 
                        while ($rows = $rs->fetch_assoc())
                          {
                             $sn = $sn + 1;
                            echo"
                              <tr>
                                <td>".$sn."</td>
                                <td>".$rows['firstName']."  ".$rows['lastName']."</td>
                                <td>".$rows['admissionNumber']."</td>
                                <td>".$rows['poste']."</td>
                                <td><input class='heureDebut' name='heureDebut[]' type='time' value='01:00' style='width:80px;'></td>
                                <td><input class='heureFin'   name='heureFin[]'   type='time' value='08:00' style='width:80px;'></td>
                                <td><input class='duree'      name='heures[]'     type='number' value='7' style='width:60px;'></td>

                              
                                <td><input name='check[]' type='checkbox' value=".$rows['admissionNumber']." class='form-control'></td>
                              </tr>";
                              echo "<input name='admissionNo[]' value=".$rows['admissionNumber']." type='hidden' class='form-control'>";
                          }
                      }
                      else
                      {
                           echo   
                           "<div class='alert alert-danger' role='alert'>
                            Non trouvé!
                            </div>";
                      }
                      
                      ?>
                    </tbody>
                  </table>
                  <br>
                  <button type="submit" name="save" class="btn btn-primary">Supplémentaire</button>
                  </form>
                </div>
              </div>
            </div>
            </div>
          </div>
         

        </div>
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
       <?php include "Includes/footer.php";?>
      <!-- Footer -->
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
$(document).ready(function () {
    $('#dataTable').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
    });

    $('#dataTableHover').DataTable({
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
    const fin   = ligne.querySelector(".heureFin").value;
    const duree = ligne.querySelector(".duree");

    if (!debut || !fin) return;

    const [hd, md] = debut.split(":").map(Number);
    const [hf, mf] = fin.split(":").map(Number);

    let debutMinutes = hd*60 + md;
    let finMinutes   = hf*60 + mf;

    // Passage au lendemain
    if (finMinutes < debutMinutes) finMinutes += 24*60;

    let dureeHeures = (finMinutes - debutMinutes)/60;

    duree.value = formatDureeHeures(dureeHeures);
}

function calculerHeureFin(ligne) {
    const debut = ligne.querySelector(".heureDebut").value;
    const duree = parseFloat(ligne.querySelector(".duree").value);
    const fin   = ligne.querySelector(".heureFin");

    if (!debut || isNaN(duree)) return;

    const [hd, md] = debut.split(":").map(Number);

    let finMinutes = hd*60 + md + duree*60;

    // Passage au lendemain
    if (finMinutes >= 24*60) finMinutes -= 24*60;

    const hf = Math.floor(finMinutes/60);
    const mf = Math.floor(finMinutes%60);

    fin.value = `${hf.toString().padStart(2,'0')}:${mf.toString().padStart(2,'0')}`;
}

// Appliquer à chaque ligne
document.querySelectorAll("table tbody tr").forEach(ligne => {
    const debutInput = ligne.querySelector(".heureDebut");
    const finInput   = ligne.querySelector(".heureFin");
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