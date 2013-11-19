<?php
/**
 * User: Patrick
 * Date: 18.11.13
 * Time: 13:31
 */

include("db_connection.php");

$con = connectToDB("localhost","dacappa","veryoftirjoicTeg3","dacappa_stockProspectus");
initializeDB($con);
mysqli_close($con);