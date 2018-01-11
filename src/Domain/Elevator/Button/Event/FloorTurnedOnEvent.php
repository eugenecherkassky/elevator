<?php

namespace Domain\Elevator\Button\Event;

use Domain\Event\FloorEvent;

class FloorTurnedOnEvent extends FloorEvent
{
    /**
     * @var string
     */
    protected $name = 'app.elevator.floor.turned_on';
}
