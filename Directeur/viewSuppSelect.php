<?php
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

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
  <title>Heures supplémentaires</title>
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
        xmlhttp.open("GET", "ajaxCallTypes.php?tid=" + str, true);
        xmlhttp.send();
      }
    }
  </script>

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
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">Heures supplémentaires d'un employé</h6>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Heures suppl</li>
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
                    <div class="form-group row mb-3" style="max-height:500px; overflow-y:auto; ">
                      <div class="col-xl-4">
                        <label class="form-control-label">Selectionner un employé<span class="text-danger ml-2">*</span></label>
                        <?php
                        $qry = "SELECT * FROM tblemployees  ORDER BY firstName ASC";
                        $result = $conn->query($qry);
                        $num = $result->num_rows;
                        if ($num > 0) {
                          echo ' <select required name="admissionNumber" class="form-control mb-3">';
                          echo '<option value="">--Choisir un employé--</option>';
                          while ($rows = $result->fetch_assoc()) {
                            echo '<option value="' . $rows['admissionNumber'] . '" >' . $rows['firstName'] . ' ' . $rows['lastName'] . '</option>';
                          }
                          echo '</select>';
                        }
                        ?>
                      </div>
                      <div class="col-xl-4">
                        <label class="form-control-label">Un jour/Tous les jours/De Tel ... à ...<span class="text-danger ml-2">*</span></label>
                        <select required name="type" onchange="typeDropDown(this.value)" class="form-control mb-3">
                          <option value="">--Choisir--</option>
                          <option value="2">Un jour</option>
                          <option value="1">Tous les jours</option>
                          <option value="3">De tel date à tel date</option>
                        </select>
                      </div>
                    </div>
                    <?php
                    echo "<div id='txtHint'></div>";
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
                      <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                          <tr>
                            <th>#</th>
                            <th>Nom & Prenom</th>
                            <th>Badge</th>
                            <th>Usine</th>
                            <th>Poste</th>
                            <th>Date</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Heures</th>
                            <th>Montant</th>
                            <th><i class='fas fa-fw fa-edit'></i></th>

                          </tr>
                        </thead>

                        <tbody>

                          <?php

                          if (isset($_POST['view'])) {

                            $admissionNumber =  $_POST['admissionNumber'];
                            $type =  $_POST['type'];

                            if ($type == "1") { //All Attendance

                              $query = "SELECT tblsupp.Id, tblsupp.heureDebut,tblsupp.heureFin,
                        FLOOR(tblsupp.montant / 100) * 100 AS montant,
                        tblsupp.dateTimeTaken, tblemployees.poste,tblservice.serviceName,tblsupp.heures,
                        tblemployees.firstName,tblemployees.lastName,tblemployees.identite,tblemployees.admissionNumber,tblemployees.poste
                        FROM tblsupp
                        INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
                        INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
                        where tblsupp.admissionNo = '$admissionNumber'
                        ORDER BY tblservice.serviceName, tblemployees.firstName ASC";
                            }
                            if ($type == "2") { //Single Date Attendance

                              $singleDate =  $_POST['singleDate'];

                              $query = "SELECT tblsupp.Id,tblsupp.dateTimeTaken,tblsupp.heureDebut,tblsupp.heureFin,
                         FLOOR(tblsupp.montant / 100) * 100 AS montant,
                          tblemployees.poste,tblservice.serviceName,tblemployees.firstName,tblemployees.lastName,tblsupp.heures,
                          tblemployees.identite,tblemployees.admissionNumber,tblemployees.poste
                        FROM tblsupp
                        INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
                       
                        INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
                        where tblsupp.dateTimeTaken = '$singleDate' and tblsupp.admissionNo = '$admissionNumber'
                        ORDER BY tblservice.serviceName, tblemployees.firstName ASC";
                            }
                            if ($type == "3") { //Date Range Attendance

                              $fromDate =  $_POST['fromDate'];
                              $toDate =  $_POST['toDate'];

                              $query = "SELECT tblsupp.Id,tblsupp.heureDebut,tblsupp.heureFin,
                          FLOOR(tblsupp.montant / 100) * 100 AS montant,
                         tblsupp.dateTimeTaken, tblemployees.poste,tblservice.serviceName,tblsupp.heures,
                        tblemployees.firstName,tblemployees.lastName,tblemployees.admissionNumber,tblemployees.poste
                        FROM tblattendance
                        INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
                        INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
                        where tblsupp.dateTimeTaken between '$fromDate' and '$toDate' 
                        and tblsupp.admissionNo = '$admissionNumber'  
                        ORDER BY tblservice.serviceName, tblemployees.firstName ASC";
                            }

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
                                <td>" . $rows['admissionNumber'] . "</td>
                                <td>" . $rows['serviceName'] . "</td>
                                <td>" . $rows['poste'] . "</td>
                                <td>" . $rows['dateTimeTaken'] . "</td>
                                <td>" . date('H:i', strtotime($rows['heureDebut'])) . "</td>
                                <td>" . date('H:i', strtotime($rows['heureFin'])) . "</td>
                                <td>" . $rows['heures'] . "</td>
                                <td style='font-weight:bold;'>" . number_format($rows['montant'], 0, ',', ' ') . " Fbu</td>
                                <td><a href='?action=edit&Id=" . $rows['Id'] . "'><i class='fas fa-fw fa-edit'></i></a></td>
                              </tr>";
                              }
                            } else {
                              echo
                              "<div class='alert alert-danger' role='alert'>
                            Non trouvés!
                            </div>";
                            }
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