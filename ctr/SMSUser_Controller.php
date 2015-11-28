<?php
require_once constant('ROOT') . 'model/ModelTemplate.php';
class SMSUser_Controller {
    public function get_smsuser_by_telephone($telephone) {
        $su = new ModelTemplate('SMSUser');
        return $su->get_single('telephone', $telephone);
    }
}