<?php

require_once __DIR__ . '/../Managers/DBManager.php';

class DbConnection {

    /**
     * @param int $option
     * @return null|PDO
     * testing = 1
     * production = 2
     */

    function getConnection($option = 1) {

        switch($option){
            case 1:
                $host_name = '127.0.0.1';
                $database = 'twinportrait-server-test';
                $user_name = 'root';
                $password = '';
                break;
            case 2:
                $host_name = 'twinportrait.ca0ainbas4d3.us-west-2.rds.amazonaws.com';
                $database = 'twinportrait_server';
                $user_name = getDBUsername();
                $password = getDBPassword();
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

}


