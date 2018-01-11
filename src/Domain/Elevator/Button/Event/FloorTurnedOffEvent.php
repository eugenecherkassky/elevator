<?php

namespace Domain\Elevator\Button\Event;

use Domain\Event\FloorEvent;

class FloorTurnedOffEvent extends FloorEvent
{
    protected $name = 'app.elevator.floor.turned_off';
}
