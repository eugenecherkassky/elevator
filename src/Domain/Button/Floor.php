<?php

namespace Domain\Button;

use Domain\Event\EventDispatcherInterface;
use Domain\Event\EventEmitter;

/**
 * Floor button
 * Class Floor
 * @package Domain
 */
class Floor extends EventEmitter
{
    /**
     * @var bool state
     */
    private $on = false;

    /**
     * @var int
     */
    private $floor;

    /**
     * Floor constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param $floor
     */
    public function __construct(EventDispatcherInterface $dispatcher, $floor)
    {
        parent::__construct($dispatcher);

        $this->floor = $floor;
    }

    public function isOn()
    {
        return $this->on;
    }

    public function off()
    {
        $this->on = false;

        $this->dispatcher->dispatch(new Event\FloorTurnedOffEvent($this->floor));
    }

    public function on()
    {
        $this->on = true;

        $this->dispatcher->dispatch(new Event\FloorTurnedOnEvent($this->floor));
    }
}
