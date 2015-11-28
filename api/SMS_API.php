<?php

require_once constant('ROOT') . 'api/API.php';
require_once constant('ROOT') . 'ctr/SMS_Controller.php';
require_once constant('ROOT') . 'ctr/SMSUser_Controller.php';

class SMS_API extends API {

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
        // Convert data sent from clicksend to an array.
        $smsData = array();
        parse_str($this->file, $smsData);

        // The sender and the recipient must be validated first.
        $smsUserCtr = new SMSUser_Controller();
        $sender = $smsUserCtr->get_smsuser_by_telephone($smsData['from']);
        $recipient = $smsUserCtr->get_smsuser_by_telephone($smsData['to']);       
        if ($sender && $recipient) {
            // Save SMS if they're successfully validated.
            $sctr = new SMS_Controller();
            return $sctr->save_SMS($sender->id, $recipient->id, $smsData);
        } else {
            // Error Code? 403?
            return null;
        }
    }

}
