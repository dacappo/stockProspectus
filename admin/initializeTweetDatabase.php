<?php

include("../lib/databaseConnection.php");
echo "<style>body{font-family: Verdana, Geneva, sans-serif; font-size: 12px;} span{font-size: 9px;} </style>";

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$sql = array();

array_push($sql, "DROP TABLE TweetTokens");
array_push($sql, "DROP TABLE Tweets");
array_push($sql, "DROP TABLE TweetSearch");
array_push($sql, "CREATE TABLE TweetSearch(ISIN CHAR(12), Query VARCHAR(30), FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");
array_push($sql, "CREATE TABLE Tweets(ID BIGINT NOT NULL UNIQUE PRIMARY KEY, ISIN CHAR(12), Retweets INT, Sentiment SMALLINT, FOREIGN KEY (ISIN) REFERENCES Shares(ISIN))");
array_push($sql, "CREATE TABLE TweetTokens(TweetID BIGINT, Token VARCHAR(30), FOREIGN KEY (TweetID) REFERENCES Tweets(ID))");

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

mysqli_close($dbh);
