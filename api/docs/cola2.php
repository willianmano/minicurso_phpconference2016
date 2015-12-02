<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
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
    return new Symfony\Component\HttpFoundation\JsonResponse($beers, 200);
});

$app->get('/beer/{id}', function ($id) use ($beers) {
    foreach($beers as $beer) {
        if(strtolower($id) == strtolower($beer['name'])) {
            return new Symfony\Component\HttpFoundation\JsonResponse($beer, 200);
        }
    }

    return new Symfony\Component\HttpFoundation\JsonResponse('Beer not found', 404);
});

$app->post('/auth', function(\Symfony\Component\HttpFoundation\Request $request){

    if($request->get('usuario') == 'admin' && $request->get('senha') == 'admin') {
        return new Symfony\Component\HttpFoundation\JsonResponse('Login ok', 200);
    }

    return new Symfony\Component\HttpFoundation\JsonResponse('Invalid username or password', 404);
});


$app->run();
