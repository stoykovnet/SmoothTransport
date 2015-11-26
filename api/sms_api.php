<?php

require_once constant('ROOT') . 'api/api.php';
require_once constant('ROOT') . 'model/ModelTemplate.php';

class sms_api extends API {

    protected $trustedUser = '';
    protected $origin = '';

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->origin = $origin;
        $this->log_received_data();
    }

    protected function sms() {
        switch ($this->method) {
            case 'GET':
                return 'Nikola e kaval';
            case 'POST':
                return $this->process_post();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    private function process_post() {
        $data = array();
        parse_str($this->file, $data);

        $td = new ModelTemplate('TruckDriver');
        

        return 1;
    }

}
