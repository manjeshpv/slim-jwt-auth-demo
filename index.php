<?php
/**
 * Created by PhpStorm.
 * User: ManjeshV
 * Date: 10/24/2015
 * Time: 10:58 AM
 */


require 'vendor/autoload.php';
use \Slim\Middleware\JwtAuthentication;
use \Slim\Middleware\JwtAuthentication\RequestPathRule;
use \Slim\Middleware\HttpBasicAuthentication;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/* Setup Slim */
$app = new \Slim\App();
$app->add(new JwtAuthentication([
    "secret" => 'secret',
    "rules" => [
        new RequestPathRule([
            "path" => "/",
            "passthrough" => ["/token"]
        ])
    ],
    "callback" => function ($request,$response,$args) use ($app) {
        $app->jwt = $args["decoded"];
    }
]));

$app->add(new HttpBasicAuthentication([
    "path" => "/token",
    "users" => [
        "user" => "password"
    ]
]));

$app->post("/secured", function ($request,$response,$args) use ($app) {
    $data = array("message"=>" success","data"=>"Secured Content");
    echo json_encode($data);
    return $response;
});

$app->post("/public", function ($request,$response,$args) use ($app) {
    $data = array("message"=>" success","data"=>"Public Content");
    echo json_encode($data);
    return $response;
});



$app->get("/token", function ($request,$response,$args) use ($app) {
    /* Here generate and return JWT to the client. */
    $signer = new Sha256();

    $token = (new Builder())->setIssuer('http://manjeshpv.com') // Configures the issuer (iss claim)
    ->setAudience('mobileApp') // Configures the audience (aud claim)
    ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
    ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
    ->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
    ->setExpiration(time() + 3600) // Configures the expiration time of the token (exp claim)
    ->set('user_id', 1) // Configures a new claim, called "uid"
    ->set('scope', array('read','write','delete')) // Configures a new claim, called "uid"
    ->sign($signer, 'secret') // creates a signature using "testing" as key
    ->getToken(); // Retrieves the generated token

        // Token is eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjRmMWcyM2ExMmFhIn0.eyJpc3MiOiJodHRwOlwvXC9tYW5qZXNocHYuY29tIiwiYXVkIjoibW9iaWxlQXBwIiwianRpIjoiNGYxZzIzYTEyYWEiLCJpYXQiOjE0NDU2NjY4NjgsIm5iZiI6MTQ0NTY2NjkyOCwiZXhwIjoxNDQ1NjcwNDY4LCJ1c2VyX2lkIjoxLCJzY29wZSI6WyJyZWFkIiwid3JpdGUiLCJkZWxldGUiXX0.9E2cESoG8xYRQWdA6zlcrHTBAN1qjWu66Du0q6vy3uI
// Header
//    {
//          "typ": "JWT",
//          "alg": "HS256",
//          "jti": "4f1g23a12aa"
//    }
// Pay Load
//{
//  "iss": "http://manjeshpv.com",
//  "aud": "mobileApp",
//  "jti": "4f1g23a12aa",
//  "iat": 1445666868,
//  "nbf": 1445666928,
//  "exp": 1445670468,
//  "user_id": 1,
//  "scope": [
//        "read",
//        "write",
//        "delete"
//    ]
//}

//        var_dump($token->verify($signer, 'testing 1')); // false, because the key is different
//        var_dump($token->verify($signer, 'testing')); // true, because the key is the same
    $data = array("message"=>"token success","access_token"=>"".$token);
    echo json_encode($data);
});


$app->run();
