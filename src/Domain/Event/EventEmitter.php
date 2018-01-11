<?php

namespace Domain\Event;

abstract class EventEmitter
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * EventEmitter constructor.
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}
