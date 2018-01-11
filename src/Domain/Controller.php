<?php

namespace Domain;

use Domain\Button\Floor;
use Domain\Elevator\Elevator;
use Domain\Event\EventDispatcherInterface;
use Domain\Event\EventInterface;
use Domain\Event\EventListenerInterface;
use Domain\Event\MoveEventInterface;
use Domain\Router\Router;
use Psr\Log\LoggerInterface;

/**
 * Elevator's controller
 * Class ControlPanel
 * @package Domain
 */
class Controller implements EventListenerInterface
{
    /**
     * @var Elevator
     */
    private $elevator;

    /**
     * @var Floor[]
     */
    private $floorButtons = [];

    /**
     * @var Router
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Controller constructor.
     * @param int $floors
     * @param string $router
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
     */
    public function __construct($floors, $router, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;

        $this->dispatcher->addListener($this);

        $this->logger = $logger;

        for ($i=0; $i<$floors; $i++) {
            $this->floorButtons[] = new Floor($dispatcher, $i);
        }

        $this->router = Router::create($router);
    }

    /**
     * Add elevator
     * @param int $weightMax
     * @param int $weightMin
     * @param int $weightNonStop
     * @param int $floor
     */
    public function addElevator($weightMax, $weightMin, $weightNonStop, $floor = 0)
    {
        $this->elevator = new Elevator(
            count($this->floorButtons),
            $weightMax,
            $weightMin,
            $weightNonStop,
            $floor,
            $this->dispatcher
        );
    }

    /**
     * Passenger call an elevator
     * @param int $floor
     * @return null
     */
    public function elevatorCall($floor)
    {
        //block button if elevator on the floor and door is open
        if ($this->elevator->getFloor() === $floor && $this->elevator->isOpen()) {

            $this->floorButtons[$floor]->off();

        } else {

            $this->floorButtons[$floor]->on();

            if ($this->elevator->isEmpty()) {
                $this->elevator->close();
            }
        }
    }

    /**
     * Passenger chose the floor
     * @param int $floor
     * @return null
     */
    public function elevatorFloorCall($floor)
    {
        $this->elevator->floorCall($floor);
    }

    /**
     * Open elevator's door
     * @return null
     */
    public function elevatorOpen()
    {
        $this->elevator->open();
    }

    /**
     * Close elevator's door
     * @return null
     */
    public function elevatorClose()
    {
        $this->elevator->close();
    }

    /**
     * Someone coming
     * @param int $weight
     * @return null
     */
    public function elevatorIn($weight)
    {
        $this->elevator->in($weight);
    }

    /**
     * Someone exit
     * @param int $weight
     * @return null
     */
    public function elevatorOut($weight)
    {
        $this->elevator->out($weight);
    }

    /**
     * {@inheritdoc}
     */
    public function publish(EventInterface $event, $sessionId)
    {
        if ($event instanceof MoveEventInterface) {

            //get next floor from router
            $floor = $this->router->next(
                $this->getChoseFloors(),
                $this->elevator->getChoseFloors(),
                $this->elevator->getFloor(),
                $this->elevator->getWeight(),
                $this->elevator->getWeightMax(),
                $this->elevator->getWeightMin(),
                $this->elevator->getWeightNonStop()
            );

            $this->logger->info(
                sprintf(
                    'Router - result %s, params:  %s, %s, %s, %s, %s, %s, %s',
                    $floor,
                    json_encode($this->getChoseFloors()),
                    json_encode($this->elevator->getChoseFloors()),
                    $this->elevator->getFloor(),
                    $this->elevator->getWeight(),
                    $this->elevator->getWeightMax(),
                    $this->elevator->getWeightMin(),
                    $this->elevator->getWeightNonStop()
                )
            );

            if (!is_null($floor)) {
                $this->elevator->move($floor);

                $this->floorButtons[$floor]->off();
            }
        }
    }

    /**
     * Return floors chose by passenger(s)
     * @return int[]
     */
    protected function getChoseFloors()
    {
        return array_keys(
            array_filter($this->floorButtons, function(Button\Floor $floor){
                return $floor->isOn();
            })
        );
    }
}
