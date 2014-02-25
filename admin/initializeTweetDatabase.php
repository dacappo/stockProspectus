<?php

include("../lib/databaseConnection.php");
echo "<style>body{font-family: Verdana, Geneva, sans-serif; font-size: 12px;} span{font-size: 9px;} </style>";

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$sql = array();

array_push($sql, "DROP TABLE IF EXISTS TweetTokens");
array_push($sql, "DROP TABLE IF EXISTS Tweets");
array_push($sql, "DROP TABLE IF EXISTS TweetSearch");
array_push($sql, "CREATE TABLE TweetSearch(ISIN CHAR(12), Query VARCHAR(30), FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");
array_push($sql, "CREATE TABLE Tweets(TweetID BIGINT NOT NULL, ISIN CHAR(12), Timestamp TIMESTAMP NOT NULL, Retweets INT, Tweet VARCHAR(160), Sentiment FLOAT,  PRIMARY KEY(TweetID,  ISIN), FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");
array_push($sql, "CREATE TABLE TweetTokens(TweetID BIGINT, ISIN CHAR(12), Token VARCHAR(30), FOREIGN KEY (TweetID, ISIN) REFERENCES Tweets(TweetID, ISIN))");

// Execute queries
foreach ($sql as $statement) {
    if ($dbh->query($statement)){
        echo "Query ran successfully: <span>" . $statement . "</span><br>";
    } else {
        echo "Error running query: " . $dbh->errorInfo() . " :<span> " . $statement . "</span><br>";
    }
}

/*
 * Create new search.txt. New search parameters have be entered!
 *
$file = '../lib/searchNew.txt';

$statement = "SELECT ISIN, Name FROM Shares";
$result = $dbh->query($statement);

foreach ($result AS $row) {
    $string .= $row['ISIN'] . "|";
    $string .= str_replace("\r","",$row['Name']) . "|" .  "\n";
}

// Write the contents back to the file
file_put_contents($file, $string);

echo "Query file written!";
*/

$dbh = null;
