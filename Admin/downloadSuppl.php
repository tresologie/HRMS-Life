<?php
include '../Includes/dbcon.php';

// ===== Définir la période par défaut =====
date_default_timezone_set('Africa/Bujumbura');
$toDate   = date('Y-m-d');                  // aujourd'hui
$fromDate = date('Y-m-d', strtotime('-6 days')); // 7 derniers jours

// ===== Si GET est fourni, on l'utilise =====
if(isset($_GET['fromDate']) && isset($_GET['toDate'])){
    $fromDate = $_GET['fromDate'];
    $toDate   = $_GET['toDate'];

    // Limiter à 7 jours max
    $diff = (strtotime($toDate) - strtotime($fromDate)) / (60*60*24) + 1;
    if($diff > 7){
        $fromDate = date('Y-m-d', strtotime($toDate. ' - 6 days'));
    }
}

// ===== Headers Excel =====
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Heures_supplementaires.xls");
header("Pragma: no-cache");
header("Expires: 0");

// ===== Requête =====
$query = "SELECT 
    tblemployees.admissionNumber,
    tblemployees.firstName,
    tblemployees.lastName,
    tblemployees.identite,
    tblemployees.poste,
    tblservice.serviceName,
    DATE(tblsupp.dateTimeTaken) as dateTaken,
    FLOOR(tblsupp.montant / 100) * 100 AS montant
FROM tblsupp
INNER JOIN tblservice ON tblservice.Id = tblsupp.classId
INNER JOIN tblemployees ON tblemployees.admissionNumber = tblsupp.admissionNo
WHERE DATE(tblsupp.dateTimeTaken) 
BETWEEN '$fromDate' AND '$toDate'
ORDER BY tblservice.serviceName, tblemployees.firstName ASC";

$rs = $conn->query($query);

// ===== Préparer données =====
$dates = [];
$data  = [];
$totalGeneral = 0;
$cnt = 1;

while ($row = $rs->fetch_assoc()) {

    $emp   = $row['admissionNumber'];
    $date  = $row['dateTaken'];
    $montant= $row['montant'];

    $dates[$date] = $date;

    $data[$emp]['name']  = $row['firstName'].' '.$row['lastName'];
    $data[$emp]['identite'] = $row['identite'];
    $data[$emp]['badge'] = $row['admissionNumber'];
    $data[$emp]['usine'] = $row['serviceName'];
    $data[$emp]['poste'] = $row['poste'];

    $data[$emp]['values'][$date] = $montant;
}

ksort($dates);
$dates = array_slice($dates, 0, 7, true); // limiter à 7 dates max

$todaysDate = date("d-m-Y");

// ===== Table Excel =====
echo "
<table>
<tr style='font-weight:bold;'>
    <td colspan='4' style='text-align:left;'> Life Campony </td>
    <td colspan='4' style='text-align:right;'>Le ".$todaysDate."</td>
</tr>

<tr style='font-weight:bold;'>
    <td></td>
    <td colspan='8' style='text-decoration:underline;'>
     <h2>Heures supplementaires de cette semaine</h2></td>
</tr>
</table>";

echo "<table border='1'>";
echo "<tr>
<th>#</th>
<th>Nom & Prenom</th>
<th>Identite</th>
<th>Badge</th>
<th>Usine</th>
<th>Poste</th>";

foreach($dates as $date){
    echo "<th>".date('d/m', strtotime($date))."</th>";
}

echo "<th>TOTAL</th>";
echo "<th>Signature</th>";
echo "</tr>";

// ===== Lignes par employé =====
foreach($data as $emp => $info){

    $totalEmp = 0;
    echo "<tr>";
    echo "<td>".$cnt."</td>";
    echo "<td>".$info['name']."</td>";
    echo "<td>".$info['identite']."</td>";
    echo "<td>".$info['badge']."</td>";
    echo "<td>".$info['usine']."</td>";
    echo "<td>".$info['poste']."</td>";

    foreach($dates as $date){
        $value = isset($info['values'][$date]) ? $info['values'][$date] : 0;
        $totalEmp += $value;
        echo "<td>".number_format($value,0,',',' ')." Fbu</td>";
    }

    echo "<td ><b>".number_format($totalEmp,0,',',' ')." Fbu</b></td>";
    echo "<td></td>";
    echo "</tr>";

    $totalGeneral += $totalEmp;
    $cnt++;
}

// ===== Total général =====
echo "<tr>
<td colspan='".(6+count($dates))."' style='text-align:right;font-weight:bold;'>TOTAL GENERAL</td>
<td style='font-weight:bold;'>".number_format($totalGeneral,0,',',' ')." Fbu</td>
</tr>";

echo "</table>";
?>