<?php

namespace Infrastructure;

use Domain\Controller;
use Domain\ControllerUseCases;
use Domain\Event\EventListenerInterface;
use Domain\Event\EventInterface as DomainEventInterface;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;

class EventHandler implements WampServerInterface, EventListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Controller[]
     */
    private $controllers = [];

    /**
     * @var Topic[]
     */
    private $subscribedTopics = [];

    /**
     * EventHandler constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->logger->info(sprintf('Server started'));
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic|string $topic
     * @param string $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $this->logger->info(sprintf('in: {%s}, {%s}', (string) $topic, json_encode($event)));

        try {
            $result = ControllerUseCases::run(
                (string) $topic,
                $event,
                $this,
                $this->getSessionId($conn),
                $this->logger,
                $this->getController($conn)
            );

            if ($result instanceof Controller) {
                $this->addController($conn, $result);
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param string $id
     * @param Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $this->logger->error(sprintf('call: {%s}, {%s}', (string) $topic, json_encode($params)));
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        if ($topic instanceof Topic) {
            $this->subscribedTopics[(string) $topic] = $topic;
        }

        $this->logger->info(sprintf('subscribe: {%s}', (string) $topic));
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic|string $topic
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->logger->info(sprintf('unSubscribe: {%s}', (string) $topic));
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->logger->info(sprintf('Connection established'));
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->removeController($conn);

        $this->logger->info(sprintf('Connection closed'));
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->removeController($conn);

        $this->logger->error($e->getMessage());
    }

    /**
     * @param DomainEventInterface $event
     * @param string $sessionId
     */
    public function publish(DomainEventInterface $event, $sessionId)
    {
        if ($event instanceof EventInterface) {

            $topicId = (string) $event;

            //send event's data to the client
            if (isset($this->subscribedTopics[$topicId])) {

                $data = json_encode($event->getData());

                $this->subscribedTopics[$topicId]->broadcast($data, [], [$sessionId]);

                $this->logger->info(sprintf('out: {%s}, {%s}', $topicId, $data));

            } else {

                $this->logger->error(sprintf('Client has not subscribed to %s channel', (string) $event));

            }
        }
    }

    /**
     * Add controller
     * @param ConnectionInterface $conn
     * @param Controller $controller
     */
    private function addController(ConnectionInterface $conn, Controller $controller)
    {
        $this->controllers[$this->getSessionId($conn)] = $controller;
    }

    /**
     * Get controller
     * @param ConnectionInterface $conn
     * @return Controller
     */
    private function getController(ConnectionInterface $conn)
    {
        $sessionId = $this->getSessionId($conn);

        if (!isset($this->controllers[$sessionId])) {
            return null;
        }

        return $this->controllers[$sessionId];
    }

    /**
     * Remove controller by connection
     * @param ConnectionInterface $conn
     */
    private function removeController(ConnectionInterface $conn)
    {
        unset($this->controllers[$this->getSessionId($conn)]);
    }

    /**
     * @param ConnectionInterface $conn
     * @return string
     */
    private function getSessionId(ConnectionInterface $conn)
    {
        //TODO: !!!some hack!!!
        return (string) $conn->WAMP->sessionId;
    }
}
