<?php

namespace Domain\Elevator\Event;

use Domain\Event\FloorEvent;

class FloorChangedEvent extends FloorEvent
{
    protected $name = 'app.elevator.floor_changed';
}
