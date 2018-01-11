<?php

namespace Domain\Event;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var EventListenerInterface[]
     */
    private $listeners = [];

    /**
     * @var string WebSocket session ID
     */
    private $sessionId;

    /**
     * EventDispatcher constructor.
     * @param string $sessionId
     */
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function addListener(EventListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(EventInterface $event)
    {
        foreach ($this->listeners as $listener) {
            $listener->publish($event, $this->sessionId);
        }
    }
}
