<?php
function removeLoginEntry($u_token){
    $dbh = getConnection(1);
    $sql = $dbh->prepare("DELETE FROM active_connection WHERE user_token = :u_token");
    $sql->bindParam(':u_token', $u_token, PDO::PARAM_STR);
    if ($sql->execute()) {
        return 1;
    } else {return 0;}
}