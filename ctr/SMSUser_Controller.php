<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class SMSUser_Controller {

    /**
     * Retrieve an SMSUser by ID.
     * @param int|string $id
     * @return SMSUser
     */
    public function get_smsuser_by_id($id) {
        $smsUser = new ModelTemplate('SMSUser');
        return $smsUser->get_single('id', $id);
    }

    /**
     * Retrieve an SMSUser by a telephone number.
     * @param string $telephone
     * @return SMSUser
     */
    public function get_smsuser_by_telephone($telephone) {
        $smsUser = new ModelTemplate('SMSUser');
        return $smsUser->get_single('telephone', $telephone);
    }

}
