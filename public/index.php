<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

header('Access-Control-Allow-Origin: *');
require('../vendor/autoload.php');
require('../config/connection.php');
require '../util/console.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$container = $app->getContainer();
$container['upload_directory'] = $_SERVER['DOCUMENT_ROOT'].'/../images';

// test connection
$app->get('/', function (Request $request, Response $response) {
    getConnection();
});



// display random portrait
$app->get('/getPortrait', function (Request $request, Response $response) {
    require '../model/imageModel.php';

    $randomPortrait = json_encode(array(
        'portraitURL' => getRandomPortrait()
    ));

    return $randomPortrait;
});

// upload form
$app->get('/portrait', function (Request $request, Response $response) {
    require '../model/formsModel.php';
    echo uploadPortraitForm();
});

//upload portrait
$app->post('/uploadPortrait', function (Request $request, Response $response) {
    require ('../model/imageModel.php');

    $uploadedFiles = $request->getUploadedFiles();

    // handle single input with multiple file uploads
    foreach ($uploadedFiles['portrait'] as $uploadedFile) {
        if (!empty($uploadedFile)) {
            $filename = uploadImage($uploadedFile);
            $response->write('uploaded ' . $filename . '<br/>');
        }
    }
});

//update portrait
$app->post('/updatePortrait', function (Request $request, Response $response) {
    require ('../model/imageModel.php');

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

    echo updatePortrait($arrayOfLandmarks, $reqDecoded['portraitUrl']);
});

//set not applicable portrait
$app->post('/setNotApplicable', function (Request $request, Response $response) {
    require ('../model/imageModel.php');

    $reqDecoded = json_decode($request->getBody(), true);
    echo handleNotApplicationPortrait($reqDecoded['portraitUrl']);
});

$app->run();
