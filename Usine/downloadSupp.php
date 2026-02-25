<?php
// Rien avant ça ! Pas d'espace ni echo
header("Content-type: application/vnd.ms-excel");
$filename = "Rapport_Heures";
$todaysDate = date("d-m-Y");
header("Content-Disposition: attachment; filename=".$filename." du ".$todaysDate.".xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../Includes/dbcon.php';
include '../Includes/session.php';



$query = "SELECT tblclass.className
FROM tblclassteacher
INNER JOIN tblclass ON tblclass.Id = tblclassteacher.classId
Where tblclassteacher.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
$num = $rs->num_rows;
$rrw = $rs->fetch_assoc();



// Date du jour
$todaysDate = date("d-m-Y");

echo "
<table>
<tr style='font-weight:bold;'>
    <td colspan='6' style='text-align:left;'> Life Campony </td>
    <td colspan='4' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>
<tr style='font-weight:bold;'>
    <td colspan='6' style='text-align:left;'> Usine: ".$rrw['className']." </td>
</tr>
<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='9' style='text-decoration:underline; text-align:center;'>
     <h2>Liste des heures supplementaires </h2></td>
</tr>

</table>";

// Tableau
echo "<table border='1'>
<thead>
<tr>
<th>#</th>
<th>Nom & Prenom</th>
<th>No. d'identite</th>
<th>Badge</th>
<th>Poste</th>
<th>Debut</th>
<th>Fin</th>
<th>Heures</th>
<th>Montant</th>
<th>Signature</th>
</tr>
</thead>";

$dateTaken = date("Y-m-d");
$cnt = 1;			

// Initialiser les totaux
$totalHeures = 0;
$totalMontant = 0;

$ret = mysqli_query($conn,"SELECT tblsupp.Id, tblsupp.dateTimeTaken, tblstudents.identite,
DATE_FORMAT(tblsupp.heureDebut, '%H:%i') AS heureDebut, 
DATE_FORMAT(tblsupp.heureFin, '%H:%i') AS heureFin,
tblsupp.heures, tblclass.className, FLOOR(tblsupp.montant / 100) * 100 AS montant,        
tblstudents.firstName, tblstudents.lastName, tblstudents.admissionNumber, tblstudents.poste
FROM tblsupp
INNER JOIN tblclass ON tblclass.Id = tblsupp.classId
INNER JOIN tblstudents ON tblstudents.admissionNumber = tblsupp.admissionNo
WHERE tblsupp.dateTimeTaken = '$dateTaken' 
AND tblsupp.classId = '".$_SESSION['classId']."'");

if(mysqli_num_rows($ret) > 0 )
{
    while ($row=mysqli_fetch_array($ret)) 
    { 
        echo "<tr>
        <td>".$cnt."</td>
        <td>".$row['firstName']."  ".$row['lastName']."</td>
        <td>".$row['identite']."</td>
        <td>".$row['admissionNumber']."</td>
        <td>".$row['poste']."</td>
        <td>".$row['heureDebut']."</td>
        <td>".$row['heureFin']."</td>
        <td>".$row['heures']."</td>
        <td style='font-weight:bold;'>".number_format($row['montant'], 0, ',', ' ')." Fbu</td>
        <td></td>
        </tr>"; 

        // Ajouter aux totaux

        $totalMontant += $row['montant'];

        $cnt++; // incrémenter après l'affichage
    }

    // Afficher la ligne total
    echo "
    <tr style='font-weight:bold;'>
        <td colspan='8'>Total</td>
        <td>".number_format($totalMontant, 0, ',', ' '). "Fbu</td>
        <td></td>
    </tr>";
}
// Récupérer le nom et prénom du class teacher correspondant au session_classId
$teacherQuery = mysqli_query($conn, "
    SELECT firstName, lastName 
    FROM tblclassteacher 
    WHERE classId = '".$_SESSION['classId']."'
");
$teacher = mysqli_fetch_assoc($teacherQuery);

// Ajouter une ligne sous le tableau avec le nom et prénom
if ($teacher) {
    echo "
    <table>
    <tr style='font-weight:bold;'>
        <td colspan='4' style='text-align:left;'>
            Chef d'usine : ".$teacher['firstName']." ".$teacher['lastName']."
        </td>
        <td colspan='6' style='text-align:right;'>Approuvee par l'assistant du Directeur General </td>
    </tr>
    </table>";
}


?>
