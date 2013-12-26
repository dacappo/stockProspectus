<?php
/**
 * User: dacappa
 * Date: 17.11.13
 * Time: 17:49
 */

function connectToDB($server, $user, $password, $database) {
    // Create connection
    $con=mysqli_connect($server, $user, $password,$database);

    // Check connection
    if (mysqli_connect_errno($con)) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>";
    }

    return $con;
}


function initializeDB($con) {

    $sql = array();

    array_push($sql, "DROP TABLE ShareValues");
    array_push($sql, "DROP TABLE Shares");
    array_push($sql, "CREATE TABLE Shares(ISIN CHAR(12) NOT NULL UNIQUE PRIMARY KEY, Name  VARCHAR(30), Currency VARCHAR(6), StockIndex VARCHAR(10))");
    array_push($sql, "CREATE TABLE ShareValues(ISIN CHAR(12) NOT NULL, Timestamp TIMESTAMP NOT NULL, Value  DOUBLE(6,2), PRIMARY KEY(ISIN, Timestamp), FOREIGN KEY (ISIN) REFERENCES shares(ISIN))");

    // Execute queries
    foreach ($sql as $query) {
        if (mysqli_query($con,$query)){
            echo "Query ran successfully: " . $query . "<br>";
        } else {
            echo "Error running query: " . mysqli_error($con) . " : " . $query . "<br>";
        }
    }

}


