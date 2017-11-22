<?php

use Slim\Http\Request;
use Slim\Http\Response;

header('Access-Control-Allow-Origin: *');

require_once '../config/DbConnection.php';
require_once '../Model/PortraitModel.php';
require_once '../Controllers/PortraitController.php';
require_once '../Model/UserModel.php';
require_once '../Model/ConsumerModel.php';
require_once '../Controllers/UserController.php';
require_once '../Managers/UtilManager.php';

/* ROUTES */

 //display random portrait
$app->get('/getPortrait', function (Request $request, Response $response) {

    $portraitController = new PortraitController();
    return $portraitController->getRandomPortrait();
});

// get portrait info
$app->post('/getPortraitInfo', function (Request $request, Response $response) {
    $portraitId = $request->getParsedBody()['id'];

    $portraitModel = new PortraitModel();
    $portraitModel->setId($portraitId);

    $portraitController = new PortraitController($portraitModel);
    echo $portraitController->getPortraitInfo();
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

        $portraitController = new PortraitController($portraitModel);
        echo $portraitController->addPortrait();
    }
//    echo addImage('PAEeCdN0S0qgNg', 'http://lh5.ggpht.com/jgpYFmLNAWJL3734TQOgoVZRUOOOuFskI_2XXSgahS_jjwRblaHKtyK_BH3U');
});

//update portrait
$app->post('/updatePortrait', function (Request $request, Response $response) {

    $arrayOfLandmarks = [];
    $reqDecoded = json_decode($request->getBody(), true);
    foreach ($reqDecoded as $feature) {
        if (!empty($feature['landmark'])) {
            $arrayOfLandmarks[$feature['landmark']] = array(
                'landmark' => $feature['landmark'],
                'landmarkKey' => $feature['landmarkKey']
            );
        }
    }

    $portraitController = new PortraitController();
    return $portraitController->updatePortrait($arrayOfLandmarks,
        $reqDecoded['portraitId'],
        $reqDecoded['gender'],
        $reqDecoded['mustache'],
        $reqDecoded['beard']);
});

//set not applicable portrait
$app->post('/setNotApplicable', function (Request $request, Response $response) {
    $reqDecoded = json_decode($request->getBody(), true);

    $portraitModel = new PortraitModel();
    $portraitModel->setId($reqDecoded['portraitId']);

    $portraitController = new PortraitController($portraitModel);
    echo $portraitController->handleNotApplicationPortrait();
});

//register user
$app->post('/registerUser', function (Request $request, Response $response) {

    $reqDecoded = json_decode($request->getBody(), true);

    $consumerModel = new ConsumerModel();
    $consumerModel->setEmail(strtolower($reqDecoded['email']));
    $consumerModel->setFeedback($reqDecoded['feedback']);
    $consumerModel->setUserType("consumer");

    $userController = new UserController($consumerModel);
    echo $userController->registerUser();
});

$app->get('/statistics', function (Request $request, Response $response) {

    $portraitController = new PortraitController();
    echo $portraitController->getStatistics();
});

$app->get('/getUser', function (Request $request, Response $response) {

    $userController = new UserController();
    echo $userController->getUser();
});
