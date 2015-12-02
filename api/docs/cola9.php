<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\YSNP\Guardian;

define("APP_ROOT", dirname(__DIR__));
chdir(APP_ROOT);

require "vendor/autoload.php";

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
        $clientToken = \App\Generators\JWTTokenGenerator::generate();

        $app['ClientToken'] = strval($clientToken);

        return new JsonResponse('Login ok', 200);
    }

    return new JsonResponse('Invalid username or password', 404);
});

$app->before(function(Request $request, Application $app) {
    $pathInfo = $request->getPathInfo();

    $guard = new Guardian();

    if($pathInfo != '/auth') {
        $clientToken = $request->headers->get('ClientToken');

        if($guard->validateJwtToken($clientToken)){
            $app['ClientToken'] = $clientToken;
        } else {
            return new JsonResponse('Bad Request', 400);
        }
    }
});

$app->after(function(Request $request, Response $response, Application $app) {
    if(isset($app['ClientToken'])) {
        $response->headers->set('ClientToken', $app['ClientToken']);
    }

    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Expose-Headers', 'ClientToken');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, ClientToken');
});


$app->run();

