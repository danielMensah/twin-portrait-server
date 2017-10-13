<?php
function getConnection() {
    //testing = 1
    //production = 2
    $option = 2;

    switch($option){
        case 1:
            $host_name = '127.0.0.1';
            $database = 'twinportrait';
            $user_name = 'root';
            $password = '';
            break;
        case 2:
            $host_name = 'twinportrait.ca0ainbas4d3.us-west-2.rds.amazonaws.com';
            $database = 'twinportrait_server';
            $user_name = 'ovothebest';
            $password = 'Mensah2929';
            break;
    }

    $dbh = null;
    try {
        $dbh = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
        return $dbh;
    } catch (PDOException $e) {
        echo "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}


