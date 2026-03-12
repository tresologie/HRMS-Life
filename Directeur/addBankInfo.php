<?php 
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

$statusMsg = "";
$mode = "add";
$editData = [];

// ----------------------- SUPPRESSION -----------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['Id'])) {
    $Id = (int)$_GET['Id'];
    $stmt = $conn->prepare("DELETE FROM tblBankInfo WHERE Id = ?");
    $stmt->bind_param("i", $Id);
    if ($stmt->execute()) {
        header("Location: addBankInfo.php?msg=deleted");
        exit;
    } else {
        $statusMsg = "<div class='alert alert-danger'>Erreur lors de la suppression</div>";
    }
    $stmt->close();
}

// ----------------------- ÉDITION -----------------------
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['Id'])) {
    $mode = "edit";
    $Id = (int)$_GET['Id'];
    
    $stmt = $conn->prepare("
        SELECT b.*, s.firstName, s.lastName, s.admissionNumber, s.identite AS student_identite
        FROM tblBankInfo b
        INNER JOIN tblemployees s ON s.admissionNumber = b.admissionNo
        WHERE b.Id = ?
    ");
    $stmt->bind_param("i", $Id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

// ----------------------- TRAITEMENT FORMULAIRE -----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $admissionNo = trim($_POST['admissionNo'] ?? '');
    $identite    = trim($_POST['identite'] ?? '');
    $bankName    = trim($_POST['bankName'] ?? '');
    $bankNumber  = trim($_POST['bankNumber'] ?? '');

    if (empty($admissionNo) || empty($identite) || empty($bankName) || empty($bankNumber)) {
        $statusMsg = "<div class='alert alert-danger'>Tous les champs sont obligatoires.</div>";
    }
    else {
        if ($mode === "edit") {
            // Mise à jour
            $Id = (int)$_POST['Id'];
            $stmt = $conn->prepare("
                UPDATE tblBankInfo 
                SET identite = ?, bankName = ?, bankNumber = ?
                WHERE Id = ?
            ");
            $stmt->bind_param("sssi", $identite, $bankName, $bankNumber, $Id);
            $successText = "Informations bancaires modifiées avec succès !";
        } 
        else {
            // Vérifier doublon
            $check = $conn->prepare("SELECT Id FROM tblBankInfo WHERE admissionNo = ?");
            $check->bind_param("s", $admissionNo);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $statusMsg = "<div class='alert alert-warning'>Cet employé possède déjà des informations bancaires.</div>";
                $check->close();
            } 
            else {
                $check->close();
                
                // Insertion
                $stmt = $conn->prepare("
                    INSERT INTO tblBankInfo 
                    (admissionNo, identite, bankName, bankNumber, addedBy) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $userId = $_SESSION['userId'] ?? 0;
                $stmt->bind_param("ssssi", $admissionNo, $identite, $bankName, $bankNumber, $userId);
                $successText = "Informations bancaires enregistrées avec succès !";
            }
        }

        if (isset($stmt) && $stmt->execute()) {
            $statusMsg = "<div class='alert alert-success'>$successText</div>";
        } 
        elseif (isset($stmt)) {
            $statusMsg = "<div class='alert alert-danger'>Erreur : " . $conn->error . "</div>";
        }
        
        if (isset($stmt)) $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="img/logo/life.jpg" rel="icon">
  <title>Gestion Informations Bancaires</title>
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

        <div class="container-fluid" id="container-wrapper">
          
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <h6 class="font-weight-bold text-primary">
              <?php echo ($mode === "edit") ? "Modifier informations bancaires" : "Informations bancaires"; ?>
            </h6>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="./">Accueil</a></li>
              <li class="breadcrumb-item active">Banque employés</li>
            </ol>
          </div>

          <?php echo $statusMsg; ?>

          <div class="row">
            <div class="col-lg-12">

              <!-- Formulaire -->
              <div class="card mb-4">
                <div class="card-body">
                  <form method="post">
                    <input type="hidden" name="Id" value="<?php echo $editData['Id'] ?? ''; ?>">

                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Employé <span class="text-danger">*</span></label>
                        <?php
                        $qry = "SELECT admissionNumber, firstName, lastName, identite 
                                FROM tblemployees 
                                ORDER BY firstName ASC, lastName ASC";
                        $result = $conn->query($qry);

                        echo '<select name="admissionNo" class="form-control" required ' . (($mode === "edit") ? 'disabled' : '') . '>';
                        echo '<option value="">-- Sélectionner un employé --</option>';

                        while ($emp = $result->fetch_assoc()) {
                            $selected = (isset($editData['admissionNo']) && $editData['admissionNo'] === $emp['admissionNumber']) ? 'selected' : '';
                            echo "<option value='{$emp['admissionNumber']}' $selected>";
                            echo htmlspecialchars("{$emp['firstName']} {$emp['lastName']} ({$emp['admissionNumber']})");
                            echo "</option>";
                        }
                        echo '</select>';

                        if ($mode === "edit") {
                            echo '<input type="hidden" name="admissionNo" value="' . htmlspecialchars($editData['admissionNo'] ?? '') . '">';
                        }
                        ?>
                      </div>

                      <div class="col-xl-6">
                        <label class="form-control-label">Numéro d’identité <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="identite" required
                               value="<?php echo htmlspecialchars($editData['identite'] ?? $editData['student_identite'] ?? ''); ?>">
                      </div>
                    </div>

                    <div class="form-group row mb-3">
                      <div class="col-xl-6">
                        <label class="form-control-label">Nom de la banque <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bankName" required
                               value="<?php echo htmlspecialchars($editData['bankName'] ?? ''); ?>">
                      </div>
                      <div class="col-xl-6">
                        <label class="form-control-label">Numéro de compte <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bankNumber" required
                               value="<?php echo htmlspecialchars($editData['bankNumber'] ?? ''); ?>">
                      </div>
                    </div>

                    <?php if ($mode === "edit"): ?>
                      <button type="submit" class="btn btn-warning">Modifier</button>
                      <a href="addBankInfo.php" class="btn btn-secondary">Annuler</a>
                    <?php else: ?>
                      <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <?php endif; ?>
                  </form>
                </div>
              </div>

              <!-- Liste -->
              <div class="card">
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
                          <th>Date ajout</th>
                          <th><i class='fas fa-edit'></i></th>
                          <th><i class='fas fa-trash'></i></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $query = "
                            SELECT b.Id, b.identite, b.bankName, b.bankNumber, b.dateAdded,
                                   s.firstName, s.lastName, s.admissionNumber
                            FROM tblBankInfo b
                            INNER JOIN tblemployees s ON s.admissionNumber = b.admissionNo
                            ORDER BY s.firstName ASC
                        ";
                        $rs = $conn->query($query);
                        $sn = 0;

                        if ($rs->num_rows > 0) {
                            while ($row = $rs->fetch_assoc()) {
                                $sn++;
                                echo "
                                <tr>
                                  <td>$sn</td>
                                  <td>{$row['firstName']} {$row['lastName']}
                                  </td>
                                  <td>{$row['identite']}</td>
                                  <td>{$row['bankName']}</td>
                                  <td>{$row['bankNumber']}</td>
                                  <td>" . date('d/m/Y', strtotime($row['dateAdded'])) . "</td>
                                  <td>
                                    <a href='?action=edit&Id={$row['Id']}' title='Modifier'>
                                      <i class='fas fa-edit'></i>
                                    </a>
                                    </td><td>
                                    <a href='?action=delete&Id={$row['Id']}' title='Supprimer'
                                       onclick='return confirm(\"Confirmer la suppression ?\");'>
                                      <i class='fas fa-trash'></i>
                                    </a>
                                  </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-4'>Aucune donnée enregistrée</td></tr>";
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
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
    });
});
</script>
</body>
</html>

<?php $conn->close(); ?>