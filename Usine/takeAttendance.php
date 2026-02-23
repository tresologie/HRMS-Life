
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

        $qurty=mysqli_query($conn,"select * from tblattendance  where classId = '$_SESSION[classId]' and dateTimeTaken='$dateTaken'");
        $count = mysqli_num_rows($qurty);

        if($count == 0){ //if Record does not exsit, insert the new record

          //insert the students record into the attendance table on page load
          $qus=mysqli_query($conn,"select * from tblstudents  where classId = '$_SESSION[classId]' ");
          while ($ros = $qus->fetch_assoc())
          {
              $qquery=mysqli_query($conn,"insert into tblattendance(admissionNo,classId,status,dateTimeTaken) 
              value('$ros[admissionNumber]','$_SESSION[classId]','0','$dateTaken')");

          }
        }

  
      



if(isset($_POST['save'])){
    
    $admissionNo=$_POST['admissionNo'];

    $check=$_POST['check'];
    $N = count($admissionNo);
    $status = "";


//check if the attendance has not been taken i.e if no record has a status of 1
  $qurty=mysqli_query($conn,"select * from tblattendance  where classId = '$_SESSION[classId]'
    and dateTimeTaken='$dateTaken' and status = '1'");
  $count = mysqli_num_rows($qurty);

  if($count > 0){

      $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>L'appel déjà faits!</div>";

  }

    else //update the status to 1 for the checkboxes checked
    {

        for($i = 0; $i < $N; $i++)
        {
                $admissionNo[$i]; //admission Number

                if(isset($check[$i])) //the checked checkboxes
                {
                      $qquery=mysqli_query($conn,"update tblattendance set status='1' where admissionNo = '$check[$i]'");

                      if ($qquery) {

                          $statusMsg = "<div class='alert alert-success'  style='margin-right:700px;'>Les présences marqués avec succés!</div>";
                      }
                      else
                      {
                          $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Erreur!</div>";
                      }
                  
                }
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
  <title>Appel</title>
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
            <h1 class="h3 mb-0 text-gray-800">Faire l'appel (Aujourd'hui Le <?php echo $todaysDate = date("d-m-Y");?>)</h1>
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
                  <h6 class="m-0 font-weight-bold text-danger">Note: <i>Cochez dans la case pour marquer la présence!</i></h6>
                </div>
                <div class="table-responsive p-3"  style="max-height:500px; overflow-y:auto; ">
                <?php echo $statusMsg; ?>
                  <table class="table align-items-center table-flush table-hover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Nom & Prenom</th>
                        <th>Badge</th>
                        <th>Poste</th>
                        <th>Cocher</th>
                      </tr>
                    </thead>
                    
                    <tbody>

                  <?php
                      $query = "SELECT tblstudents.Id,tblstudents.admissionNumber,tblclass.className,tblclass.Id As classId,tblstudents.firstName,
                      tblstudents.lastName,tblstudents.admissionNumber,tblstudents.poste, tblstudents.dateCreated
                      FROM tblstudents
                      INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
                  
                      where tblstudents.classId = '$_SESSION[classId]' ";
                      $rs = $conn->query($query);
                      $num = $rs->num_rows;
                      $sn=0;
                      $status="";
                      if($num > 0)
                      { 
                        while ($rows = $rs->fetch_assoc())
                          {
                             $sn = $sn + 1;
                            echo"
                              <tr>
                                <td>".$sn."</td>
                                <td>".$rows['firstName'].'  '.$rows['lastName']."</td> 
                                <td>".$rows['admissionNumber']."</td>
                                <td>".$rows['poste']."</td>
                              
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
                  <button type="submit" name="save" class="btn btn-primary">Faire l'appel</button>
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
</script>


</body>

</html>