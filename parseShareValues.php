<?php
/**
 * User: dacappo
 * Date: 18.11.13
 * Time: 14:09
 */

$start = microtime(true);

// Include the library
include('simple_html_dom.php');
include('db_connection.php');

// Attributes
$ISINs = array();
$Names = array();
$Values = array();

// Retrieve the DOM from a given URL
$html = file_get_html('http://www.finanzen.net/index/Dax');

foreach($html->find('div.double_row_performance') as $e) {

    foreach($e->find('tr') as $tr) {
        $aktie = $tr->find('td');
        if (sizeof($aktie) > 0) {
            $num = explode("\n",$aktie[0]->plaintext);
            array_push($ISINs, $num[1]);

            if (substr($num[0],-2,1) == "N") {
                array_push($Names, substr($num[0],0,strlen($num[0])-3));
            } else {
                array_push($Names, $num[0]);
            }

            $num = explode("\n",$aktie[1]->plaintext);
            array_push($Values, $num[0]);

        }
    }

}

// Write to DB

$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$end = microtime(true);


for($i = 0; $i < sizeof($ISINs) && $i < sizeof($Names) && $i < sizeof($Values); $i++) {
    $queryForShare = "INSERT INTO Shares VALUES ('" . substr($ISINs[$i],0,12) . "', '" . $Names[$i] . "') ON DUPLICATE KEY UPDATE ISIN = ISIN";
    $queryForValue ="INSERT INTO ShareValues VALUES ('" . substr($ISINs[$i],0,12) . "', CURRENT_TIMESTAMP, " . str_replace(",",".",$Values[$i]) . ")";
    echo $query;

    if (mysqli_query($con,$queryForShare)){
        echo "Query ran successfully: " . $queryForShare . "<br>";
    } else {
        echo "Error running query: " . mysqli_error($con) . " : " . $queryForValue . "<br>";
    }

    if (mysqli_query($con,$queryForValue)){
        echo "Query ran successfully: " . $queryForValue . "<br>";
    } else {
        echo "Error running query: " . mysqli_error($con) . " : " . $queryForValue . "<br>";
    }
}

mysqli_close($con);

$laufzeit = $end - $start;
echo "<div style='background-color:black; color:white;' >Runtime: ".$laufzeit." seconds!</div>";

?>