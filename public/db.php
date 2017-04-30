<?php
$db_name = 'olep_user';
$db_user = 'root';
$db_password = '';
$dsn = 'mysql:dbname='.$db_name.';host=127.0.0.1';
try {
$dbh = new PDO($dsn, $db_user, $db_password);
return $dbh;
} catch (PDOException $e) {
echo 'Connection failed: ' . $e->getMessage();
}
?>
