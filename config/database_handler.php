<?php


$host = "localhost";
$user = "root";
$pass = "";
$db = "webbshop";


try {
    $dsn = "mysql:host=$host;dbname=$db;";
    $databaseHandler = new PDO($dsn, $user, $pass);

} catch(PDOException $e) {
    echo "Error! ". $e->getMessage() ."<br />";
    die;
}


?>