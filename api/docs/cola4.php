<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

$app->get('/beer', function() use ($beers) {
    return new JsonResponse($beers, 200);
});

$app->get('/beer/{id}', function ($id) use ($beers) {
    foreach($beers as $beer) {
        if(strtolower($id) == strtolower($beer['name'])) {
            return new JsonResponse($beer, 200);
        }
    }

    return new JsonResponse('Beer not found', 404);
});

$app->post('/auth', function(Request $request) use ($authorizedAppTokens) {

    $appToken = $request->headers->get('AppToken');

    if (is_null($appToken) || !in_array($appToken, $authorizedAppTokens)) {
        return new JsonResponse('Bad Request', 400);
    }

    if($request->get('usuario') == 'admin' && $request->get('senha') == 'admin') {
        $clientToken = \App\Generators\Sha2TokenGenerator::generate();

        $responseHeaders = [
            'ClientToken' => $clientToken
        ];

        return new JsonResponse('Login ok', 200, $responseHeaders);
    }

    return new JsonResponse('Invalid username or password', 404);
});


$app->run();
