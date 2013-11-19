<!DOCTYPE html>
<html>
<head>
	<title>Aktienindex</title>
	<style type=text/css>
		html {
			color: #6678b1;
			font-family: Segoe UI Light, Verdana;
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
	<tr><th>ISIN</th><th>Value</th></tr>

<?php

// Include the library
include('db_connection.php');

// Attributes
$ISINs = array();
$Names = array();
$Values = array();


$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
$query = "SELECT * FROM ShareValues ORDER BY Timestamp DESC LIMIT 30";

$result = mysqli_query($con,$query);

while($row = mysqli_fetch_array($result)){
    echo '<tr>';
    echo '<td>';
    echo $row['ISIN'];
    echo '</td>';
    echo '<td class="price">';
    echo $row['Value'] . "&euro;";
    echo '</td>';
    echo '</tr>';
}

mysqli_close($con);

?>
</table>

</body>
</html>



