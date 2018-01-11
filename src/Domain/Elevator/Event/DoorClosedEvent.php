<?php

namespace Domain\Elevator\Event;

use Domain\Event\FloorEvent;
use Domain\Event\MoveEventInterface;

class DoorClosedEvent extends FloorEvent implements MoveEventInterface
{
    protected $name = 'app.elevator.door.closed';
}
