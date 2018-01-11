<?php

namespace Domain\Elevator\Event;

use Domain\Event\Event;

class MovedEvent extends Event
{
    protected $name = 'app.elevator.moved';

    /**
     * @var int Offset, in pixel
     */
    protected $offset;

    /**
     * MovedEvent constructor.
     * @param int $offset
     */
    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'offset' => $this->offset
        ];
    }
}
