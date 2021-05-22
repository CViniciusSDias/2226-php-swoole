<?php

use Swoole\Http\{Request, Response, Server};

$servidor = new Server('0.0.0.0', 8080);

$servidor->on('request', function (Request $request, Response $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end(print_r($request->header, true));
});

$servidor->start();
