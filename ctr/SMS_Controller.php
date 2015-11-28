<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class SMS_Controller {

    public function save_SMS($senderId, $recipientId, $smsData) {
        $sms = new ModelTemplate('SMS');
        
        $sms->message = $smsData['message'];
        $sms->sender_id = $senderId;
        $sms->recipient_id = $recipientId;
        $sms->clicksend_ts = $smsData['timestamp'];
        
        return $sms->submit_new();
    }

}
