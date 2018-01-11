<?php

namespace Domain\Elevator\Event;

use Domain\Event\FloorEvent;

class DoorOpenedEvent extends FloorEvent
{
    protected $name = 'app.elevator.door.opened';
}
