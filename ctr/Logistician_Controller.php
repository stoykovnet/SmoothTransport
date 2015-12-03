<?php

require_once constant('ROOT') . 'ctr/SMSUser_Controller.php';
require_once constant('ROOT') . 'model/ModelTemplate.php';

class Logistician_Controller {

    /**
     * Retrieve a logistician by username and password.
     * @param string $username
     * @param string $password
     * @return Logistician
     */
    public function get_logistician_by_credentials($username, $password) {
        $logistician = new ModelTemplate('Logistician');
        $logistician = $logistician->get_single(array('username', 'password')
                , array($username, $password));

        if ($logistician) {
            // Add super class SMSUser fields to the Logistician instance.
            $smsUserCtr = new SMSUser_Controller();

            // It should have super class fields, otherwise delete the instance.
            if ($smsUser = $smsUserCtr->get_smsuser_by_id($logistician->id)) {
                $logistician->add_field('telephone', $smsUser->telephone);
            } else {
                $logistician = null;
            }
        }
        return $logistician;
    }

    /**
     * Retrieve a logistician by ID.
     * @param int|string $id
     * @return Logistician
     */
    public function get_logistician_by_id($id) {
        $logistician = new ModelTemplate('Logistician');
        $logistician = $logistician->get_single('id', $id);

        if ($logistician) {
            // Add super class SMSUser fields to the Logistician instance.
            $smsUserCtr = new SMSUser_Controller();

            // It should have super class fields, otherwise delete the instance.
            if ($smsUser = $smsUserCtr->get_smsuser_by_id($id)) {
                $logistician->add_field('telephone', $smsUser->telephone);
            } else {
                $logistician = null;
            }
        }
        return $logistician;
    }

}
