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

$beers = [
    ['name' => 'Heineken', 'stars' => '5'],
    ['name' => 'Antarctica', 'stars' => '4'],
    ['name' => 'Cristal', 'stars' => '1']
];

$appToken = [
  'c3de5b07ce5e26a436a90b7b9d756f8865bb1464f781fbcec964ca5f1d53953d'
];

$clientToken = [
  '89e0a65933566fa6c14b6cd8f02df5f150764b79e8a9da45f6f153b0b52c93eb'
];

$openRoutes = [
  '/auth'
];

$app->get('/', function() {
    return 'Hello world!';
});

$app->get('/beer', function(Request $request) use ($beers) {
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

$app->post('/auth', function(Request $request) use ($app, $appToken) {
    if ($request->get('usuario') == 'admin' && $request->get('senha') == 'admin') {
        $clientToken = \App\Generators\Sha2TokenGenerator::generate();

        $app['ClientToken'] = $clientToken;

        return new JsonResponse('Login ok', 200);
    }

    return new JsonResponse('Invalid username or password', 404);
});


$app->before(function(Request $request, Application $app) use ($appToken) {
  $token = $request->headers->get('AppToken');

  if (!in_array($token, $appToken)) {
      return new JsonResponse('Access denied', 400);
  }
});

$app->before(function(Request $request, Application $app) use ($clientToken, $openRoutes) {

  $route = $request->getPathInfo();

  if(in_array($route, $openRoutes)) {
    return;
  }

  $token = $request->headers->get('ClientToken');

  if (!in_array($token, $clientToken)) {
      return new JsonResponse('Access denied', 400);
  }

  $app['ClientToken'] = $token;

});

$app->after(function(Request $request, Response $response, Application $app) {

  $response->headers->set('ClientToken', $app['ClientToken']);

  $response->headers->set('Access-Control-Allow-Origin', '*');
  $response->headers->set('Access-Control-Expose-Headers', 'AppToken');
  $response->headers->set('Access-Control-Expose-Headers', 'ClientToken');
  $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, ClientToken, AppToken');

});
$app->run();
