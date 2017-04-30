<?php
function insertToken($user_id, $token){
    $dbh = getConnection(1);
    $acsql = $dbh->prepare("INSERT INTO active_connection(user_id, user_token, session_state_id) VALUES (:u_id, :u_token, 1);");
    $acsql->bindParam(':u_id', $user_id, PDO::PARAM_INT);
    $acsql->bindParam(':u_token', $token, PDO::PARAM_STR);
    $acsql->execute();

}