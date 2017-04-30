<?php
function checkPassword($user_id, $user_pwd){
    $dbh = getConnection(2);
    $sql = $dbh->prepare("CALL password_check(:u_id, :u_pwd)");
    $sql->bindParam(':u_id', $user_id, PDO::PARAM_INT);
    $sql->bindParam(':u_pwd', $user_pwd, PDO::PARAM_STR);
    $sql->execute();
    $p_auth = $sql->fetch();
    return $p_auth['p_auth'];
}