<?php
function encodeUserToken($u_auth, $p_auth, $u_token){
    if (!isset($p_auth)){
        $p_auth = 0;
        $u_token = 0;
    }
    $jarr = array(
        'u_auth' => $u_auth,
        'p_auth' => $p_auth,
        'u_token' => $u_token
        );
<<<<<<< HEAD
    /*$jarr[u_auth] = $u_auth;
    $jarr[p_auth] = $p_auth;
    $jarr[u_token] = $u_token;*/
=======
>>>>>>> master
    return json_encode($jarr, JSON_NUMERIC_CHECK);
}