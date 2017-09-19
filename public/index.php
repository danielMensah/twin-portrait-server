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
    require '../model/imageModel.php';

    getConnection();
});

// display random portrait
$app->get('/getPortrait', function (Request $request, Response $response) {
    require '../model/imageModel.php';

    return getRandomPortrait();
});

// get portrait info
$app->post('/getPortraitInfo', function (Request $request, Response $response) {
    require '../model/imageModel.php';
    $portraitId = $request->getParsedBody()['id'];

    echo getPortraitInfo($portraitId);
});

// upload form
$app->get('/portrait', function (Request $request, Response $response) {
    require '../model/formsModel.php';
    echo uploadPortraitForm();
});

//upload portrait
$app->get('/uploadPortrait', function (Request $request, Response $response) {
    require ('../model/imageModel.php');

//    $uploadedFiles = $request->getUploadedFiles();
//
//    // handle single input with multiple file uploads
//    foreach ($uploadedFiles['portrait'] as $uploadedFile) {
//        if (!empty($uploadedFile)) {
//            $filename = uploadImage($uploadedFile);
//            $response->write('uploaded ' . $filename . '<br/>');
//        }
//    }

////    for ($i = 0; $i < 8; $i++) {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, "https://storage.googleapis.com/tags_data/new_labels_assets/Self_portrait/7.json");
//        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
//
//// Fetch and return content, save it.
//        $raw_data = curl_exec($ch);
//        curl_close($ch);
//
//// If the API is JSON, use json_decode.
//        $data = json_decode($raw_data);
//
//        foreach ($data as $item) {
//            echo addImage($item->id, $item->img);
//        }
////    }
    echo 'Uncomment script to be able to upload portraits';
//    echo addImage('PAEeCdN0S0qgNg', 'http://lh5.ggpht.com/jgpYFmLNAWJL3734TQOgoVZRUOOOuFskI_2XXSgahS_jjwRblaHKtyK_BH3U');
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

    echo updatePortrait(
        $arrayOfLandmarks,
        $reqDecoded['portraitUrl'],
        $reqDecoded['gender'],
        $reqDecoded['mustache'],
        $reqDecoded['beard']);
});

//set not applicable portrait
$app->post('/setNotApplicable', function (Request $request, Response $response) {
    require ('../model/imageModel.php');

    $reqDecoded = json_decode($request->getBody(), true);
    echo handleNotApplicationPortrait($reqDecoded['portraitUrl']);
});

//register user
$app->post('/registerUser', function (Request $request, Response $response) {
    require ('../model/userModel.php');

    $reqDecoded = json_decode($request->getBody(), true);
     echo registerUser($reqDecoded);
});

$app->run();
