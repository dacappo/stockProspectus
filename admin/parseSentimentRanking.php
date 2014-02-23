<?php
/*
 *  Parses sentiment list (lib/afinn.txt) into database
 *
 */
include("../lib/databaseConnection.php");

$afinn_file  = file_get_contents('../lib/afinn.txt');
$pos_file    = file_get_contents('../lib/pos.txt');
$neg_file    = file_get_contents('../lib/neg.txt');

$afinn_rows        = explode("\n", $afinn_file);
$pos_rows        = explode("\n", $pos_file);
$neg_rows        = explode("\n", $neg_file);

$dbh = connectToDatabase("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");

$stmt = $dbh->prepare('INSERT INTO SentimentValues VALUES (:token, :sentiment)');
$stmt->bindParam(':token', $token);
$stmt->bindParam(':sentiment', $sentiment);

foreach($afinn_rows as $row => $data){
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

foreach($pos_rows as $row => $data){
    //get row data
    $row_data = explode(' ', $data);

    $token     = $row_data[0];
    $sentiment  =  0.4;

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

foreach($neg_rows as $row => $data){
    //get row data
    $row_data = explode(' ', $data);

    $token     = $row_data[0];
    $sentiment  =  -0.4;

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