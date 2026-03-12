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



$query = "SELECT tblservice.serviceName
FROM tblchef
INNER JOIN tblservice ON tblservice.Id = tblchef.classId
Where tblchef.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
$num = $rs->num_rows;
$rrw = $rs->fetch_assoc();



// Date du jour
date_default_timezone_set('Africa/Bujumbura');
$todaysDate = date("d-m-Y");

echo "
<table>
<tr style='font-weight:bold;'>
    <td colspan='3' style='text-align:left;'> Life Campony </td>
    <td colspan='3' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>
<tr style='font-weight:bold;'>
    <td colspan='3' style='text-align:left;'> Usine: ".$rrw['serviceName']." </td>
</tr>
<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='5' style='text-decoration:underline; text-align:center;'>
     <h2>Liste des presences </h2></td>
</tr>
</table>";
echo "
        <table border=1'>
        <thead>
            <tr>
            <th>#</th>
            <th>Nom & Prenom</th>
            <th>Badge</th>
            <th>Poste</th>
            <th>Date</th>
            <th>Status</th>
            </tr>
        </thead>";

$filename="Liste de Présences";
$dateTaken = date("Y-m-d");

$cnt=1;			
$ret = mysqli_query($conn,"SELECT tblattendance.Id,tblattendance.status,tblattendance.dateTimeTaken,tblservice.serviceName,
        tblemployees.firstName,tblemployees.lastName,tblemployees.admissionNumber,tblemployees.poste
        FROM tblattendance
        INNER JOIN tblservice ON tblservice.Id = tblattendance.classId

        INNER JOIN tblemployees ON tblemployees.admissionNumber = tblattendance.admissionNo
        where tblattendance.dateTimeTaken = '$dateTaken' and tblattendance.classId = '$_SESSION[classId]' 
        ORDER BY tblemployees.firstName ASC");

if(mysqli_num_rows($ret) > 0 )
{
while ($row=mysqli_fetch_array($ret)) 
{ 
    
    if($row['status'] == '1'){$status = "Present"; $colour="#00FF00";}else{$status = "Absent";$colour="#FF0000";}

echo '  
<tr>  
<td>'.$cnt.'</td> 
<td>'.$row['firstName'].'  '.$row['lastName'].'</td>
<td>'.$row['admissionNumber'].'</td> 
<td>'.$row['poste'].'</td> 
<td>'.$row['dateTimeTaken'].'</td>	
<td>'.$status.'</td>	 	 					
</tr>  
';
header("Content-type:application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=".$filename." du ".$todaysDate.".xls");
header("Pragma: no-cache");
header("Expires: 0");
			$cnt++;
			}
	}
?>
</table>