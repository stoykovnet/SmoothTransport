<?php

require_once constant('ROOT') . 'model/ModelTemplate.php';

class SMS_Controller {

    public function save_SMS($senderId, $recipientId, $smsData) {
        $sms = new ModelTemplate('SMS');
        
        $sms->message = $smsData['message'];
        $sms->sender_id = $senderId;
        $sms->recipient_id = $recipientId;
        $sms->clicksend_ts = gmdate('Y-m-d H:i:s', $smsData['timestamp']);
        
        return $sms->submit_new();
    }

    public function get_unseen_SMS($userId = null) {
        $sms = new ModelTemplate('SMS');
        return $sms->get_all('is_seen', 'false');
    }
}
