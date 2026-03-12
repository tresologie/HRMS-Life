<?php
include '../Includes/dbcon.php';
include '../Includes/session.php';

date_default_timezone_set('Africa/Bujumbura');

if(isset($_GET['fromDate']) && isset($_GET['toDate'])){
    $fromDate = $_GET['fromDate'];
    $toDate   = $_GET['toDate'];
} else {
    die("Periode invalide !");
}

// Headers Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Liste de presences.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Requête
$query = "SELECT 
    tblemployees.admissionNumber,
    tblemployees.firstName,
    tblemployees.lastName,
    tblemployees.poste,
    tblservice.serviceName,
    DATE(tblattendance.dateTimeTaken) as dateTaken,
    tblattendance.status
FROM tblattendance
INNER JOIN tblservice ON tblservice.Id = tblattendance.classId
INNER JOIN tblemployees ON tblemployees.admissionNumber = tblattendance.admissionNo
WHERE DATE(tblattendance.dateTimeTaken) 
BETWEEN '$fromDate' AND '$toDate'
ORDER BY tblservice.serviceName, tblemployees.firstName ASC";

$rs = $conn->query($query);

// Préparer données
$dates = [];
$data  = [];

while ($row = $rs->fetch_assoc()) {

    $emp   = $row['admissionNumber'];
    $date  = $row['dateTaken'];
    $status= $row['status'];

    $dates[$date] = $date;

    $data[$emp]['name']  = $row['firstName'].' '.$row['lastName'];
    $data[$emp]['badge'] = $row['admissionNumber'];
    $data[$emp]['usine'] = $row['serviceName'];
    $data[$emp]['poste'] = $row['poste'];

    $data[$emp]['values'][$date] = ($status == 1) ? 'P' : 'A';
    $todaysDate = date("d-m-Y");
    $cnt=1;
}

ksort($dates);

// ===== TABLE EXCEL =====
echo "
<table>
<tr style='font-weight:bold;'>
    <td colspan='4' style='text-align:left;'> Life Campony </td>
    <td colspan='4' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>

<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='8' style='text-decoration:underline;'>
     <h2>Liste de Presences du ".$fromDate." au ".$toDate." </h2></td>
</tr>

</table>";
echo "<table border='1'>";

echo "<tr>
<th>#</th>
<th>Nom & Prenom</th>
<th>Badge</th>
<th>Usine</th>
<th>Poste</th>";

foreach($dates as $date){
    echo "<th>".date('d/m', strtotime($date))."</th>";
}

echo "<th>TOTAL</th>";
echo "</tr>";

foreach($data as $emp => $info){

    $totalP = 0;

    echo "<tr>";
    echo "<td>".$cnt."</td>";
    echo "<td>".$info['name']."</td>";
    echo "<td>".$info['badge']."</td>";
    echo "<td>".$info['usine']."</td>";
    echo "<td>".$info['poste']."</td>";

    foreach($dates as $date){

        $value = isset($info['values'][$date]) ? $info['values'][$date] : '';

        if($value == 'P'){
            $totalP++;
        }

        echo "<td align='center'>".$value."</td>";
    }

    echo "<td align='center'><b>".$totalP."</b></td>";
    echo "</tr>";
    $cnt++;
}

echo "</table>";
?>