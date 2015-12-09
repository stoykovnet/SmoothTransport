<?php

class ClickSend_API {

    /**
     * Send delivery confirmation SMS to a given telephone number.
     * @param int|string $senderId
     * @param int|string $recipientId
     * @param string $recipientTelephoneNumber
     * @return string
     */
    public function send_delivery_confirmation($senderId, $recipientId, $recipientTelephoneNumber) {
        return $this->send_SMS($senderId, $recipientId, $recipientTelephoneNumber, 'We have received your inquiry.');
    }

    /**
     * Send a text message as SMS to Clicksend.com's queue, to have it sent to
     * the specified telephone number. On success the SMS is saved.
     * @param int|string $senderId
     * @param int|string $recipientId
     * @param string $recipientTelephoneNumber
     * @param string $message
     * @return string
     */
    public function send_SMS($senderId, $recipientId, $recipientTelephoneNumber, $message) {
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

}
