<?php
include("../lib/databaseConnection.php");

$file = '../lib/search.txt';
// Get overall file content
$content = file_get_contents($file);
// Get single lines
$rows = explode("\n", $content);

// Establish database connection
$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
$stmt = $dbh->prepare('INSERT INTO TweetSearch VALUES (:isin, :query)');
$stmt->bindParam(':isin', $isin);
$stmt->bindParam(':query', $query);

// Loop through rows
foreach ($rows AS $row) {
    $row = explode("|",$row);
    if (sizeof($row) > 2) {
        $isin = $row[0];
        $query = $row[2];

        if ($stmt->execute()){
            echo "Query ran successfully: <span>" . $stmt->queryString . " : " . $isin . " " . $query . "</span><br>";
        } else {
            $spreadh = 0;
            echo "Error running query: " . array_pop($stmt->errorInfo()) . " : <span>" . $stmt->queryString . "</span><br>";
        }
    }
}