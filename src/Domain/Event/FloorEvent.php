<?php

namespace Domain\Event;

abstract class FloorEvent extends Event
{
    /**
     * @var int
     */
    protected $floor;

    public function __construct($floor)
    {
        $this->floor = $floor;
    }

    public function getData()
    {
        return [
            'floor' => $this->floor
        ];
    }
}