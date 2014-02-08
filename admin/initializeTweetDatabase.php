<?php

include("../lib/databaseConnection.php");
echo "<style>body{font-family: Verdana, Geneva, sans-serif; font-size: 12px;} span{font-size: 9px;} </style>";

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$sql = array();

array_push($sql, "DROP TABLE TweetTokens");
array_push($sql, "DROP TABLE Tweets");
array_push($sql, "CREATE TABLE Tweets(ID INT NOT NULL UNIQUE PRIMARY KEY AUTO_INCREMENT, ISIN CHAR(12), Retweets INT, Sentiment SMALLINT, FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");
array_push($sql, "CREATE TABLE TweetTokens(ID INT NOT NULL UNIQUE PRIMARY KEY AUTO_INCREMENT,Tweet INT, Token VARCHAR(30), FOREIGN KEY (Tweet) REFERENCES Tweets(ID))");

// Execute queries
foreach ($sql as $statement) {
    if ($dbh->query($statement)){
        echo "Query ran successfully: <span>" . $statement . "</span><br>";
    } else {
        echo "Error running query: " . $dbh->errorInfo() . " :<span> " . $statement . "</span><br>";
    }
}
$dhb = null;
