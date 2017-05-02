<?php
function encodeUserToken($u_auth, $p_auth, $u_token){
    if (!isset($p_auth)){
        $p_auth = 0;
        $u_token = 0;
    }
    $jarr = array(
        array("label" => "u_auth", "data" => $u_auth;
        array("label" => "p_auth", "data" => $p_auth;
        array("label" => "u_token", "data" => $u_token
    );
    //$jarr = array();
    /*$jarr[u_auth] = $u_auth;
    $jarr[p_auth] = $p_auth;
    $jarr[u_token] = $u_token;*/
    return json_encode($jarr);
}