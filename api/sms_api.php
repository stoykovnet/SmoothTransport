<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/api.php';

class sms_api extends API {

    protected $trustedUser = '';
    protected $origin = '';

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->origin = $origin;
    }

    protected function sms() {
        switch ($this->method) {
            case 'GET':
                return 'Nikola e kaval';
            case 'POST':
                return $this->log_sent_data();
            default:
                return 'No such method for ' . __FUNCTION__;
        }
    }

    private function log_sent_data() {
        $file = '';
        if (file_exists('../debug/sms_api_post.txt')) {
            $file = file_get_contents('../debug/sms_api_post.txt');
        }
        file_put_contents('../debug/sms_api_post.txt', '[' . date('Y-m-d H:i:s') . ']: '
                . " Received data from $this->origin "
                . $this->file . "\r\n"
                . $file);
        return "Got $this->file";
    }

}
