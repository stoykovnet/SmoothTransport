<?php

require_once constant('ROOT') . 'ctr/SMSUser_Controller.php';
require_once constant('ROOT') . 'model/ModelTemplate.php';

class TruckDriver_Controller {

    /**
     * Get the total number of all present truck drivers.
     * @return int
     */
    public function count_truck_drivers() {
        $truckDriver = new ModelTemplate('TruckDriver');
        return $truckDriver->count();
    }
    
    /**
     * Retrieve all truck drivers.
     * @return array(TruckDriver)
     */
    public function get_all_truck_drivers() {
        $truckDriver = new ModelTemplate('TruckDriver');
        $truckDrivers = $truckDriver->get_all();

        if ($truckDrivers) {
            // Add super class SMSUser fields to each TruckDriver instance.
            foreach ($truckDrivers as $key => &$td) {
                $smsUserCtr = new SMSUser_Controller();

                // It should have super class fields, otherwise delete the instance.
                if ($smsUser = $smsUserCtr->get_smsuser_by_id($td->id)) {
                    $td->add_field('telephone', $smsUser->telephone);
                } else {
                    unset($truckDrivers[$key]);
                }
            }
        }
        return $truckDrivers;
    }

    /**
     * Retrieve single truck driver, specified by ID.
     * @param int|string $id
     * @return TruckDriver
     */
    public function get_truck_driver_by_id($id) {
        $truckDriver = new ModelTemplate('TruckDriver');
        $truckDriver = $truckDriver->get_single('id', $id);

        if ($truckDriver) {
            // Add super class SMSUser fields to the TruckDriver instance.
            $smsUserCtr = new SMSUser_Controller();

            // It should have super class fields, otherwise delete the instance.
            if ($smsUser = $smsUserCtr->get_smsuser_by_id($id)) {
                $truckDriver->add_field('telephone', $smsUser->telephone);
            } else {
                $truckDriver = null;
            }
        }
        return $truckDriver;
    }

}
