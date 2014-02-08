<?php
/**
 * User: Patrick
 * Date: 18.11.13
 * Time: 13:31
 */

include("../lib/databaseConnection.php");
echo "<style>body{font-family: Verdana, Geneva, sans-serif; font-size: 12px;} span{font-size: 9px;} </style>";

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$sql = array();
array_push($sql, "DROP TABLE ShareValues");
array_push($sql, "DROP TABLE Shares");
array_push($sql, "CREATE TABLE Shares(ISIN CHAR(12) NOT NULL UNIQUE PRIMARY KEY, Name  VARCHAR(30), Currency VARCHAR(6), StockIndex VARCHAR(10))");
array_push($sql, "CREATE TABLE ShareValues(ISIN CHAR(12) NOT NULL, Timestamp TIMESTAMP NOT NULL, Value  DOUBLE(6,2), SpreadH  DOUBLE(6,2), SpreadD  DOUBLE(6,2), SpreadW  DOUBLE(6,2), PRIMARY KEY(ISIN, Timestamp), FOREIGN KEY (ISIN) REFERENCES shares(ISIN))");

// Execute queries
foreach ($sql as $statement) {
    if ($dbh->query($statement)){
        echo "Query ran successfully: <span>" . $statement . "</span><br>";
    } else {
        echo "Error running query: " . $dbh->errorInfo() . " :<span> " . $statement . "</span><br>";
    }
}

$dhb = null;
