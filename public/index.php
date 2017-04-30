<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require $_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'].'/../config/connection.php';
$app = new \Slim\App;

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello Test");
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

$app->get('/random', function (Request $request, Response $response) {
    require $_SERVER['DOCUMENT_ROOT'].'/../util/tokenGeneratorUtil.php';
    $str = generateToken();
    $response->getBody()->write("Hello, $str");
    return $response;
});

$app->get('/users', function (Request $request, Response $response) {
    $dbh = getConnection(1);
    $sql = $dbh->prepare("SELECT * FROM user");
    $sql->execute();
    $result = $sql->fetchAll();
//    echo json_encode($result);
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
