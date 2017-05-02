<?php
function getConnection($option) {
    switch($option){
        case 1:
            $dbhost="ec2-34-250-225-109.eu-west-1.compute.amazonaws.com";
            $dbuser="olep_api";
            $dbpass="ZvRLZiensqef";
            $dbname="olep_user";
            break;
        case 2:
            $dbhost="ec2-34-250-225-109.eu-west-1.compute.amazonaws.com";
            $dbuser="olep_api_pwd";
            $dbpass="XpEMqus03TnEBV";
            $dbname="olep_secure";
            break;
    }
    $dbh = new PDO("mysql:host=$dbhost;port=55030;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
