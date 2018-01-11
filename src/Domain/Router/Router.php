<?php

namespace Domain\Router;

abstract class Router
{
    /**
     * @param string $name
     * @return CitizenRouter|DictatorRouter
     */
    public static function create($name)
    {
        if ($name === 'citizen') {
            return new CitizenRouter();
        }

        if ($name === 'dictator') {
            return new DictatorRouter();
        }

        throw new \LogicException(sprintf('Router "%s" does n\'t available', $name));
    }

    /**
     * Return next floor
     * @param array $floors
     * @param array $elevatorFloors
     * @param int $currentFloor
     * @param int $weight
     * @param int $weightMax
     * @param int $weightMin
     * @param int $weightNonStop
     * @return int|null
     */
    abstract public function next(
        array $floors,
        array $elevatorFloors,
        $currentFloor,
        $weight,
        $weightMax,
        $weightMin,
        $weightNonStop
    );
}
