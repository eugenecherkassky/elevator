<?php

namespace Domain\Elevator\Event;

class WeightSensorLessEvent extends WeightSensorChangedEvent
{
    protected $name = 'app.elevator.weight_sensor.less';
}
