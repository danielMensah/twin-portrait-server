<?php
function checkUsername($user_iden){
    $dbh = getConnection(1);
    $sql = $dbh->prepare("CALL username_check(:u_iden)");
    $sql->bindParam(':u_iden', $user_iden, PDO::PARAM_STR);
    $sql->execute();
    $auth = $sql->fetch();
    return $auth;
}