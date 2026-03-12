<?php 
error_reporting(0);
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');


$query = "SELECT tblservice.serviceName
FROM tblchef
INNER JOIN tblservice ON tblservice.Id = tblchef.classId
Where tblchef.Id = '$_SESSION[userId]'";

$rs = $conn->query($query);
$num = $rs->num_rows;
$rrw = $rs->fetch_assoc();
$todaysDate = date("d-m-Y");

echo "
<table>
<tr style='font-weight:bold;'>
    <td colspan='3' style='text-align:left;'> Life Campony </td>
    <td colspan='3' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>
<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='5' style='text-decoration:underline;'>
     <h2>Liste de tous les usines et chefs de Life campony </h2></td>
</tr>

</table>";
?>
        <table border="1">
        <thead>
            <tr>
            <th>#</th>
            <th>Nom & Prenom</th>
            <th>Email</th>
            <th>Tel</th>
            <th>Usine</th>
            <th>Date</th>
            </tr>
        </thead>

<?php 
$filename="Liste des usines et chefs de Life campony";
$dateCreated = date("Y-m-d");

$cnt=1;			
$ret = mysqli_query($conn,"SELECT tblchef.firstName,tblchef.lastName,
tblchef.emailAddress,tblchef.phoneNo,tblchef.dateCreated,tblservice.serviceName
 FROM tblchef
 INNER JOIN tblservice ON tblservice.Id = tblchef.classId ");

if(mysqli_num_rows($ret) > 0 )
{
 while ($row=mysqli_fetch_array($ret)) 
        { 
echo '  
<tr>  
<td>'.$cnt.'</td> 
<td>'.$row['firstName'].'  '.$row['lastName'].'</td>  
<td>'.$row['emailAddress'].'</td> 
<td>'.$row['phoneNo'].'</td>
<td>'.$row['serviceName'].'</td>  
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