
<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

//------------------------SAVE--------------------------------------------------

if(isset($_POST['save'])){
    
    $serviceName=$_POST['serviceName'];
    
   
    $query=mysqli_query($conn,"select * from tblservice where serviceName ='$serviceName'");
    $ret=mysqli_fetch_array($query);

    if($ret > 0){ 

        $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Cette usine existe déjà!</div>";
    }
    else{

        $query=mysqli_query($conn,"insert into tblservice(serviceName) value('$serviceName')");

    if ($query) {
        
        $statusMsg = "<div class='alert alert-success'  style='margin-right:700px;'>Usine ajouté avec succès!</div>";
    }
    else
    {
         $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;>Erreur!</div>";
    }
  }
}

//---------------------------------------EDIT-------------------------------------------------------------






//--------------------EDIT------------------------------------------------------------

 if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "edit")
	{
        $Id= $_GET['Id'];

        $query=mysqli_query($conn,"select * from tblservice where Id ='$Id'");
        $row=mysqli_fetch_array($query);

        //------------UPDATE-----------------------------

        if(isset($_POST['update'])){
    
            $serviceName=$_POST['serviceName'];
        
            $query=mysqli_query($conn,"update tblservice set serviceName='$serviceName' where Id='$Id'");

            if ($query) {
                
                echo "<script type = \"text/javascript\">
                window.location = (\"createClass.php\")
                </script>"; 
            }
            else
            {
                $statusMsg = "<div class='alert alert-danger' style='margin-right:700px;'>Erreur!</div>";
            }
        }
    }


//--------------------------------DELETE------------------------------------------------------------------

  if (isset($_GET['Id']) && isset($_GET['action']) && $_GET['action'] == "delete")
	{
        $Id= $_GET['Id'];

        $query = mysqli_query($conn,"DELETE FROM tblservice WHERE Id='$Id'");

        if ($query == TRUE) {

                echo "<script type = \"text/javascript\">
                window.location = (\"createClass.php\")
                </script>";  
        }
        else{

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
<?php include 'includes/title.php';?>
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


        <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h6 class="font-weight-bold text-primary" style="margin-left:30px">Ajouter une usine</h6>

            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="downloadUsines.php">Exporter</a>(Exel)</li>
              <li class="breadcrumb-item"><a href="#">Imprimer</a>(PDF)</li>
              
            </ol>

            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active" aria-current="page">Ajouter une usine</li>
            </ol>
          </div>
        
            <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
       

       <div class="row">
         <div class="col-lg-12">
           <!-- Form Basic -->
          
           <div class="card mb-4 p-3">
                  <form method="post">
                    <div class="form-group row mb-3">
                        <div class="col-xl-6">
                            <label class="form-control-label">Usine<span class="text-danger ml-2">*</span></label>
                      <input type="text" class="form-control" name="serviceName" value="<?php echo $row['serviceName'];?>" id="exampleInputFirstName" placeholder="Nom d'usine">
                        </div>
                    </div>
                      <?php
                    if (isset($Id))
                    {
                    ?>
                    <button type="submit" name="update" class="btn btn-warning">Modifier</button>
                    <a href="createClass.php" class="btn btn-secondary">Annuler</a>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
              </div>
        
            <!-- Input Group -->
               <div class="row">
            <div class="col-lg-12">
            <div class="card mb-4">
              <div class="table-responsive p-3">
              <?php echo $statusMsg; ?>
                <h6 class="font-weight-bold text-primary">Tous les Usines et leurs chefs</h6>
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Usine</th>
                        <th>Chef</th>
                        <th>Editer</th>
                        <th>Supprimer</th>
                      </tr>
                    </thead>
                  
                    <tbody>

                  <?php
                      $query = "SELECT tblchef.Id,tblservice.serviceName,tblchef.firstName,
                      tblchef.lastName,tblchef.dateCreated
                      FROM tblchef
                      INNER JOIN tblservice ON tblservice.Id = tblchef.classId";
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
                                <td>".$rows['serviceName']."</td>
                                <td>".$rows['firstName'].'  '.$rows['lastName']."</td>
                                <td><a href='?action=edit&Id=".$rows['Id']."'><i class='fas fa-fw fa-edit'></i></a></td>
                                <td><a href='?action=delete&Id=".$rows['Id']."'><i class='fas fa-fw fa-trash'></i></a></td>
                              </tr>";
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