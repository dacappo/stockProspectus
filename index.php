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

        .positive {
            color: #4cae4c;
        }

        .negative {
            color: #c12e2a;
        }

        .neutral {
            color: black;
            visibility: hidden;
        }

        .sum_up_row {
            background-color: #f5f5f5;
            font-weight: 800;
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

            <a class="navbar-brand" href="dax"><span class="glyphicon glyphicon-stats"></span> Stock Prospectus</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">Home</a></li>
                <li><a href="admin/">Settings</a></li>
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
                <li id="dax"><a href="./dax">DAX</a></li>
                <li id="dj"><a href="./dowjones" >Dow Jones</a></li>
            </ul>

            <h4>Lists</h4>
            <ul class="nav nav-pills nav-stacked">
                <li id="up"><a href="./up">Tending Upwards</a></li>
                <li id="down"><a href="./down" >Tending Downwards</a></li>
            </ul>
            <!-- Default panel contents -->
            <br><br><br>
            <table>
            <tr><td>Timestamp </td><td><span id="time_of_update" class="label label-primary">Info</span></td></tr>

            <tr><td>Hourly </td><td><span id="time_of_update" class="label label-primary">last hour</span></td></tr>

            <tr><td>Daily </td><td><span id="time_of_update" class="label label-primary">last 24 hours</span></td></tr>

            <tr><td>Weekly </td><td><span id="time_of_update" class="label label-primary">last 168 hours</span></td></tr>
            </table>

        </div>
        <div class="col-md-9">
                <!-- Table -->
                <table id="share-table" class="table table-hover">
                    <tr>
                        <th>Name</th>
                        <th class="price">Value</th>
                        <th class="price">Hourly <span class="glyphicon glyphicon-stats"></span></th>
                        <th class="price hidden-xs">Daily <span class="glyphicon glyphicon-stats"></span></th>
                        <th class="price hidden-xs">Weekly <span class="glyphicon glyphicon-stats"></span></th>
                    </tr>


<?php

include('lib/databaseConnection.php');

$index = $_GET["index"];

// Attributes
$ISINs = array();
$Names = array();
$Values = array();

$avgSpreadH;
$avgSpreadD;
$avgSpreadW;

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$valueQuery = $dbh->prepare("SELECT * FROM (SELECT Shares.Name, Shares.ISIN, ShareValues.Value, ShareValues.SpreadH, ShareValues.SpreadD, ShareValues.SpreadW, ShareValues.Timestamp, Shares.Currency FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = :index ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result ORDER BY Name");
$valueQuery->bindParam(':index', $index);

$averageQuery = $dbh->prepare("SELECT AVG(SpreadH) AS SpreadH, AVG(SpreadD) AS SpreadD, AVG(SpreadW) AS SpreadW FROM(SELECT ShareValues.SpreadH AS SpreadH, ShareValues.SpreadD AS SpreadD, ShareValues.SpreadW AS SpreadW FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = :index ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result;");
$averageQuery->bindParam(':index', $index);

$prospectus1Query = $dbh->prepare("SELECT * FROM (SELECT Shares.Name, Shares.ISIN FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = :index ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result LEFT OUTER JOIN (SELECT pro.ISIN, Sentiment, Timestamp FROM dacappa_stockprospectus.prospectus AS pro, dacappa_stockprospectus.shares AS sha WHERE sha.ISIN = pro.isin AND sha.StockIndex = :index AND pro.Period = 1 AND DATE_ADD(Timestamp, INTERVAL 1 HOUR) >= NOW() ORDER BY Timestamp DESC) AS Tab ON Tab.ISIN = Result.ISIN GROUP BY Name");
$prospectus1Query->bindParam(':index', $index);

$prospectus24Query = $dbh->prepare("SELECT * FROM (SELECT Shares.Name, Shares.ISIN FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = :index ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result LEFT OUTER JOIN (SELECT pro.ISIN, Sentiment, Timestamp FROM dacappa_stockprospectus.prospectus AS pro, dacappa_stockprospectus.shares AS sha WHERE sha.ISIN = pro.isin AND sha.StockIndex = :index AND pro.Period = 24 AND DATE_ADD(Timestamp, INTERVAL 24 HOUR) >= NOW() ORDER BY Timestamp DESC) AS Tab ON Tab.ISIN = Result.ISIN GROUP BY Name");
$prospectus24Query->bindParam(':index', $index);

$prospectus168Query = $dbh->prepare("SELECT * FROM (SELECT Shares.Name, Shares.ISIN FROM Shares, ShareValues WHERE Shares.ISIN = ShareValues.ISIN AND Shares.StockIndex = :index ORDER BY ShareValues.Timestamp DESC LIMIT 30) AS result LEFT OUTER JOIN (SELECT pro.ISIN, Sentiment, Timestamp FROM dacappa_stockprospectus.prospectus AS pro, dacappa_stockprospectus.shares AS sha WHERE sha.ISIN = pro.isin AND sha.StockIndex = :index AND pro.Period = 168 AND DATE_ADD(Timestamp, INTERVAL 168 HOUR) >= NOW() ORDER BY Timestamp DESC) AS Tab ON Tab.ISIN = Result.ISIN GROUP BY Name");
$prospectus168Query->bindParam(':index', $index);

if ($valueQuery->execute()){
    //echo "Query ran successfully: <span>" . $stmtQueries->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($valueQuery->errorInfo()) . " : <span>" . $valueQuery->queryString . "</span><br>";
}

if ($averageQuery->execute()){
    //echo "Query ran successfully: <span>" . $stmtQueries->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($averageQuery->errorInfo()) . " : <span>" . $averageQuery->queryString . "</span><br>";
}

if ($prospectus1Query->execute() && $prospectus24Query->execute() && $prospectus168Query->execute()){
    //echo "Query ran successfully: <span>" . $stmtQueries->queryString . "</span><br>";
} else {
    echo "Error running query: " . array_pop($prospectus1Query->errorInfo()) . " : <span>" . $prospectus1Query->queryString . "</span><br>";
}


while($row = $averageQuery->fetch()){
    $avgSpreadH = round($row['SpreadH'],2);
    $avgSpreadD = round($row['SpreadD'],2);
    $avgSpreadW = round($row['SpreadW'],2);
}

$timeStamp = "Something went wrong!";

while($row = $valueQuery->fetch()){

    $prospectus1 = $prospectus1Query->fetch();
    $prospectus24 = $prospectus24Query->fetch();
    $prospectus168 = $prospectus168Query->fetch();

    $prospectus1 = $prospectus1['Sentiment'];
    $prospectus24 = $prospectus24['Sentiment'];
    $prospectus168 = $prospectus168['Sentiment'];

    $timeStamp = $row['Timestamp'];
    echo '<tr>';
    echo '<td>' . $row['Name'] . '</td>';
    echo '<td class="price">' . $row['Value'] . $row['Currency'] . '</td>';

    if ($row['SpreadH'] != 9999.99) {
        if ($row['SpreadH'] >= $avgSpreadH) {
            if($prospectus1 > 0) {
                echo '<td class="price positive">' . $row['SpreadH'] . '% ' .  '<span class="positive glyphicon glyphicon-arrow-up"></span></td>';
            } else if ($prospectus1 < 0) {
                echo '<td class="price positive">' . $row['SpreadH'] . '% ' .  '<span class="negative glyphicon glyphicon-arrow-down"></span></td>';
            } else {
                echo '<td class="price positive">' . $row['SpreadH'] . '% ' .  '<span class="neutral glyphicon glyphicon-arrow-right"></span></td>';
            }
        } else {
            if($prospectus1 > 0) {
                echo '<td class="price negative">' . $row['SpreadH'] . '% ' .  '<span class=" positive glyphicon glyphicon-arrow-up"></span></td>';
            } else if ($prospectus1 < 0) {
                echo '<td class="price negative">' . $row['SpreadH'] . '% ' .  '<span class=" negative glyphicon glyphicon-arrow-down"></span></td>';
            } else {
                echo '<td class="price negative">' . $row['SpreadH'] . '% ' .  '<span class="neutral glyphicon glyphicon-arrow-right"></span></td>';
            }
        }
    } else {
        echo '<td class="price">-</td>';
    }

    if ($row['SpreadD'] != 9999.99) {
        if ($row['SpreadD'] >= $avgSpreadD) {
            if($prospectus24 > 0) {
                echo '<td class="hidden-xs price positive">' . $row['SpreadD'] . '% ' . '<span class="positive glyphicon glyphicon-arrow-up"></span></td>';
            } else if ($prospectus24 < 0){
                echo '<td class="hidden-xs price positive">' . $row['SpreadD'] . '% ' . '<span class="negative glyphicon glyphicon-arrow-down"></span></td>';
            } else {
                echo '<td class="hidden-xs price positive">' . $row['SpreadD'] . '% ' . '<span class="neutral glyphicon glyphicon-arrow-right"></span></td>';
            }
        } else {
            if($prospectus24 > 0) {
                echo '<td class="hidden-xs price negative">' . $row['SpreadD'] . '% ' . '<span class=" positive glyphicon glyphicon-arrow-up"></span></td>';
            } else if ($prospectus24 < 0){
                echo '<td class="hidden-xs price negative">' . $row['SpreadD'] . '% ' . '<span class=" negative glyphicon glyphicon-arrow-down"></span></td>';
            } else {
                echo '<td class="hidden-xs price negative">' . $row['SpreadD'] . '% ' . '<span class="neutral glyphicon glyphicon-arrow-right"></span></td>';
            }
        }
    } else {
        echo '<td class="hidden-xs price">-</td>';
    }

    if ($row['SpreadW'] != 9999.99) {
        if ($row['SpreadW'] > $avgSpreadW) {
            if($prospectus168 = 0) {
                echo '<td class="price hidden-xs positive" >' . $row['SpreadW'] . '%' .  '<span class="positive glyphicon glyphicon-arrow-up"></span></td>';
            }else if ($prospectus168 < 0){
                echo '<td class="price hidden-xs positive" >' . $row['SpreadW'] . '%' .  '<span class="negative glyphicon glyphicon-arrow-down"></span></td>';
            } else {
                echo '<td class="price hidden-xs positive" >' . $row['SpreadW'] . '%' .  '<span class="neutral glyphicon glyphicon-arrow-right"></span></td>';
            }
        } else {
            if($prospectus168 > 0) {
                echo '<td class="price hidden-xs negative" >' . $row['SpreadW'] . '%' .  '<span class=" positive glyphicon glyphicon-arrow-up"></span></td>';
            } else if ($prospectus168 < 0){
                echo '<td class="price hidden-xs negative" >' . $row['SpreadW'] . '%' .  '<span class=" negative glyphicon glyphicon-arrow-down"></span></td>';
            } else {
                echo '<td class="price hidden-xs negative" >' . $row['SpreadW'] . '%' .  '<span class="neutral glyphicon glyphicon-arrow-right"></span></td>';
            }
        }
    } else {
        echo '<td class="hidden-xs price">-</td>';
    }
    echo '</tr>';
}


echo '<tr class="sum_up_row"><td><span class="glyphicon glyphicon-stats"></span></td><td></td>';
if ($avgSpreadH < 1000.00) {
    if ($avgSpreadH >= 0) {
        echo '<td class="price ">' . $avgSpreadH . '%' . '</td>';
    } else {
        echo '<td class="price">' . $avgSpreadH . '%' . '</td>';
    }
} else {
    echo '<td class="price">-</td>';
}

if ($avgSpreadD < 1000.00) {
    if ($avgSpreadD >= 0) {
        echo '<td class="hidden-xs price">' . $avgSpreadD . '%' . '</td>';
    } else {
        echo '<td class="hidden-xs price">' . $avgSpreadD . '%' . '</td>';
    }
} else {
    echo '<td class="hidden-xs price">-</td>';
}

if ($avgSpreadW < 1000.00) {
    if ($avgSpreadW >= 0) {
        echo '<td class="hidden-xs price">' . $avgSpreadW . '%' . '</td>';
    } else {
        echo '<td class="hidden-xs price">' . $avgSpreadW . '%' . '</td>';
    }
} else {
    echo '<td class="hidden-xs price">-</td>';
}

echo '</tr>';

echo '</table>';
echo '<script>document.getElementById("time_of_update").innerHTML = "' . $timeStamp . '"; document.getElementById("' . $index . '").setAttribute("class","active")</script>';

?>
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





