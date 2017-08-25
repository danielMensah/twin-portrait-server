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

$app->get('/', function (Request $request, Response $response) {
    getConnection();
});

$app->get('/getPortrait', function (Request $request, Response $response) {
    require '../model/imageModel.php';

    $randomPortrait = json_encode(array(
        'portraitURL' => getRandomPortrait()
    ));

    return $randomPortrait;
});

$app->get('/portrait', function (Request $request, Response $response) {
    require '../model/formsModel.php';
    echo uploadPortraitForm();
});

$app->post('/uploadPortrait', function (Request $request, Response $response) {
    require('../model/imageModel.php');

    $uploadedFiles = $request->getUploadedFiles();

    // handle single input with multiple file uploads
    foreach ($uploadedFiles['portrait'] as $uploadedFile) {
        if (!empty($uploadedFile)) {
            $filename = uploadImage($uploadedFile);
            $response->write('uploaded ' . $filename . '<br/>');
        }
    }
}

$app->run();
