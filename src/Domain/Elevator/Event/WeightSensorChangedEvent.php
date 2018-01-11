<?php

namespace Domain\Elevator\Event;

use Domain\Event\Event;

class WeightSensorChangedEvent extends Event
{
    protected $name = 'app.elevator.weight_sensor.changed';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getData()
    {
        return [
            'value' => $this->value
        ];
    }
}
