<?php
function getConnection() {
//    $host_name = '127.0.0.1';
//    $database = 'test';
//    $user_name = 'root';
//    $password = '';

    $host_name = 'twinportrait.ca0ainbas4d3.us-west-2.rds.amazonaws.com';
    $database = 'twinportrait_server';
    $user_name = 'ovothebest';
    $password = 'Mensah2929';

    $dbh = null;
    try {
        $dbh = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
        echo '<p>Connected</p></br>';
        return $dbh;
    } catch (PDOException $e) {
        echo "Error!: " . $e->getMessage() . "<br/>";
        die();
    }

//    $host_name = '127.0.0.1';
//    $database = 'test';
//    $user_name = 'root';
//    $password = '';
//    $connect = mysqli_connect($host_name, $user_name, $password, $database);
//
//    if (mysqli_connect_errno()) {
//        die('<p>Failed to connect to MySQL: '.mysqli_connect_error().'</p>');
//    } else {
//        echo '<p>Connection to MySQL server successfully established.</p >';
//    }
}


