<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class Car_Controller {

    public function get_single_car($carId) {
        $car = new ModelTemplate('Car');
        return $car->get_single('id', $carId);
    }

}
