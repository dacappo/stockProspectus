<?php

include("../lib/databaseConnection.php");
echo "<style>body{font-family: Verdana, Geneva, sans-serif; font-size: 12px;} span{font-size: 9px;} </style>";

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$sql = array();
array_push($sql, "DROP TABLE IF EXISTS SentimentValues");
array_push($sql, "DROP TABLE IF EXISTS Prospectus");
array_push($sql, "DROP TABLE IF EXISTS Evaluation");
array_push($sql, "CREATE TABLE SentimentValues(Token VARCHAR(30) NOT NULL UNIQUE PRIMARY KEY, Sentiment FLOAT)");
array_push($sql, "CREATE TABLE Prospectus(ISIN CHAR(12), Sentiment FLOAT, Timestamp TIMESTAMP, Period SMALLINT , FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");
array_push($sql, "CREATE TABLE Evaluation(ISIN CHAR(12), Sentiment FLOAT, Timestamp TIMESTAMP, Period SMALLINT , RelativeChange FLOAT, Success BOOLEAN, FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");

// Execute queries
foreach ($sql as $statement) {
    if ($dbh->query($statement)){
        echo "Query ran successfully: <span>" . $statement . "</span><br>";
    } else {
        echo "Error running query: " . $dbh->errorInfo() . " :<span> " . $statement . "</span><br>";
    }
}
$dhb = null;
