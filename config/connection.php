<?php
function getConnection($option) {
    switch($option){
        case 1:
            $dbhost="127.0.0.1";
            $dbuser="root";
            $dbpass="";
            $dbname="olep_user";
            break;
        case 2:
            $dbhost="127.0.0.1";
            $dbuser="root";
            $dbpass="";
            $dbname="olep_password";
            break;
    }
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
