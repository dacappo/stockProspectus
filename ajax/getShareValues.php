<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 25.12.13
 * Time: 15:34
 */

header('Content-Type: application/json');

// Include the library
include('../databaseConnection.php');

$index = $_GET["index"];

// Attributes
$ISINs = array();
$Names = array();
$Values = array();


$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
if ($index == "dax") {
    $query = "SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.Timestamp, Shares.Currency FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = 'DAX' ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name";
} else if ($index == "dj") {
    $query = "SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.Timestamp, Shares.Currency FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = 'DJ' ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name";
} else {

}

$result = mysqli_query($con,$query);
mysqli_close($con);

$timeStamp = "Something went wrong!";


$dataForJson = array();

while($row = mysqli_fetch_array($result)){
    $timeStamp = $row['Timestamp'];
    $share = array("Name"=>str_replace('&amp;','&',$row['Name']), "ISIN"=>$row['ISIN'], "Value"=>$row['Value'], "Currency"=>$row['Currency']);
    array_push($dataForJson, $share);
}

echo '{ "shares" : ' . json_encode($dataForJson) . ', "Timestamp" : "' . $timeStamp . '"}';