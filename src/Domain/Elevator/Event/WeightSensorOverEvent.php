<?php

namespace Domain\Elevator\Event;

class WeightSensorOverEvent extends WeightSensorChangedEvent
{
    protected $name = 'app.elevator.weight_sensor.over';
}
