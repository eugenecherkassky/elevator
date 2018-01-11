<?php

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

require dirname(__DIR__) . '/vendor/autoload.php';

$logger = new ConsoleLogger(
    new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG)
);

$loop = React\EventLoop\Factory::create();

$ws = new React\Socket\Server($loop);
$ws->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect

new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                new \Infrastructure\EventHandler($logger)
            )
        )
    ),
    $ws
);

$loop->run();
