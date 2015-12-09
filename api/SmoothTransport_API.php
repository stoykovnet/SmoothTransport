<?php

require_once constant('ROOT') . 'api/API.php';
require_once constant('ROOT') . 'api/ClickSend_API.php';
require_once constant('ROOT') . 'ctr/Logistician_Controller.php';
require_once constant('ROOT') . 'ctr/SMS_Controller.php';
require_once constant('ROOT') . 'ctr/SMSUser_Controller.php';
require_once constant('ROOT') . 'ctr/Truck_Controller.php';
require_once constant('ROOT') . 'ctr/DeliveryOrder_Controller.php';

class SmoothTransport_API extends API {

    protected $origin = '';

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->origin = $origin;
        $this->log_request();
    }

    protected function logistician() {
        switch ($this->method) {
            case 'GET':
                return $this->process_logistician_get();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    protected function sms() {
        switch ($this->method) {
            case 'POST':
                return $this->process_sms_post();
            case 'PUT':
                return $this->process_sms_put();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    protected function truck_driver() {
        switch ($this->method) {
            case 'GET':
                return $this->process_truck_driver_get();
            case 'POST':
                return $this->process_truck_driver_post();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    protected function truck() {
        switch ($this->method) {
            case 'GET':
                return $this->process_truck_get();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    protected function point_of_interest() {
        switch ($this->method) {
            case 'GET':
                return $this->process_point_of_interest_get();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    /**
     * Process the following URI:
     * api/v1/logistician/ - to get a logistician. 
     * api/v1/logistician/{id}/sms/ - to get all SMSs that the logistician has received.
     * api/v1/logistician/{id}/sms/unseen/ - to get only SMSs that are unseen.
     * api/v1/logistician/{id}/sms/count/ - count all SMSs that the logistician has received.
     * api/v1/logistician/{id}/sms/unseen/count/ - count only SMSs that are unseen.
     * 
     * To get a logistician, an Authorization header must be added to the request 
     * with {username}:{SHA 256 hashed password}.
     * @return Logistician
     */
    private function process_logistician_get() {

        // URI: api/v1/logistician/
        if (empty($this->arguments) && $this->verb === '') {
            // There should be an Authorization header in the request, to log in.
            $headers = apache_request_headers();

            if (isset($headers['Authorization'])) {
                // Username (plain text): Password (hashed with SHA 256).
                $credentials = explode(':', $headers['Authorization']);

                $logisticianCtr = new Logistician_Controller();
                // Return a logistician, if the credentials are valid, otherwise return null.
                if ($logistician = $logisticianCtr->
                        get_logistician_by_credentials($credentials[0], $credentials[1])) {
                    return $logistician->get_all_fields();
                }
            }
        }

        // URI: api/v1/logistician/{id}/sms/{argument1}/{argument2}/{etc}
        elseif (count($this->arguments) > 1 && $this->arguments[1] === 'sms') {
            $SMSCtr = new SMS_Controller();

            // URI: api/v1/logistician/{id}/sms/ || api/v1/logistician/{id}/sms/unseen/
            if (count($this->arguments) === 2 ||
                    (count($this->arguments) === 3 && $this->arguments[2] === 'unseen')) {
                $SMSs = null;
                if ($this->arguments[1] === 'unseen') {
                    $SMSs = $SMSCtr->get_all_recipient_SMSs($this->arguments[0], 'true');
                } else {
                    $SMSs = $SMSCtr->get_all_recipient_SMSs($this->arguments[0]);
                }

                if ($SMSs) {
                    foreach ($SMSs as &$sms) {
                        $sms->sender_id = $sms->sender_id->get_all_fields();
                        $sms->recipient_id = $sms->recipient_id->get_all_fields();
                        $sms = $sms->get_all_fields();
                    }
                }

                return $SMSs;
            }

            // URI: api/v1/logistician/{id}/sms/count/ || api/v1/logistician/{id}/sms/unseen/count/
            elseif ((count($this->arguments) === 3 && $this->arguments[2] === 'count') ||
                    ((count($this->arguments) === 4 && $this->arguments[3] === 'count'))) {
                if ($this->arguments[2] === 'unseen') {
                    return $SMSCtr->count_all_recipient_SMSs($this->arguments[0], 'true');
                } else {
                    return $SMSCtr->count_all_recipient_SMSs($this->arguments[0]);
                }
            }
        }

        return null;
    }

    /**
     * Process the following URI:
     * /api/v1/sms/ - to save an SMS.
     * 
     * When an SMS is saved a delivery confirmation SMS is sent back to sender.
     * @return mixed
     */
    private function process_sms_post() {
        // Convert sent post data to an array.
        $smsData = array();
        parse_str($this->file, $smsData);

        // Verify there is actually any content in the post data.
        if (isset($smsData['from']) && $smsData['from'] !== '' &&
                isset($smsData['to']) && $smsData['to'] !== '' &&
                isset($smsData['message']) && $smsData['message'] !== '' &&
                isset($smsData['timestamp']) && $smsData['timestamp'] !== '') {

            // The sender and the recipient must be validated first.
            $smsUserCtr = new SMSUser_Controller();
            $sender = $smsUserCtr->get_smsuser_by_telephone($smsData['from']);
            $recipient = $smsUserCtr->get_smsuser_by_telephone($smsData['to']);

            if ($sender && $recipient) {
                // Save SMS if they're successfully validated.
                $sctr = new SMS_Controller();
                if ($sctr->save_SMS($sender->id, $recipient->id, $smsData)) {
                    // The SMS is received. Inform sender.
                    $cSend = new ClickSend_API();
                    return $cSend->send_delivery_confirmation($recipient->id, $sender->id, $sender->telephone);
                }
            }
        }
        return null;
    }

    private function process_sms_put() {
        
        // URI: api/v1/sms/{id}/
        if (count($this->arguments) === 1) {
            return $this->update_sms();
        }
        return null;
    }
    
    private function update_sms() {
        $smsData = json_decode($this->file, true);
        $smsCtr = new SMS_Controller();
        return $smsCtr->update_SMS($this->arguments[0], $smsData);
    }

    /**
     * Process the following URIs:
     * api/v1/truck_driver/ - to get all truck drivers.
     * api/v1/truck_driver/count/ - to get the total number of all drivers.
     * api/v1/truck_driver/{id}/ - to get a driver by ID.
     * api/v1/truck_driver/{id}/truck/ - to get a truck driver's truck.
     * @return TruckDriver|Truck
     */
    private function process_truck_driver_get() {
        $truckDriverCtr = new TruckDriver_Controller();

        // URI: api/v1/truck_driver/
        if (empty($this->arguments) && $this->verb === '') {
            // Return truck drivers, if there are any, otherwise - null.
            if ($truckDrivers = $truckDriverCtr->get_all_truck_drivers()) {
                foreach ($truckDrivers as &$td) {
                    $td = $td->get_all_fields();
                }
                return $truckDrivers;
            }
        } else {

            // URI: api/v1/truck_driver/{action}/
            if ($this->verb !== '') {
                // URI: api/v1/truck_driver/count/
                if ($this->verb === 'count') {
                    return $truckDriverCtr->count_truck_drivers();
                }
            }

            // URI: api/v1/truck_driver/{id}/
            elseif (count($this->arguments) === 1) {
                // Return a truck driver, if there is such, otherwise - null.
                if ($truckDriver = $truckDriverCtr->get_truck_driver_by_id($this->arguments[0])) {
                    return $truckDriver->get_all_fields();
                }
            }

            // URI: api/v1/truck_driver/{id}/truck/            
            elseif (count($this->arguments) === 2 && $this->arguments[1] === 'truck') {
                $tctr = new Truck_Controller();
                // Return a truck driver's truck, if they have such, otherwise - null.
                if ($truck = $tctr->get_truck_driver_truck($this->arguments[0])) {
                    return $truck->get_all_fields();
                }
            }
        }

        return null;
    }

    private function process_truck_driver_post() {
        
        // URI: api/v1/truck_driver/{id}/sms/
        if (count($this->arguments) === 2 && $this->arguments[1] === 'sms') {
            return $this->send_message_to_truck_driver($this->arguments[0]);
        }

        return null;
    }

    private function send_message_to_truck_driver($recipientId) {
        
        $smsData = json_decode($this->file, true);
        // Verify there is actually any content in the post data.
        if (isset($smsData['senderId']) && $smsData['senderId'] !== '' &&
                isset($smsData['recipientId']) && $smsData['recipientId'] !== '' &&
                isset($smsData['recipientTelephone']) && $smsData['recipientTelephone'] !== '' &&
                isset($smsData['message']) && $smsData['message'] !== '') {

            $cSend = new ClickSend_API();
            return $cSend->send_SMS($smsData['senderId'], $recipientId
                            , $smsData['recipientTelephone'], $smsData['message']);
        }

        return null;
    }

    private function process_truck_get() {
        // URI: api/v1/truck/
        if (count($this->arguments) === 0) {
            return $this->get_all_trucks();
        }

        // URI: api/v1/truck/{id}/
        if (count($this->arguments) === 1) {
            return $this->get_single_truck($this->arguments[0]);
        }

        // URI: api/v1/truck/{id}/delivery_order/
        if (count($this->arguments) === 2 && $this->arguments[1] === 'delivery_order') {
            return $this->get_truck_delivery_order($this->arguments[0]);
        }
        return null;
    }

    private function get_all_trucks() {
        $truckCtr = new Truck_Controller();
        $trucks = $truckCtr->get_all_trucks();

        foreach ($trucks as &$truck) {
            $truck = $truck->get_all_fields();
        }
        return $trucks;
    }

    private function get_single_truck($id) {
        $truckCtr = new Truck_Controller();
        $truck = $truckCtr->get_truck_by_id($id);

        if ($truck) {
            return $truck->get_all_fields();
        }

        return null;
    }

    private function get_truck_delivery_order($truckId) {
        $orderCtr = new DeliveryOrder_Controller();
        $order = $orderCtr->get_truck_delivery_order($truckId);

        if ($order && $order->car_id && $order->manufacturer_id && $order->shop_id) {
            $order->car_id = $order->car_id->get_all_fields();
            $order->manufacturer_id = $order->manufacturer_id->get_all_fields();
            $order->shop_id = $order->shop_id->get_all_fields();
            return $order->get_all_fields();
        }

        return null;
    }

    private function process_point_of_interest_get() {
        // URI: api/v1/point_of_interest/services/
        if (count($this->arguments) === 0 && $this->verb === 'services') {
            return $this->get_services();
        }

        return null;
    }

    private function get_services() {
        $poiCtr = new PointOfInterest_Controller();
        $pois = $poiCtr->get_all('type', 'service');

        if ($pois) {
            foreach ($pois as &$poi) {
                $poi = $poi->get_all_fields();
            }
        }

        return $pois;
    }

}
