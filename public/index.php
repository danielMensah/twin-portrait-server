<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

header('Access-Control-Allow-Origin: *');
require '../vendor/autoload.php';
require '../config/DbConnection.php';
require '../util/console.php';
require '../Model/PortraitModel.php';
require '../Controllers/PortraitController.php';
require '../Model/UserModel.php';
require '../Controllers/UserController.php';
require_once '../util/curlCall.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$container = $app->getContainer();
$container['upload_directory'] = $_SERVER['DOCUMENT_ROOT'].'/../images';

// display random portrait
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
    require '../Model/formsModel.php';
    echo uploadPortraitForm();
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

    $data = makeCall($url);

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

    $userModel = new UserModel();
    $userModel->setEmail(strtolower($reqDecoded['email']));
    $userModel->setFeedback($reqDecoded['feedback']);

    $userController = new UserController($userModel);
    echo $userController->registerUser();
});

$app->get('/statistics', function (Request $request, Response $response) {

    $portraitController = new PortraitController();
   echo $portraitController->getStatistics();
});

$app->run();
