<?php

require __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

// create our http client (Guzzle)
$client = new Client('http://localhost:8000', array(
    'request.options' => array(
        'exceptions' => false,
    )
));

$nickname = 'ObjectOrienter'.rand(0, 999);
$data = array(
    'nickname' => $nickname,
    'avatarNumber' => 5,
    'tagLine' => 'a test dev!'
);

// 1) Create a programmer resource
$request = $client->post('/api/programmers', null, json_encode($data));
$response = $request->send();

// 2) GET a programmer resource
$request = $client->get('/api/programmers/'.$nickname);
$response = $request->send();

echo $response;
echo "\n\n";