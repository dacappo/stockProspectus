<!DOCTYPE html>
<html>
<head>
	<title>Aktienindex</title>
	<style type=text/css>
		html {
			color: #6678b1;
			font-family: Segoe UI Light;
		}
		
		table {
			width:100%;
			
			font-weight: 300;
			text-align:left;
			margin-bottom: 30px;
		}
		
		tr:hover {
			background-color:#f2f8ff;
		}
		
		th:hover {
			background-color:#ffffff;
		}
		
		th {
			padding: 5px;
			color: #6678b1;
			border-bottom: 2px solid #6678b1;
			font-weight: 300;
		}
		
		td {
			font-size: 14px;
			padding: 5px;
			border-bottom: 1px solid #DDDDDD;
		}
		
		td.price {
			text-align: right;
		}
		
		.statistic {
			position: fixed;
			bottom: 0px;
			right: 0px;
			left: 0px;
			font-size:12px;
			color:#FFFFFF;
			background-color: #6678b1;
			padding: 5px;
		}
	</style>

</head>
<body>
<table>
	<tr><th>ISIN</th><th>Name</th><th>Value</th></tr>

<?php
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
		echo '<tr>';
		$aktie = $tr->find('td');
		if (sizeof($aktie) > 0) {
			echo '<td>';
			//echo $aktie[0]->find('a',0)->plaintext;
            $num = explode("\n",$aktie[0]->plaintext);
            echo $num[1];
            array_push($ISINs, $num[1]);
            echo '</td>';
            echo '<td>';

            if (substr($num[0],-2,1) == "N") {
                echo substr($num[0],0,strlen($num[0])-3);
                array_push($Names, substr($num[0],0,strlen($num[0])-3));
            } else {
                echo $num[0];
                array_push($Names, $num[0]);
            }

			echo '</td>';
			echo '<td class=price>';
			$num = explode("\n",$aktie[1]->plaintext);
			echo $num[0] . " &#8364;";
            array_push($Values, $num[0]);
			echo '</td>';


		}
		echo '</tr>';
	}
	
}

// Write to DB

$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
$date = new DateTime();
$timestamp = $date->getTimestamp();






$end = microtime(true);
 
$laufzeit = $end - $start;


?>
</table>
<div class=statistic>
<?php
	echo "Runtime: ".$laufzeit." seconds!";
?>
</div>

<?php

for($i = 0; $i < sizeof($ISINs) && $i < sizeof($Names) && $i < sizeof($Values); $i++) {
    $query ="INSERT INTO sharevalues VALUES ('" . substr($ISINs[$i],0,12) . "', CURRENT_TIMESTAMP, " . str_replace(",",".",$Values[$i]) . ")";
    echo $query;

    if (mysqli_query($con,$query)){
        echo "Query ran successfully: " . $query . "<br>";
    } else {
        echo "Error running query: " . mysqli_error($con) . "<br>";
    }
}

?>

</body>
</html>