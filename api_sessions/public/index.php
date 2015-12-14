<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\YSNP\Guardian;

define("APP_ROOT", dirname(__DIR__));
chdir(APP_ROOT);

require "vendor/autoload.php";

session_name('phpconference');
session_start();

$app = new Application();
$app['debug'] = true;

$app->get('/', function() {
    return 'Hello world!';
});

$beers = [
    ['name' => 'Heineken', 'stars' => '5'],
    ['name' => 'Antarctica', 'stars' => '4'],
    ['name' => 'Cristal', 'stars' => '1']
];

$app->get('/beer', function() use ($beers) {
    return new JsonResponse($beers, 200);
});

$app->get('/beer/{id}', function (Request $request, $id) use ($beers) {
    foreach($beers as $beer) {
        if(strtolower($id) == strtolower($beer['name'])) {
            return new JsonResponse($beer, 200);
        }
    }

    return new JsonResponse('Beer not found', 404);
});

$app->post('/auth', function(Request $request) use ($app) {
    if($request->get('usuario') == 'admin' && $request->get('senha') == 'admin') {

        session_regenerate_id();

        $_SESSION["nome"] = 'Admin';

        return new JsonResponse('Login ok', 200);
    }

    return new JsonResponse('Invalid username or password', 404);
});

$app->get('/logout', function(Request $request) use ($app) {

    unset($_SESSION['nome']);

    session_regenerate_id();

    return new JsonResponse('Logout ok', 200);
});

$app->before(function(Request $request, Application $app) {
    $pathInfo = $request->getPathInfo();

    if($pathInfo != '/auth') {
        if(!isset($_SESSION['nome'])) {
            return new JsonResponse('Bad Request', 400);
        }
    }
});

$app->after(function(Request $request, Response $response, Application $app) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Expose-Headers', 'ClientToken');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, ClientToken');
});


$app->run();
