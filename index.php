<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Stock prospectus based on social media">
    <meta name="author" content="Patrick Spiegel">

    <title>Stock Prospectus</title>
    <link rel="shortcut icon" href="favicon.png" type="image/png"/>

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
        <div class="my_navigation col-md-3">

            <h4>Indexes</h4>
            <ul class="nav nav-pills nav-stacked">
                <li id="dax"><a href="/dax">DAX</a></li>
                <li id="dj"><a href="/dowjones" >Dow Jones</a></li>
            </ul>

            <h4>Lists</h4>
            <ul class="nav nav-pills nav-stacked">
                <li id="up"><a href="/up">Tending Upwards</a></li>
                <li id="down"><a href="/down" >Tending Downwards</a></li>
            </ul>

        </div>
        <div class="col-md-9">
            <div id="main_panel" class="panel panel-default">

                <!-- Default panel contents -->
                <div id="time_of_update" class="panel-heading">

                </div>
                <!-- Table -->
                <table id="share-table" class="table">
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                        <th>Hourly Spread</th>
                        <th class="hidden-sm">Daily Spread</th>
                        <th class=" hidden-sm">Weekly Spread</th>
                    </tr>


<?php

include('databaseConnection.php');

$index = $_GET["index"];

// Attributes
$ISINs = array();
$Names = array();
$Values = array();

$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
if ($index == "dax") {
    $query = "SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.SpreadH, ShareValues.SpreadD, ShareValues.SpreadW, ShareValues.Timestamp, Shares.Currency FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = 'DAX' ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name";
} else if ($index == "dj") {
    $query = "SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.SpreadH, ShareValues.SpreadD, ShareValues.SpreadW, ShareValues.Timestamp, Shares.Currency FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = 'DJ' ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name";
} else {
    $query = "SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.SpreadH, ShareValues.SpreadD, ShareValues.SpreadW, ShareValues.Timestamp, Shares.Currency FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = 'DAX' ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name";
}

$result = mysqli_query($con,$query);
mysqli_close($con);

$timeStamp = "Something went wrong!";



while($row = mysqli_fetch_array($result)){
    $timeStamp = $row['Timestamp'];
    echo '<tr>';
    echo '<td>' . $row['Name'] . '</td>';
    echo '<td class="price">' . $row['Value'] . '</td>';

    if ($row['SpreadH'] != $row['Value']) {
        echo '<td class="price">' . $row['SpreadH'] . $row['Currency'] .  '</td>';
    } else {
        echo '<td class="price">-</td>';
    }

    if ($row['SpreadD'] != $row['Value']) {
        echo '<td class="hidden-sm price">' . $row['SpreadD'] . $row['Currency'] . '</td>';
    } else {
        echo '<td class="hidden-sm price">-</td>';
    }

    if ($row['SpreadW'] != $row['Value']) {
        echo '<td class="price hidden-sm" >' . $row['SpreadW'] . $row['Currency'] .  '</td>';
    } else {
        echo '<td class="hidden-sm price">-</td>';
    }



    echo '</tr>';
}

echo '<script>document.getElementById("time_of_update").innerHTML = "' . $timeStamp . '"; document.getElementById("' . $index . '").setAttribute("class","active")</script>';

?>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

</body>
</html>





