
<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';


    $query = "SELECT tblclass.className
    FROM tblclassteacher
    INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
    Where tblclassteacher.Id = '$_SESSION[userId]'";

    $rs = $conn->query($query);
    $num = $rs->num_rows;
    $rrw = $rs->fetch_assoc();


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
  <title>Tableau de bord</title>
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
            <h1 class="h3 mb-0 text-gray-800">Statistiques du <?php echo $todaysDate = date("d-m-Y");?>
            (<b><?php echo $rrw['className'];?></b>)</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
            </ol>
          </div>

      <div class="row mb-3">
          <!-- New User Card Example -->
          <?php 
          $query1=mysqli_query($conn,"SELECT * from tblstudents where classId = '$_SESSION[classId]'");

          $students = mysqli_num_rows($query1);
        
          ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Tous les Employés</div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $students;?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> 20.4%</span>
                        <span>Since last month</span> -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x" style="color: blue;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <!-- Std Att Card  -->
            <?php 
            $query1=mysqli_query($conn,"SELECT * from tblattendance where status = '1' and classId = '$_SESSION[classId]' and DATE(dateTimeTaken) = CURDATE()");                       
             $totAttendance = mysqli_num_rows($query1);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Présents</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totAttendance;?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                        <!-- <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> 1.10%</span>
                        <span>Since yesterday</span> -->
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar-check fa-2x text-success" ></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>



            <!-- Std Att Card  -->
            <?php 
                    
            $absent = $students-$totAttendance;
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Absents</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $absent ;?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                      </div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-user-times fa-2x text-secondary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>   


            <!-- Std Att Card  -->
            <?php 
             $query2 = mysqli_query($conn, "SELECT admissionNo, COUNT(*) FROM tblattendance 
             WHERE classId = '$_SESSION[classId]' AND status = '0'
             GROUP BY admissionNo HAVING COUNT(*) >= 5 "); 
             $abandon = mysqli_num_rows($query2);
             ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Abandons</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $abandon ;?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                      </div>
                    </div>
                    <div class="col-auto">
                    <i class="fa fa-cut fa-2x" style="color: red;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

        </div>

       
        <div class="container-fluid" id="container-wrapper">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
        </div>
        </div>

        <div class="row mb-3">
          <!-- New User Card Example -->
          <?php 
          $result = mysqli_query($conn,"SELECT SUM((salaire * 7) / 240) AS total_montant
          FROM tblstudents
          WHERE classId = '$_SESSION[classId]' ");
      
      $row = mysqli_fetch_assoc($result);
      $total = $row['total_montant'];
      $total = floor($total / 100) * 100;
      
          ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Montant prévu pour les heures supplementaires
                        </div>
                      <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo number_format($total, 0, ',', ' ')?>Fbu</div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                      </div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-money-bill fa-2x" style="color: blue;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <!-- Std Att Card  -->
            <?php 
            $result = mysqli_query($conn, "SELECT SUM(montant) AS total_montant 
            FROM tblsupp 
            WHERE classId = '$_SESSION[classId]' and DATE(dateTimeTaken) = CURDATE()");
  
            $row = mysqli_fetch_assoc($result);
            $payer = $row['total_montant'];
            $payer = floor($payer / 100) * 100;
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">A payer</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($payer, 0, ',', ' ')?>Fbu</div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>


             <!-- Std Att Card  -->
             <?php 
           $retour=$total-$payer;
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">A retourner</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($retour, 0, ',', ' ')?>Fbu</div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-money-bill-wave fa-2x text-secondary"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>



            <!-- Std Att Card  -->
            <?php 
           $query3=mysqli_query($conn,"SELECT * from tblsupp where montant = '0' 
           and classId = '$_SESSION[classId]' and DATE(dateTimeTaken) = CURDATE()");                       
           $carro = mysqli_num_rows($query3);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card h-100">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-uppercase mb-1">Carotés</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $carro ;?></div>
                      <div class="mt-2 mb-0 text-muted text-xs">
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fa fa-times fa-2x" style="color: red;"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>


        </div>


        <!---Container Fluid-->
      </div>
      <!-- Footer -->
      <?php include 'includes/footer.php';?>
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
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="js/demo/chart-area-demo.js"></script>  
</body>

</html>