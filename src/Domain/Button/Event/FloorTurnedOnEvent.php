<?php

namespace Domain\Button\Event;

use Domain\Event\FloorEvent;

class FloorTurnedOnEvent extends FloorEvent
{
    /**
     * @var string
     */
    protected $name = 'app.controller.floor.turned_on';
}
