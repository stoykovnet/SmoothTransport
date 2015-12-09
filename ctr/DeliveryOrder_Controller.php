<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';
require_once constant('ROOT') . 'ctr/Car_Controller.php';
require_once constant('ROOT') . 'ctr/PointOfInterest_Controller.php';

class DeliveryOrder_Controller {
    
    public function get_truck_delivery_order($truckId) {
        $order = new ModelTemplate('DeliveryOrder');
        $carCtr = new Car_Controller();
        $pointOfInterestCtr = new PointOfInterest_Controller();
        
        $order = $order->get_single('truck_id', $truckId);
        $order->car_id = $carCtr->get_single_car($order->car_id);
        $order->manufacturer_id = $pointOfInterestCtr->get_single_point_of_interest($order->manufacturer_id);
        $order->shop_id = $pointOfInterestCtr->get_single_point_of_interest($order->shop_id);
        
        return $order;
    }
}