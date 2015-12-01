<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class Logistician_Controller {

    public function get_logistician($username, $password) {
        $lg = new ModelTemplate('Logistician');
        
        return $lg->get_single(array('username', 'password'), 
                array($username, $password));
    }

}
