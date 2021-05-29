<?php

require __DIR__ . '/../vendor/autoload.php';

ini_set('error_reporting', E_ALL);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\{Request, Response, Server};

Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$servidor = new Server('0.0.0.0', 8080);
$rotas = require __DIR__ . '/../config/rotas.php';
/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/dependencias.php';

$servidor->on(
    'request',
    function (Request $request, Response $response) use ($rotas, $container) {
        $path = $request->server['path_info'] ?? '/';

        if ($path === '/') {
            $response->redirect('/listar-cursos');
            return;
        }

        if (!isset($rotas[$path])) {
            $response->setStatusCode(404);
            return;
        }

        /*session_start();
        if (!isset($_SESSION['logado']) && stripos($path, 'login') === false) {
            $_SESSION['tipo_mensagem'] = 'danger';
            $_SESSION['mensagem_flash'] = 'Você não está logado';
            header('Location: /login');
            exit();
        }*/

        $controllerClass = $rotas[$path];

        $psr17Factory = new Psr17Factory();

        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        $serverRequest = $creator->fromGlobals();

        /** @var RequestHandlerInterface $controllerInstance */
        $controllerInstance = $container->get($controllerClass);

        $responsePsr7 = $controllerInstance->handle($serverRequest);

        foreach ($responsePsr7->getHeaders() as $header => $valores) {
            foreach ($valores as $value) {
                $response->header($header, $value);
            }
        }
        $response->end($responsePsr7->getBody());
    }
);

$servidor->start();
