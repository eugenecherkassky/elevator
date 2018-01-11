<?php

namespace Domain\Elevator;

use Domain\Elevator\Event;
use Domain\Event\EventDispatcherInterface;
use Domain\Event\EventEmitter;

/**
 * Elevator's weight sensor
 * Class WeightSensor
 * @package Domain\Elevator
 */
class WeightSensor extends EventEmitter
{
    /**
     * @var int min weight, less elevator can't move
     */
    private $min;

    /**
     * @var int max weight, over elevator can't move
     */
    private $max;

    /**
     * @var int current weight
     */
    private $value = 0;

    /**
     * WeightSensor constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param int $max
     * @param int $min
     */
    public function __construct(EventDispatcherInterface $dispatcher, $max, $min)
    {
        $this->assertWeight($max);
        $this->assertWeight($min);

        parent::__construct($dispatcher);

        $this->max = $max;
        $this->min = $min;
    }

    /**
     * Someone coming
     * @param int $weight
     */
    public function in($weight)
    {
        $this->assertWeight($weight);

        $this->changeValue($this->value + $weight);
    }

    /**
     * Someone exit
     * @param int|null $weight
     */
    public function out($weight)
    {
        $this->assertWeight($weight);

        if ($weight === 0) {
            $weight = $this->value;
        }

        $this->changeValue($this->value - $weight);
    }

    /**
     * Everything OK?
     * @return bool
     */
    public function isOk()
    {
        return !$this->isLess() && !$this->isOver();
    }

    /**
     * Is less then min
     * @return bool
     */
    public function isLess()
    {
        return $this->value < $this->min;
    }

    /**
     * Is over then max
     * @return bool
     */
    public function isOver()
    {
        return $this->value > $this->max;
    }

    /**
     * Return max weight
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Return min weight
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Return current weight
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Validate weight format
     * @param int|null $weight
     */
    protected function assertWeight($weight)
    {
        if (!is_null($weight) && !is_int($weight)) {
            throw new \LogicException('Weight should be integer type');
        };
    }

    /**
     * Change weight
     * @param int $value
     */
    protected function changeValue($value)
    {
        $this->value = $value;

        if ($this->isLess()) {

            $this->dispatcher->dispatch(new Event\WeightSensorLessEvent($this->value));

        } elseif ($this->isOver()) {

            $this->dispatcher->dispatch(new Event\WeightSensorOverEvent($this->value));

        } else {

            $this->dispatcher->dispatch(new Event\WeightSensorChangedEvent($this->value));

        }
    }
}
