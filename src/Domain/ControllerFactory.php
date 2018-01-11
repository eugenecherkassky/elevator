<?php

namespace Domain;

use Domain\Event\EventDispatcher;
use Domain\Event\EventListenerInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller factory
 * Class ControllerFactory
 * @package Domain
 */
class ControllerFactory
{
    /**
     * @param array $data
     * @param EventListenerInterface $listener
     * @param string $sessionId
     * @return Controller
     */
    public static function create(array $data, EventListenerInterface $listener, $sessionId, LoggerInterface $logger)
    {
        $dispatcher = new EventDispatcher($sessionId);
        $dispatcher->addListener($listener);

        //TODO: !!!raw client's data, should be validated before use!!!
        $controller = new Controller((int) $data['floors'], $data['router'], $dispatcher, $logger);

        $controller->addElevator((int) $data['weightMax'], (int) $data['weightMin'], (int) $data['weightNonStop']);

        return $controller;
    }
}
