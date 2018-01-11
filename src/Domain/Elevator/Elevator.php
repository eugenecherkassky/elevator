<?php

namespace Domain\Elevator;

use Domain\Event\EventDispatcherInterface;
use Domain\Event\EventEmitter;

/**
 * Elevator
 * Class Elevator
 * @package Domain\Elevator
 */
class Elevator extends EventEmitter
{
    /**
     * @var int current
     */
    private $floor;

    /**
     * @var Door
     */
    private $door;

    /**
     * @var WeightSensor
     */
    private $weightSensor;

    /**
     * @var int
     */
    private $weightNonStop;

    /**
     * @var Button\Floor[]
     */
    private $floorButtons = [];

    /**
     * Elevator constructor.
     * @param int $floors
     * @param int $weightMax
     * @param int $weightMin
     * @param int $weightNonStop
     * @param int $floor
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        $floors,
        $weightMax,
        $weightMin,
        $weightNonStop,
        $floor,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($dispatcher);

        for ($i = 0; $i<$floors; $i++) {
            $this->floorButtons[] = new Button\Floor($dispatcher, $i);
        }

        $this->weightSensor  = new WeightSensor($dispatcher, $weightMax, $weightMin);
        $this->weightNonStop = $weightNonStop;
        $this->door          = new Door($dispatcher);
        $this->floor         = $floor;
    }

    /**
     * Close elevator's door
     */
    public function close()
    {
        if ($this->door->isOpen() &&
            ($this->isEmpty() || ($this->weightSensor->isOk() && count($this->getChoseFloors())))
        ) {

            sleep(0.5);

            $this->door->close($this->floor);
        }
    }

    /**
     * Open elevator's door
     */
    public function open()
    {
        $this->door->open($this->floor);
    }

    /**
     * Is door open?
     * @return bool
     */
    public function isOpen()
    {
        return $this->door->isOpen();
    }

    /**
     * Return Elevator is empty?
     * @return bool
     */
    public function isEmpty()
    {
        return $this->weightSensor->isLess();
    }

    /**
     * Press floor button
     * @param int $floor
     */
    public function floorCall($floor)
    {
        if ($this->weightSensor->isOk()) {

            $this->floorButtons[$floor]->on();

            $this->close();

        } else {
            $this->floorButtons[$floor]->off();
        }
    }

    /**
     * Move elevator
     * @param $floor
     * @return bool
     */
    public function move($floor)
    {
        $this->beforeMoveBehavior();

        $cycles = (int) abs($this->floor - $floor);
        $offset = $this->floor - $floor > 0 ? 1 : -1;

        for ($i = $cycles - 1; $i >= 0; $i--) {
            sleep(0.5);

            $this->dispatcher->dispatch(new Event\MovedEvent($offset * 100));

            $this->changeFloor($this->floor - $offset);
        }

        $this->afterMoveBehavior();

        return true;
    }

    /**
     * Someone coming
     * @param int $weight
     */
    public function in($weight)
    {
        $this->weightSensor->in($weight);
    }

    /**
     * Someone exit
     * @param int|null $weight
     */
    public function out($weight = null)
    {
        $this->weightSensor->out($weight);
    }

    /**
     * Return current floor
     * @return int
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * Return current weight
     * @return int
     */
    public function getWeight()
    {
        return $this->weightSensor->getValue();
    }

    /**
     * Return non stop weight
     * @return int
     */
    public function getWeightNonStop()
    {
        return $this->weightNonStop;
    }

    /**
     * Return max weight
     * @return int
     */
    public function getWeightMax()
    {
        return $this->weightSensor->getMax();
    }

    /**
     * Return min weight
     * @return int
     */
    public function getWeightMin()
    {
        return $this->weightSensor->getMin();
    }

    /**
     * Is weight ok?
     * @return bool
     */
    public function isWeightOk()
    {
        return $this->weightSensor->isOk();
    }

    /**
     * Return floors chose by passenger(s)
     * @return int[]
     */
    public function getChoseFloors()
    {
        return array_keys(
            array_filter($this->floorButtons, function(Button\Floor $floor){
                return $floor->isOn();
            })
        );
    }

    /**
     * Change floor
     * @param int $floor
     */
    protected function changeFloor($floor)
    {
        $this->floor = $floor;

        $this->dispatcher->dispatch(new Event\FloorChangedEvent($this->floor));
    }

    /**
     * Before move behavior, template method
     */
    protected function beforeMoveBehavior()
    {

    }

    /**
     * After move behavior, template method
     */
    protected function afterMoveBehavior()
    {
        sleep(0.5);

        $this->floorButtons[$this->floor]->off();

        sleep(0.5);

        $this->door->open($this->floor);
    }
}