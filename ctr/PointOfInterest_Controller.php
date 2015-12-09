<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class PointOfInterest_Controller {
    public function get_single_point_of_interest($poiId) {
        $poi = new ModelTemplate('PointOfInterest');
        return $poi->get_single('id', $poiId);
    }
    
    public function get_all($where = null, $value = null) {
        $poi = new ModelTemplate('PointOfInterest');
        return $poi->get_all($where, $value);
    }
}
