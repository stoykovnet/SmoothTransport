<?php

abstract class API {

    /**
     * The HTTP method (a.k.a. verb) of the request (GET, POST, PUT, or DELETE).
     * @var string 
     */
    protected $method = '';

    /**
     * The requested model entity.
     * @var string 
     */
    protected $endpoint = '';

    /**
     * Optional. Use only if an unusual action must be carried out, which is 
     * impossible to be handled by the standard HTTP methods.
     * @var string
     */
    protected $verb = '';

    /**
     * Optional. To pass parameters to the endpoint.
     * For instance, one can to get single entity by ID or set the response format
     * to JSON or XML.
     * @var array
     */
    protected $arguments = array();

    /**
     * To store the input data of a POST or PUT request.
     * @var mixed
     */
    protected $file = null;

    /**
     * Get request's method and data.
     * CORS supported.
     */
    public function __construct($request) {
        // Set up Cross-Origin Resource Sharing.
        header('Access-Control-Allow-Orgin: *');
        header('Access-Control-Allow-Methods: *');
        // Default.
        header('Content-Type: application/json');

        $this->get_request_method();
        $this->parse_request_uri($request);

        switch ($this->method) {
            case 'GET':
                $this->request = $this->sanatize_inputs($_GET);
                break;
            case 'POST':
                $this->request = $this->sanatize_inputs($_POST);
                $this->file = file_get_contents("php://input");
                break;
            case 'PUT':
                $this->request = $this->sanatize_inputs($_GET);
                $this->file = file_get_contents("php://input");
                break;
            case 'DELETE':
                $this->request = $this->sanatize_inputs($_POST);
                break;
            default:
                $this->get_the_response('Invalid HTTP method', 405);
                break;
        }
    }

    /**
     * Parse a request URI to get the requested endpoint and the arguments for 
     * that endpoint. If there's a verb it will be retrieved as well.
     * Request URI may look like: api/v1/{endpoint}/{|verb}/{argument1}/{argument2}
     * @param string $uri to be parsed.
     */
    private function parse_request_uri($uri) {
        $this->arguments = explode('/', rtrim($uri, '/'));

        $this->endpoint = array_shift($this->arguments);

        // The verb is not numeric like an element ID.
        if (array_key_exists(0, $this->arguments) &&
                !is_numeric($this->arguments[0])) {
            $this->verb = array_shift($this->arguments);
        }
    }

    /**
     * Detect the HTTP method of this request.
     * @throws exception if no valid HTTP method is specified.
     */
    private function get_request_method() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        /*
         * To check it is really a POST method. Otherwise it could be a PUT or
         * or DELETE method. PUT and DELETE usually are hidden under POST in a 
         * special header.
         */
        if ($this->method === 'POST' &&
                array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'DELETE') {
                if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'DELETE') {
                    $this->method = 'DELETE';
                } elseif ($_SERVER['HTTP_X_HTTP_METHOD'] === 'PUT') {
                    $this->method = 'PUT';
                } else {
                    throw new Exception('Unexpected header');
                }
            }
        }
    }

    /**
     * Call the method that is responsible for handling the requested endpoint.
     * @return mixed
     */
    public function route_request() {
        if (method_exists($this, $this->endpoint)) {
            return $this->get_the_response($this->{$this->endpoint}($this->arguments));
        } elseif ($this->endpoint === '') {
            return $this->get_the_response('No endpoint specified.', 404);
        } else {
            return $this->get_the_response('There is no endpoint called: '
                            . $this->endpoint, 404);
        }
    }

    /**
     * Build a HTTP response with the appropriate HTTP code.
     * @param mixed $data
     * @param int $code default=200.
     * @return array
     */
    private function get_the_response($data, $code = 200) {
        header('HTTP/1.1 ' . $code . ' ' . $this->get_request_status($code));
        return json_encode($data);
    }

    /**
     * Recursion. Strip html/php tags from a value.
     * @param mixed $data
     * @return mixed
     */
    private function sanatize_inputs($data) {
        $sanatized = array();

        if (is_array($data)) {
            foreach ($data as $key => $datum) {
                $sanatized[$key] = $this->sanatize_inputs($datum);
            }
        } else {
            $sanatized = trim(strip_tags($data));
        }

        return $sanatized;
    }

    /**
     * Get HTTP status message.
     * @param int $code
     * @return string
     */
    private function get_request_status($code) {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        );

        return ($status[$code]) ? $status[$code] : $status[500];
    }

    /**
     * Save HTTP request log. The log includes what data is received from outside,
     * who is the requester, access date and time, what method is used.
     */
    protected function log_request() {
        $log = '';
        $logPath = '../_bin/debug/api_requests_log.txt';
        if (file_exists($logPath)) {
            $log = file_get_contents($logPath);
        }
        file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . ']: '
                . "[$this->method]"
                . " From $this->origin "
                . $this->file . "\r\n"
                . $log);
    }

}
