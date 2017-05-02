<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require('../vendor/autoload.php');
require('../config/connection.php');
//require $_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php';
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Welcome to OLEP's API!\n Currently under development!");
    return $response;
});

$app->get('/test}', function (Request $request, Response $response) {
    $response->getBody()->write("Hello, You!");

    return $response;
});

/* USER LOGIN SECTION */
$app->post('/user', function (Request $request, Response $response) {
    require $_SERVER['DOCUMENT_ROOT'].'/../model/userModel.php';
    require $_SERVER['DOCUMENT_ROOT'].'/../model/upasswordModel.php';
    require $_SERVER['DOCUMENT_ROOT'].'/../model/activeConnectionModel.php';
    require $_SERVER['DOCUMENT_ROOT'].'/../util/tokenGeneratorUtil.php';
    require $_SERVER['DOCUMENT_ROOT'].'/../util/encodeUtil.php';
    $hash_pwd = hash('sha512',$user_pwd = $request->getParsedBody()['password']);
    $user_iden = $request->getParsedBody()['username'];
    $u_auth = checkUsername($user_iden);
    $p_auth = 0;
    $u_token = 0;
    if ($u_auth['u_auth'] == 1){
        $p_auth = checkPassword($u_auth['user_id'], $hash_pwd);
        if ($p_auth == 1) {
            $u_token = generateToken();
            insertToken($u_auth['user_id'], $u_token);
        }
    }
    $data =  encodeUserToken($u_auth['u_auth'],$p_auth, $u_token);
    echo $data;
    return $response;
});
/* END LOGIN SECTION */

$app->get('/userProfile/{token}', function (Request $request, Response $response) {
    $token = $request->getAttribute('token');
    $dbh = getConnection(1);
    $sql = $dbh->prepare("  SELECT up.first_name, up.last_name FROM user_profile up, active_connection ac, user u
                            WHERE ac.user_id = u.user_id
                            AND up.user_id = u.user_id
                            AND ac.user_token = :token;");
    $sql->bindParam(':token', $token, PDO::PARAM_STR);
    if ($sql->execute()){
        $result = json_encode($sql->fetchAll(PDO::FETCH_ASSOC));

        //$response->getBody()->write("$token\n $dbh->lastInsertId()");
    }
    return $result;
});

/*
$app->post('/userProfile/{token}', function (Request $request, Response $response) {
    $dbh = getConnection(1);
    $email_address = $request->getParsedBody()['email_address'];
    $sql = $dbh->prepare(""); // CALL PROCEDURE
    // bindParams
    if ($sql->execute()){
        $response->getBody()->write("Success");
    } else {
        $response->getBody()->write("Failure");
    }
    return $response;
});

*/
$app->get('/randomKey', function (Request $request, Response $response) {
    require $_SERVER['DOCUMENT_ROOT'].'/../util/tokenGeneratorUtil.php';
    $str = generateToken();
    $response->getBody()->write("$str");
    return $response;
});

$app->get('/users', function (Request $request, Response $response) {
    $dbh = getConnection(1);
    $sql = $dbh->prepare("SELECT * FROM user");
    $sql->execute();
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($result);
});

$app->post('/users', function (Request $request, Response $response) {
    $dbh = getConnection(1);
    $user_id = $request->getParsedBody()['username'];
    $user_group_id = $request->getParsedBody()['user_group_id'];
    $u_pwd = hash('sha512',$user_pwd = $request->getParsedBody()['password']);
    $sql = $dbh->prepare("INSERT INTO user (user_identifier, user_group_id) VALUES (:u_id, :ug_id)");
    $sql->bindParam(':u_id', $user_id, PDO::PARAM_STR);
    $sql->bindParam(':ug_id', $user_group_id, PDO::PARAM_INT);
    if ($sql->execute())
    {
        $u_id = $dbh->lastInsertId();
        $dbh = getConnection(2);
        $sql = $dbh->prepare("INSERT INTO upassword (user_id, password_hash) VALUES (:u_id, :u_password)");
        $sql->bindParam(':u_id', $u_id, PDO::PARAM_INT);
        $sql->bindParam(':u_password', $u_pwd, PDO::PARAM_STR);
        $sql->execute();
    }
});

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});
$app->run();