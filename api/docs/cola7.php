<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

$authorizedAppTokens = [
    '2f82ed9258510da0e0d89630c1dc797029d441a192a1fc6e0520adee52497d40'
];

$authorizedClientToken = [
    'ad6761548a14908eb22a25233f9b1e206acd2a109cf8c95adde25d8065ef89ef'
];

$app->get('/beer', function() use ($beers) {
    return new JsonResponse($beers, 200);
});

$app->get('/beer/{id}', function (Request $request, $id) use ($beers, $authorizedClientToken) {
    $clientToken = $request->headers->get('ClientToken');

    if (is_null($clientToken) || !in_array($clientToken, $authorizedClientToken)) {
        return new JsonResponse('Bad Request', 400);
    }

    foreach($beers as $beer) {
        if(strtolower($id) == strtolower($beer['name'])) {
            return new JsonResponse($beer, 200);
        }
    }

    return new JsonResponse('Beer not found', 404);
});

$app->post('/auth', function(Request $request) use ($app) {

    if($request->get('usuario') == 'admin' && $request->get('senha') == 'admin') {
        $clientToken = \App\Generators\Sha2TokenGenerator::generate();

        $app['ClientToken'] = $clientToken;

        return new JsonResponse('Login ok', 200);
    }

    return new JsonResponse('Invalid username or password', 404);
});

$app->before(function(Request $request, Application $app) use ($authorizedAppTokens, $authorizedClientToken){
    $pathInfo = $request->getPathInfo();

    $appToken = $request->headers->get('AppToken');

    $isValidToken = false;

    if($pathInfo == '/auth') {
        if(in_array($appToken, $authorizedAppTokens)) {
            $app['AppToken'] = $appToken;

            $isValidToken = true;
        }
    } else {
        $clientToken = $request->headers->get('ClientToken');

        if(in_array($appToken, $authorizedAppTokens) && in_array($clientToken, $authorizedClientToken)) {
            $app['ClientToken'] = $clientToken;

            $isValidToken = true;
        }
    }

    if (!$isValidToken) {
        return new JsonResponse('Bad Request', 400);
    }
});

$app->after(function(Request $request, Response $response, Application $app) {
    if(isset($app['AppToken'])) {
        $response->headers->set('AppToken', $app['AppToken']);
    }
    if(isset($app['ClientToken'])) {
        $response->headers->set('ClientToken', $app['ClientToken']);
    }

    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Expose-Headers', 'AppToken');
    $response->headers->set('Access-Control-Expose-Headers', 'ClientToken');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, ClientToken, AppToken');
});


$app->run();
