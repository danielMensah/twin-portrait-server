<?php
function encodeUserToken($u_auth, $p_auth, $u_token){
    if (!isset($p_auth)){
        $p_auth = 0;
        $u_token = 0;
    }
    $jarr = array();
    $jarr[u_auth] = $u_auth;
    $jarr[p_auth] = $p_auth;
    $jarr[u_token] = $u_token;
    return json_encode($jarr);
}