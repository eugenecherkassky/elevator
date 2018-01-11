<?php

namespace Domain\Router;

class CitizenRouter extends Router
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

            //pickup passengers if elevator is not full
            if ($weight < $weightNonStop) {

                //get floors bottom then current
                $floors = array_filter($floors, function($floor) use ($currentFloor) {
                    return $floor < $currentFloor;
                });

                $elevatorFloors = array_unique(array_merge($elevatorFloors, $floors));

                sort($elevatorFloors);
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
