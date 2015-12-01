<?php

require_once constant('ROOT') . 'api/API.php';
require_once constant('ROOT') . 'ctr/SMS_Controller.php';
require_once constant('ROOT') . 'ctr/SMSUser_Controller.php';
require_once constant('ROOT') . 'ctr/Logistician_Controller.php';

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
                return $this->process_sms_get();
            case 'POST':
                return $this->process_sms_post();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    protected function user() {
        switch ($this->method) {
            case 'GET':
                return $this->process_user_get();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    private function process_sms_get() {
        if (isset($_GET['seen'])) {
            // Get all unseen SMS.
            if ($_GET['seen'] === '0') {
                $sctr = new SMS_Controller();
                return count($sctr->get_unseen_SMS());
            }
        }
        return 0;
    }

    private function process_sms_post() {
        // Convert sent data to an array.
        $smsData = array();
        parse_str($this->file, $smsData);

        // The sender and the recipient must be validated first.
        $smsUserCtr = new SMSUser_Controller();
        $sender = $smsUserCtr->get_smsuser_by_telephone($smsData['from']);
        $recipient = $smsUserCtr->get_smsuser_by_telephone($smsData['to']);

        if ($sender && $recipient) {
            // Save SMS if they're successfully validated.
            $sctr = new SMS_Controller();
            if ($sctr->save_SMS($sender->id, $recipient->id, $smsData)) {
                // The SMS is received. Inform sender.
                return $this->send_delivery_confirmation($recipient->id, $sender->id, $sender->telephone);
            } else {
                // Couldn't save the SMS.
                return null;
            }
        } else {
            // Invalid sender or recipient number.
            return null;
        }
    }

    private function send_delivery_confirmation($senderId, $recipientId, $recipientTelephoneNumber) {
        return $this->send_SMS($senderId, $recipientId, $recipientTelephoneNumber, 'We have received your inquiry.');
    }

    private function send_SMS($senderId, $recipientId, $recipientTelephoneNumber, $message) {
        $recipientTelephoneNumber = '+61411111111'; // Testing telephone number.

        $cSMS = curl_init('https://api.clicksend.com/rest/v2/send.json');

        curl_setopt($cSMS, CURLOPT_POST, TRUE);

        curl_setopt($cSMS, CURLOPT_USERPWD, 'stoykovnet:4558458E-4FDF-B6A1-DF57-F5F9BE1BD0F3');
        curl_setopt($cSMS, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($cSMS, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($cSMS, CURLOPT_POSTFIELDS
                , "&to=$recipientTelephoneNumber"
                . "&message=$message"
                . '&method=rest'
        );

        $response = json_decode(curl_exec($cSMS));

        if ($response->recipientcount > 0) {
            $sctr = new SMS_Controller();
            $sctr->save_SMS($senderId, $recipientId
                    , array('message' => $message, 'timestamp' => time()));
        }

        return $response->recipientcount;
    }

    private function process_user_get() {
        if (isset($_GET['username']) && isset($_GET['password'])) {
            $lctr = new Logistician_Controller();
            $logistician = $lctr->get_logistician($_GET['username'], $_GET['password']);
            if ($logistician) {
                return $logistician->get_all_fields();
            }
        }
        return null;
    }

}
