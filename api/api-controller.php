<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/sms_api.php';

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $api = new sms_api($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $api->route_request();
} catch (Exception $ex) {
    echo json_encode(Array('error' => $ex->getMessage()));
}