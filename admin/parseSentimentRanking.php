<?php
/*
 *  Parses sentiment list (lib/afinn.txt) into database
 *
 */
include("../lib/databaseConnection.php");

$txt_file    = file_get_contents('../lib/afinn.txt');
$rows        = explode("\n", $txt_file);

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$stmt = $dbh->prepare('INSERT INTO SentimentValues VALUES (:token, :sentiment)');
$stmt->bindParam(':token', $token);
$stmt->bindParam(':sentiment', $sentiment);

foreach($rows as $row => $data){
    //get row data
    $row_data = explode(' ', $data);

    $token     = $row_data[0];
    $sentiment  = $row_data[1];

    //display data
    echo 'Row ' . $row . ' Token: ' . $token . '<br />';
    echo 'Row ' . $row . ' Sentiment: ' . $sentiment . '<br />';

    if ($stmt->execute()){
        echo "Query ran successfully: <span>" . $stmt->queryString . "</span><br>";
    } else {
        echo "Error running query: " . array_pop($stmt->errorInfo()) . " : <span>" . $stmt->queryString . "</span><br>";
    }

    echo '<br/>';
}

$dbh = null;