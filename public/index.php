<?php

header('Access-Control-Allow-Origin: *');

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require_once __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require_once __DIR__ . '/../src/dependencies.php';

// Register middleware
require_once __DIR__ . '/../src/middleware.php';

// Register routes
require_once __DIR__ . '/../src/routes.php';

// Run app
$app->run();
