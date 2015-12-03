<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class Truck_Controller {

    /**
     * Retrieve all trucks.
     * @return array(Truck)
     */
    public function get_all_trucks() {
        $truck = new ModelTemplate('Truck');
        return $truck->get_all();
    }
    
    /**
     * Retrieve a truck by ID.
     * @param int|string $truckId
     * @return Truck
     */
    public function get_truck_by_id($truckId) {
        $truck = new ModelTemplate('Truck');
        return $truck->get_single('id', $truckId);
    }

    /**
     * Retrieve the truck that the selected truck driver is using right now.
     * Select truck driver with ID.
     * @param int|string $truckDriverId
     * @return Truck
     */
    public function get_truck_driver_truck($truckDriverId) {
        // The association between TruckDriver and Truck.
        $truckDriverAndTruck = new ModelTemplate('TruckDriverAndTruck');
        $truck = new ModelTemplate('Truck');

        // Driver must have a truck.
        if ($association = $truckDriverAndTruck->
                get_single('truck_driver_id', $truckDriverId)) {
            return $this->get_truck_by_id($association->truck_id);
        }
        return null;
    }

}
