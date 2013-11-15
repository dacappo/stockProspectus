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
	<tr><th>Name</th><th>Value</th></tr>

<?php
$start = microtime(true);

// Include the library
include('simple_html_dom.php');
 
// Retrieve the DOM from a given URL
$html = file_get_html('http://www.finanzen.net/index/Dax');

foreach($html->find('div.double_row_performance') as $e) {
	
	foreach($e->find('tr') as $tr) {
		echo '<tr>';
		$aktie = $tr->find('td');
		if (sizeof($aktie) > 0) {
			echo '<td>';
			echo $aktie[0]->find('a',0)->plaintext;
			echo '</td>';
			echo '<td class=price>';
			$num = explode("\n",$aktie[1]->plaintext);
			echo $num[0] . " &#8364;";
			echo '</td>';
		}
		echo '</tr>';
	}
	
}

$end = microtime(true);
 
$laufzeit = $end - $start;


?>
</table>
<div class=statistic>
<?php
	echo "Runtime: ".$laufzeit." seconds!";
?>
</div>

</body>
</html>