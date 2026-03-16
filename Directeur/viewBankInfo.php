<?php
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
  <link href="img/logo/life.jpg" rel="icon">
  <title>Visualisation des informations bancaires</title>

  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="css/ruang-admin.min.css" rel="stylesheet">
</head>

<body id="page-top">
  <div id="wrapper">

    <?php include "Includes/sidebar.php"; ?>

    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">

        <?php include "Includes/topbar.php"; ?>


        <div class="d-sm-flex align-items-center justify-content-between">
          <h6 class="font-weight-bold text-primary" style="margin-left:30px">Liste des comptes bancaires</h6>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="downloadSuppl.php?from=<?php echo $fromDate; ?>
              &to=<?php echo $toDate; ?>">Exporter</a>(Exel)</li>
            <li class="breadcrumb-item"><a href="printBankInfo.php">Imprimer</a>(PDF)</li>

          </ol>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Accueil</a></li>
            <li class="breadcrumb-item active">Visualisation Banque</li>
          </ol>
        </div>

        <div class="container-fluid" id="container-wrapper">

          <!-- Carte principale -->
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="table-responsive p-3">
                  <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                    <thead class="thead-light">
                      <tr>
                        <th>#</th>
                        <th>Employé</th>
                        <th>Identité</th>
                        <th>Banque</th>
                        <th>N° Compte</th>
                        <th>Date</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php
                      $query = "
                        SELECT 
                            b.Id,
                            b.identite,
                            b.bankName,
                            b.bankNumber,
                            b.dateAdded,
                            s.firstName,
                            s.lastName,
                            s.admissionNumber
                        FROM tblBankInfo b
                        INNER JOIN tblemployees s ON s.admissionNumber = b.admissionNo
                        ORDER BY s.firstName ASC, s.lastName ASC
                    ";

                      $rs = $conn->query($query);
                      $sn = 0;

                      if ($rs->num_rows > 0) {
                        while ($row = $rs->fetch_assoc()) {
                          $sn++;
                          $date = date('d/m/Y', strtotime($row['dateAdded']));

                          echo "
                            <tr>
                                <td>$sn</td>
                                <td>
                                    <strong>{$row['firstName']} {$row['lastName']}</strong>
                                </td>
                                <td>{$row['identite']}</td>
                                <td>{$row['bankName']}</td>
                                <td>{$row['bankNumber']}</td>
                                <td>$date</td>
                            </tr>";
                        }
                      } else {
                        echo "
                        <tr>
                            <td colspan='7' class='text-center py-5 text-muted'>
                                <i class='fas fa-info-circle fa-2x mb-3 d-block'></i>
                                Aucune information bancaire enregistrée pour le moment
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

  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Scripts -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/ruang-admin.min.js"></script>

  <!-- DataTables -->
  <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#dataTableHover').DataTable({
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
      });
    });
  </script>

</body>

</html>

<?php
$conn->close();
?>