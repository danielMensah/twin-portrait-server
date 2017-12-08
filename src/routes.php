<?php

use Slim\Http\Request;
use Slim\Http\Response;

header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/DbConnection.php';
require_once __DIR__ . '/../Model/PortraitModel.php';
require_once __DIR__ . '/../Controllers/PortraitController.php';
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/ConsumerModel.php';
require_once __DIR__ . '/../Controllers/UserController.php';
require_once __DIR__ . '/../Managers/UtilManager.php';

/* ROUTES */


 //display random portrait
$app->get('/getPortrait', function (Request $request, Response $response) {

    $portraitController = new PortraitController();
    $response->getBody()->write($portraitController->getRandomPortrait());

    return $response;
});

// get portrait info
$app->post('/getPortraitInfo', function (Request $request, Response $response) {
    $portraitId = $request->getParsedBody()['id'];

    $portraitModel = new PortraitModel();
    $portraitModel->setId($portraitId);

    $portraitController = new PortraitController();
    $response->getBody()->write($portraitController->getPortraitInfo($portraitModel));

    return $response;
});

// upload form
$app->get('/portrait', function (Request $request, Response $response) {
    $portraitController = new PortraitController();
    echo $portraitController->uploadPortraitForm();
});

//upload portrait
$app->post('/uploadPortrait', function (Request $request, Response $response) {

    $num = $_POST["json"];
    $api = $_POST["api"];

    switch ($api) {
        case 1:
            $url = "https://storage.googleapis.com/tags_data/new_labels_assets/Self_portrait/$num.json";
            break;
        case 2:
            $url = "https://storage.googleapis.com/tags_data/new_labels_assets/Portrait/$num.json";
            break;
    }

    $utilManger = new UtilManager();
    $data = $utilManger->curlCall($url);

    foreach ($data as $item) {
        $portraitModel = new PortraitModel();
        $portraitModel->setId($item->id);
        $portraitModel->setImageUrl($item->img);

        $portraitController = new PortraitController();
        echo $portraitController->addPortrait($portraitModel);
    }
//    echo addImage('PAEeCdN0S0qgNg', 'http://lh5.ggpht.com/jgpYFmLNAWJL3734TQOgoVZRUOOOuFskI_2XXSgahS_jjwRblaHKtyK_BH3U');
});

//update portrait
$app->post('/updatePortrait', function (Request $request, Response $response) {
    $reqDecoded = json_decode($request->getBody(), true);

    $portraitController = new PortraitController();
    $response->getBody()->write(
        $portraitController->updatePortrait($reqDecoded['landmarks'],
            $reqDecoded['portraitId'],
            $reqDecoded['gender'])
    );

    return $reqDecoded;
});

//set not applicable portrait
$app->post('/setNotApplicable', function (Request $request, Response $response) {
    $reqDecoded = json_decode($request->getBody(), true);

    $portraitModel = new PortraitModel();
    $portraitModel->setId($reqDecoded['portraitId']);

    $portraitController = new PortraitController();
    $response->getBody()->write($portraitController->handleNotApplicationPortrait($portraitModel));

    return $response;
});

//register user
$app->post('/registerUser', function (Request $request, Response $response) {

    $reqDecoded = json_decode($request->getBody(), true);

    $consumerModel = new ConsumerModel();
    $consumerModel->setEmail(strtolower($reqDecoded['email']));
    $consumerModel->setFeedback($reqDecoded['feedback']);
    $consumerModel->setUserType("consumer");

    $userController = new UserController();
    $response->getBody()->write($userController->registerUser($consumerModel));

    return $response;
});

$app->get('/statistics', function (Request $request, Response $response) {

    $portraitController = new PortraitController();
    $response->getBody()->write($portraitController->getStatistics());

    return $response;
});

$app->post('/match', function (Request $request, Response $response) {
    $reqDecoded = json_decode($request->getBody(), true);

    $portraitController = new PortraitController();
    $response->getBody()->write(
        $portraitController->generatePossibleDoppelganger(
            $reqDecoded['landmarks'],
            $reqDecoded['gender'],
            ($reqDecoded['beard'] === 'true'),
            ($reqDecoded['mustache'] === 'true'))
    );

    return $reqDecoded;
});