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

function connectToDatabase($server, $user, $password, $database) {

    $dsn = 'mysql:dbname=' . $database . ';host=' . $server;

    try {
        $dbh = new PDO($dsn, $user, $password);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        $dbh = false;
    }
    return $dbh;
}



