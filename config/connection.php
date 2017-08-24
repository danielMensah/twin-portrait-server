<?php
function getConnection() {
    $host_name = '127.0.0.1';
    $database = 'test';
    $user_name = 'root';
    $password = '';

    $dbh = null;
    try {
        $dbh = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
        echo '<p>hello</p></br>';
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


