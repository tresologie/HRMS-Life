<?php
error_reporting(E_ALL);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

// Récupérer les dates distinctes d'enregistrement (DESC = plus récent en haut)
$datesResult = $conn->query("
    SELECT DISTINCT DATE(date_validation) AS dateOnly 
    FROM tblsalaire_resume 
    ORDER BY dateOnly DESC
");

$availableDates = [];
while ($row = $datesResult->fetch_assoc()) {
    $availableDates[] = $row['dateOnly'];
}

// Date par défaut : la plus récente (ou vide si rien)
$selectedDate = $availableDates[0] ?? '';

// Si formulaire soumis
if (isset($_POST['view']) && !empty($_POST['selectedDate'])) {
    $selectedDate = $_POST['selectedDate'];
}

// Sécurisation
$selectedDateEsc = $conn->real_escape_string($selectedDate);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="img/logo/life.jpg" rel="icon">
    <title>Historique des Salaires</title>
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

                <div class="d-sm-flex align-items-center justify-content-between">
                    <h6 class="font-weight-bold text-primary" style="margin-left:30px">
                        Historique des salaires validés
                    </h6>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="downloadHistoriSalaire.php">Exporter</a>(Excel)</li>
                        <li class="breadcrumb-item"><a href="printHistoriSalaire.php">Imprimer</a>(PDF)</li>
                    </ol>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="./">Accueil</a></li>
                        <li class="breadcrumb-item active">Historique Salaires</li>
                    </ol>
                </div>

                <div class="container-fluid" id="container-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group row mb-3">
                                            <div class="col-xl-4">
                                                <label>Selectionner la date de validation <span class="text-danger">*</span></label>
                                                <select name="selectedDate" id="selectedDate" class="form-control" required>
                                                    <?php foreach ($availableDates as $date): ?>
                                                        <option value="<?= $date ?>" <?= $date == $selectedDate ? 'selected' : '' ?>>
                                                            <?= date("d/m/Y", strtotime($date)) ?>
                                                        </option>
                                                    <?php endforeach; ?>

                                                    <?php if (empty($availableDates)): ?>
                                                        <option value="">Aucune validation enregistrée</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" name="view" class="btn btn-primary">Afficher</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Salaires validés le <?= $selectedDate ? date("d/m/Y", strtotime($selectedDate)) : '—' ?>
                                    </h6>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive p-3">
                                        <table class="table table-bordered table-hover table-sm" id="dataTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nom & Prénom</th>
                                                    <th>Prés</th>
                                                    <th>Abs</th>
                                                    <th>Salaire de base</th>
                                                    <th> Salaire recevable</th>
                                                    <th>N° de Compte</th>
                                                    <th>Banque/MF</th>
                                                    <th>Validé le</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($selectedDate)) {
                                                    $query = "
                                                    SELECT 
                                                        id,
                                                        admissionNumber,
                                                        nom,
                                                        prenom,
                                                        identite,
                                                        presences,
                                                        absences,
                                                        salaire_base,
                                                        salaire_receivable,
                                                        compte_bancaire,
                                                        banque,
                                                        date_validation
                                                    FROM tblsalaire_resume
                                                    WHERE DATE(date_validation) = '$selectedDateEsc'
                                                    ORDER BY nom ASC, prenom ASC
                                                ";

                                                    $rs = $conn->query($query);
                                                    $num = $rs->num_rows;
                                                    $sn = 0;

                                                    if ($num > 0) {
                                                        while ($row = $rs->fetch_assoc()) {
                                                            $sn++;
                                                ?>
                                                            <tr>
                                                                <td><?= $sn ?></td>
                                                                <td>
                                                                    <b><?= htmlspecialchars($row['prenom'] . ' ' . $row['nom']) ?></b><br>
                                                                    <small><?= htmlspecialchars($row['identite'] ?: '-') ?></small>
                                                                </td>
                                                                <td class="text-center text-success"><?= $row['presences'] ?></td>
                                                                <td class="text-center text-danger"><?= $row['absences'] ?></td>
                                                                <td class="text-right"><?= number_format($row['salaire_base'], 0, ',', ' ') ?> Fbu</td>
                                                                <td class="text-right font-weight-bold"><?= number_format($row['salaire_receivable'], 0, ',', ' ') ?> Fbu</td>
                                                                <td class="font-monospace text-center"><?= htmlspecialchars($row['compte_bancaire'] ?: '-') ?></td>
                                                                <td><?= htmlspecialchars($row['banque'] ?: '-') ?></td>
                                                                <td><?= date("d/m/Y H:i", strtotime($row['date_validation'])) ?></td>
                                                            </tr>
                                                <?php
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='10' class='text-center py-4 text-muted'>Aucun salaire validé à cette date</td></tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='10' class='text-center py-4 text-muted'>Sélectionnez une date pour voir les enregistrements</td></tr>";
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

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>

    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                }
            });
        });
    </script>
</body>

</html>