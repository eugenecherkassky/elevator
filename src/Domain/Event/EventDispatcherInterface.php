<?php

namespace Domain\Event;

interface EventDispatcherInterface
{
    /**
     * @param EventListenerInterface $listener
     */
    public function addListener(EventListenerInterface $listener);

    /**
     * @param EventInterface $event
     */
    public function dispatch(EventInterface $event);
}
