<?php

include("../lib/databaseConnection.php");
echo "<style>body{font-family: Verdana, Geneva, sans-serif; font-size: 12px;} span{font-size: 9px;} </style>";

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$sql = array();
array_push($sql, "DROP TABLE SentimentValues");
array_push($sql, "DROP TABLE Prospectus");
array_push($sql, "CREATE TABLE SentimentValues(Token VARCHAR(30) NOT NULL UNIQUE PRIMARY KEY, Sentiment SMALLINT)");
array_push($sql, "CREATE TABLE Prospectus(ISIN CHAR(12), Sentiment SMALLINT, FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");

// Execute queries
foreach ($sql as $statement) {
    if ($dbh->query($statement)){
        echo "Query ran successfully: <span>" . $statement . "</span><br>";
    } else {
        echo "Error running query: " . $dbh->errorInfo() . " :<span> " . $statement . "</span><br>";
    }
}
$dhb = null;
