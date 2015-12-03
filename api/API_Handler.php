<?php
if ($_SERVER['DOCUMENT_ROOT'] === 'C:/wamp/www/') {
    define('ROOT', $_SERVER['DOCUMENT_ROOT'] . 'smoothTransport/');
} else {
    define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once constant('ROOT') . 'api/SmoothTransport_API.php';

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $api = new SmoothTransport_API($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $api->route_request();
} catch (Exception $ex) {
    echo json_encode(Array('error' => $ex->getMessage()));
}