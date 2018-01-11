<?php

namespace Domain;

use Domain\Event\EventListenerInterface;
use Psr\Log\LoggerInterface;

//TODO: class name not suitable, thinking...
class ControllerUseCases
{
    /**
     * @param LoggerInterface $logger
     * @param $eventName
     * @param array $eventData
     * @param EventListenerInterface $listener
     * @param $sessionId
     * @param Controller|null $controller
     * @return Controller|null
     */
    public static function run(
        $eventName,
        array $eventData,
        EventListenerInterface $listener,
        $sessionId,
        LoggerInterface $logger,
        Controller $controller = null
    ) {
        $logger->info(sprintf('Start handle: %s, %s', $eventName, json_encode($eventData)));

        //TODO: !!!raw client's data, should be validated before use!!!
        switch ($eventName) {
            case 'app.controller.create':
                $result = ControllerFactory::create($eventData, $listener, $sessionId, $logger);
                break;
            case 'app.controller.floor.press':
                $result = $controller->elevatorCall((int) $eventData['floor']);
                break;
            case 'app.elevator.floor.press':
                $result = $controller->elevatorFloorCall((int) $eventData['floor']);
                break;
            case 'app.elevator.open.press':
                $result = $controller->elevatorOpen();
                break;
            case 'app.elevator.close.press':
                $result = $controller->elevatorClose();
                break;
            case 'app.elevator.in':
                $result = $controller->elevatorIn((int) $eventData['weight']);
                break;
            case 'app.elevator.out':
                $result = $controller->elevatorOut((int) $eventData['weight']);
                break;
            default:
                throw new \LogicException(sprintf('Can\'t resolve controller handler'));
        }

        $logger->info(sprintf('Finished handle: %s', $eventName));

        return $result;
    }
}
