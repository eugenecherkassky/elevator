<?php

namespace Domain\Event;

use Infrastructure\EventInterface as InfrastructureEventInterface;

abstract class Event implements EventInterface, InfrastructureEventInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * Return data
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * Return event name
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}