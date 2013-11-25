<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Stock prospectus based on social media">
    <meta name="author" content="Patrick Spiegel">

    <title>Stock Prospectus</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <style>
        body {
            padding-top: 50px;
        }
        .starter-template {
            padding: 40px 15px;
            text-align: center;
        }

        .price {
            text-align: right;
        }

    </style>


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" href="#">Share Prospectus</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>

<div class="container">
    <div class="starter-template">
        <h1>Twitter Based Stock Analysis</h1>
        <p class="lead">Making use of current Twitter feeds, a general sentiment for each share can be measured.<br></p>
    </div>

    <div class="row">
        <div class="col-md-3">

            <h4>Indexes</h4>
            <ul class="nav nav-pills nav-stacked">
                <li class="active" ><a href="#dax">DAX</a></li>
                <li><a href="#dow">Dow Jones</a></li>
            </ul>

            <h4>Lists</h4>
            <ul class="nav nav-pills nav-stacked">
                <li class="" ><a href="#up">Tending Upwards</a></li>
                <li><a href="#down">Tending Downwards</a></li>
            </ul>
            

        </div>
        <div class="col-md-9">
            <div class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">DAX</div>

                <!-- Table -->
                <table class="table">
                    <tr><th>Name</th><th>ISIN</th><th>Value</th></tr>

<?php

// Include the library
include('db_connection.php');

// Attributes
$ISINs = array();
$Names = array();
$Values = array();


$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
$query = "SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.Timestamp FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name";


$result = mysqli_query($con,$query);
mysqli_close($con);

while($row = mysqli_fetch_array($result)){
    echo '<tr>';
    echo '<td>';
    echo $row['Name'];
    echo '</td>';
    echo '<td>';
    echo $row['ISIN'];
    echo '</td>';
    echo '<td class="price">';
    echo $row['Value'] . "&euro;";
    echo '</td>';
    echo '</tr>';
}
?>

    </table>
</div>
</div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>


<!--
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
	<tr><th>Name</th><th>ISIN</th><th>Value</th></tr>





