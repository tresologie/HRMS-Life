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

$todaysDate = date("d-m-Y");

echo "
<table>
<tr style='font-weight:bold;'>
    <td colspan='4' style='text-align:left;'> Life Campony </td>
    <td colspan='4' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>

<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='8' style='text-decoration:underline;'>
     <h2>Liste de tous les employes de Life campony </h2></td>
</tr>

</table>";
?>
        <table border="1">
        <thead>
            <tr>
            <th>#</th>
            <th>Nom & Prenom</th>
            <th>Identite</th>
            <th>Badge</th>
            <th>Usine</th>
            <th>Poste</th>
            <th>Salaire</th>
            <th>Date</th>
            </tr>
        </thead>

<?php 
$filename="Liste de tous les employes de Life campony";
$dateCreated = date("Y-m-d");

$cnt=1;			
$ret = mysqli_query($conn,"SELECT tblstudents.Id,tblstudents.dateCreated, tblclass.className, tblstudents.salaire,
tblstudents.firstName,tblstudents.lastName,tblstudents.identite,tblstudents.admissionNumber,tblstudents.poste
 FROM tblstudents 
 INNER JOIN tblclass ON tblclass.Id = tblstudents.classId 
 ORDER BY tblstudents.firstName ASC");

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
<td>'.$row['className'].'</td> 
<td>'.$row['poste'].'</td>	
<td>'.$row['salaire'].'Fbu</td>	 
<td>'.$row['dateCreated'].'</td>	 					
</tr>  
';
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=".$filename.".xls");
header("Pragma: no-cache");
header("Expires: 0");
			$cnt++;
			
	}
}
?>
</table>