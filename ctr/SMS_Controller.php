<?php

require_once constant('ROOT') . 'ctr/SMSUser_Controller.php';
require_once constant('ROOT') . 'ctr/TruckDriver_Controller.php';
require_once constant('ROOT') . 'model/ModelTemplate.php';

class SMS_Controller {

    /**
     * Get the number of all SMSs that were sent to a selected recipient.
     * Select recipient by ID.
     * @param int|string $recipientId
     * @param boolean $unseen Optional. If you want to get the number of only unseen SMSs.
     * @return int
     */
    public function count_all_recipient_SMSs($recipientId, $unseen = false) {
        $sms = new ModelTemplate('SMS');

        if ($unseen) {
            return $sms->count(array('recipient_id', 'is_seen', 'is_resolved')
                            , array($recipientId, 'false', 'false'));
        } else {
            return $sms->count('recipient_id', $recipientId);
        }
    }

    /**
     * Retrieve all SMSs that were sent to a selected recipient. 
     * Select recipient by ID.
     * @param int|string $recipientId
     * @param boolean $unseen Optional. If you want to retrieve only unseen SMSs.
     * @return array(SMS)
     */
    public function get_all_recipient_SMSs($recipientId, $unseen = false) {
        $SMS = new ModelTemplate('SMS');
        $SMSs = null;

        if ($unseen) {
            $SMSs = $SMS->get_all(array('recipient_id', 'is_seen', 'is_resolved')
                    , array($recipientId, 'false', 'false'));
        } else {
            $SMSs = $SMS->get_all(array('recipient_id', 'is_resolved')
                    , array($recipientId, 'false'));
        }

        if ($SMSs) {
            $truckDriverCtr = new TruckDriver_Controller();
            $logisticianCtr = new Logistician_Controller();

            // Retrieve SMSs' senders and recipients.
            foreach ($SMSs as $key => &$SMS) {
                if ($sender = $truckDriverCtr->get_truck_driver_by_id($SMS->sender_id)) {
                    $SMS->sender_id = $sender;
                }
                if ($recipient = $logisticianCtr->get_logistician_by_id($SMS->recipient_id)) {
                    $SMS->recipient_id = $recipient;
                }
                // If no sender and recipient delete SMS.
                if (!($sender && $recipient)) {
                    unset($SMSs[$key]);
                }
            }
        }
        return $SMSs;
    }

    /**
     * Save an SMS.
     * @param int|string $senderId
     * @param int|string $recipientId
     * @param array $smsData
     * @return int
     */
    public function save_SMS($senderId, $recipientId, $smsData) {
        $sms = new ModelTemplate('SMS');

        $sms->message = $smsData['message'];
        $sms->sender_id = $senderId;
        $sms->recipient_id = $recipientId;
        $sms->clicksend_ts = gmdate('Y-m-d H:i:s', $smsData['timestamp']);

        return $sms->submit_new();
    }

    public function update_SMS($id, $smsData) {
        $sms = new ModelTemplate('SMS');

        $sms->id = $id;
        $sms->message = $smsData['message'];
        $sms->is_seen = $smsData['is_seen'];
        $sms->is_resolved = $smsData['is_resolved'];

        return $sms->submit_changes();
    }

}
