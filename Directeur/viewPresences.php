
<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// Définir période du 28 au 28
$today = date('Y-m-d');

if(date('d') >= 28){
    $fromDate = date('Y-m-28');
    $toDate = date('Y-m-28', strtotime('+1 month'));
} else {
    $fromDate = date('Y-m-28', strtotime('-1 month'));
    $toDate = date('Y-m-28');
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
  <title>Presences des employés</title>
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">

<script>
    function typeDropDown(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else { 
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET","ajaxCallTypes.php?tid="+str,true);
        xmlhttp.send();
    }
}
</script>

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
        <div class="container-fluid" id="container-wrapper">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h6 class="font-weight-bold text-primary" style="margin-left:30px">Présences des employés</h6>


            <ol class="breadcrumb">
              <li class="breadcrumb-item">
              <a href="downloadPresences.php?fromDate=<?php echo $fromDate; ?>
              &toDate=<?php echo $toDate; ?>"> Exporter</a>(Exel)</li>
              <li class="breadcrumb-item">
              <a href="printPresences.php?fromDate=<?php echo $fromDate; ?>
              &toDate=<?php echo $toDate; ?>">Imprimer</a>(PDF)</li>
              
            </ol>

            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Présences</li>
            </ol>
          </div>
        

          <div class="row">
            <div class="col-lg-12">
              <!-- Form Basic -->
              <div class="card mb-4">
                <div class="card-body">
                  <form method="post">
                    <div class="form-group row mb-3 ">
                   
                    
                        <div class="col-xl-4">
                        <label class="form-control-label">Un jour/Un mois/De ...à ...<span class="text-danger ml-2">*</span></label>
                          <select required name="type" onchange="typeDropDown(this.value)" class="form-control mb-3">
                          <option value="">--Choisir--</option>
                          <option value="2" >Un jour</option>
                          <option value="1" >Un mois</option>
                          <option value="3" >De ... à ...</option>
                        </select>
                        </div>
                    </div>
                      <?php
                        echo"<div id='txtHint'></div>";
                      ?>

                    <button type="submit" name="view" class="btn btn-primary">Afficher</button>
                  </form>
                </div>
              </div>

              <!-- Input Group -->
                 <div class="row">
              <div class="col-lg-12">
              <div class="card mb-4">
                <div class="table-responsive p-3">
                <table class="table table-bordered table-hover table-sm" id="dataTableHover">

                  <?php

if(isset($_POST['view'])){

  $type = $_POST['type'];

  // Déterminer la période
  if($type == "1"){ // un mois
    if(date('d') >= 28){
      $fromDate = date('Y-m-28');
      $toDate   = date('Y-m-28', strtotime('+1 month'));
  } else {
      $fromDate = date('Y-m-28', strtotime('-1 month'));
      $toDate   = date('Y-m-28');
  }

   }elseif($type == "2"){ // Single date
  $singleDate = $_POST['singleDate'];
        $fromDate = $singleDate;
       $toDate = $singleDate;

    }elseif($type == "3"){ // Date Range
        $fromDate = $_POST['fromDate'];
        $toDate = $_POST['toDate'];

      // Limiter à 31 jours max
      $diff = (strtotime($toDate) - strtotime($fromDate)) / (60*60*24) + 1;

      if($diff > 31){
          // Limiter à 31 jours maximum
          $toDate = date('Y-m-d', strtotime($fromDate . ' +30 days'));
      }

   } 
}

  // Requête SQL
  $query = "SELECT 
    tblemployees.admissionNumber,
    tblemployees.firstName,
    tblemployees.lastName,
    tblemployees.poste,
    tblservice.serviceName,
    tblattendance.dateTimeTaken,
    tblattendance.status
FROM tblattendance
INNER JOIN tblservice ON tblservice.Id = tblattendance.classId
INNER JOIN tblemployees ON tblemployees.admissionNumber = tblattendance.admissionNo
WHERE tblattendance.dateTimeTaken BETWEEN '$fromDate' AND '$toDate'
ORDER BY tblservice.serviceName ASC, tblemployees.firstName ASC";

  $rs = $conn->query($query);

  // Préparer le tableau
  $dates = [];
  $data = [];

  while ($row = $rs->fetch_assoc()) {

    $emp = $row['admissionNumber'];
    $date = $row['dateTimeTaken'];
    $status = $row['status'];

    $dates[$date] = $date;

    $data[$emp]['name'] = $row['firstName'].' '.$row['lastName'];
    $data[$emp]['badge'] = $row['admissionNumber'];
    $data[$emp]['usine'] = $row['serviceName'];
    $data[$emp]['poste'] = $row['poste'];

    // Marquer P ou A
    $data[$emp]['values'][$date] = ($status == 1) ? 'P' : 'A';
}
  // Trier et limiter dates à 7 max
  ksort($dates);
  $sn=0;

  // Affichage du tableau
  if(!empty($data)){

      echo "<thead class='thead-light'>";
      echo "<tr>";
      echo "<th>#</th>";
      echo "<th>Nom & Prenom</th>";
      echo "<th>Badge</th>";
      echo "<th>Usine</th>";
      echo "<th>Poste</th>";
      foreach($dates as $date){
         echo "<th>".date("d/m", strtotime($date))."</th>";
         } 
         echo "<th>TOTAL</th>"; // colonne Total 
         echo "</tr></thead>";

      foreach($data as $emp => $info){

        $totalP = 0;
        $sn = $sn + 1;
    
        echo "<tr>";
        echo "<td>$sn</td>";
        echo "<td>".$info['name']."</td>";
        echo "<td>".$info['badge']."</td>";
        echo "<td>".$info['usine']."</td>";
        echo "<td >".$info['poste']."</td>";
    
        foreach($dates as $date){
    
            $value = isset($info['values'][$date]) ? $info['values'][$date] : '';
    
            if($value == 'P'){
                $totalP++;
            }
    
            echo "<td style='text-align:center;'>".$value."</td>";
        }
    
        echo "<td style='font-weight:bold;'>".$totalP."</td>";
        echo "</tr>";
    }

      echo "</tbody>";

  } else {

      echo "<tr>
              <td colspan='".(count($dates)+30)."'>
                  <div class='alert alert-danger'>
                      Non trouvés!
                  </div>
              </td>
            </tr>";
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

  <!-- Page level custom scripts -->
  <script>
$(document).ready(function () {
  $('#dataTableHover').DataTable({
        scrollX: true,
        autoWidth:true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
    });
});
</script>


</body>

</html>