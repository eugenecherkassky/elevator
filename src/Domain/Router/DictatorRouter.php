<?php

namespace Domain\Router;

class DictatorRouter extends Router
{
    /**
     * {@inheritdoc}
     */
    public function next(
        array $floors,
        array $elevatorFloors,
        $currentFloor,
        $weight,
        $weightMax,
        $weightMin,
        $weightNonStop
    ) {
        $next = null;

        if ($weight > $weightMin) {

            if ($weight > $weightMax) {
                return null;
            }

            //find next floor
            for ($i = count($elevatorFloors)-1; $i>=0; $i--) {
                if ($currentFloor < $elevatorFloors[$i]) {
                    $next = $elevatorFloors[$i];
                } elseif (is_null($next)) {
                    return $elevatorFloors[$i];
                }
            }
        } else {
            //if elevator empty, sent it too the highest chose floor
            return array_pop($floors);
        }

        return $next;
    }
}
