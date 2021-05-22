<?php

use Swoole\Http\Server;

$servidor = new Server('0.0.0.0', 8080);

$servidor->on('request', function ($request, $response) {
    $response->end('Recebi a requisição');
});

$servidor->start();
