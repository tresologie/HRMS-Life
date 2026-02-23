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
    <td colspan='3' style='text-align:left;'> Life Campony </td>
    <td colspan='3' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>
<tr style='font-weight:bold;'>
    <td colspan='3' style='text-align:left;'> Usine: ".$rrw['className']." </td>
</tr>
<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='5' style='text-decoration:underline; text-align:center;'>
     <h2>Liste des employes de l'usine </h2></td>
</tr>

</table>";
echo"
        <table border='1'>
        <thead>
            <tr>
            <th>#</th>
            <th>Nom & Prenom</th>
            <th>Identite</th>
            <th>Badge</th>
            <th>Poste</th>
            <th>Date</th>
            </tr>
        </thead>";

$filename="Tous les employes de ";
$dateCreated = date("Y-m-d");
$todaysDate = date("d-m-Y");

$cnt=1;			
$ret = mysqli_query($conn,"SELECT tblstudents.Id,tblstudents.dateCreated,
tblstudents.firstName,tblstudents.lastName,tblstudents.identite,tblstudents.admissionNumber,tblstudents.poste
 FROM tblstudents 
 INNER JOIN tblclass ON tblclass.Id = tblstudents.classId
 where tblstudents.classId = '$_SESSION[classId]'");

if(mysqli_num_rows($ret) > 0 )
{
 while ($row=mysqli_fetch_array($ret)) 
        { 
echo '  
<tr>  
<td>'.$cnt.'</td> 
<td>'.$row['firstName'].'  '.$row['lastName'].'</td> 
<td>'.$row['identite'].'</td> 
<td>'.$row['admissionNumber'].'</td> 
<td>'.$row['poste'].'</td>		 
<td>'.$row['dateCreated'].'</td>	 					
</tr>  
';
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=".$filename." .xls");
header("Pragma: no-cache");
header("Expires: 0");
			$cnt++;
			
	}
}
?>
</table>