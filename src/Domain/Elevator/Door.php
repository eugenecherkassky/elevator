<?php

namespace Domain\Elevator;

use Domain\Event\EventEmitter;

/**
 * Door
 * Class Door
 * @package Domain\Elevator
 */
class Door extends EventEmitter
{
    /**
     * @var bool состояние двери
     */
    private $open = true;

    /**
     * Open door
     * @param int $floor
     */
    public function close($floor)
    {
        $this->open = false;

        $this->dispatcher->dispatch(new Event\DoorClosedEvent($floor));
    }

    /**
     * Open door
     * @param int $floor
     */
    public function open($floor)
    {
        $this->open = true;

        $this->dispatcher->dispatch(new Event\DoorOpenedEvent($floor));
    }

    /**
     * Is door open?
     * @return bool
     */
    public function isOpen()
    {
        return $this->open;
    }
}