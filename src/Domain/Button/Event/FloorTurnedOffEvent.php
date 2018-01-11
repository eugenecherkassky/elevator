<?php

namespace Domain\Button\Event;

use Domain\Event\FloorEvent;

class FloorTurnedOffEvent extends FloorEvent
{
    protected $name = 'app.controller.floor.turned_off';
}
