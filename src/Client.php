<?php

namespace p33rs\HiringApi;

/**
 * Class Client
 * @package p33rs\HiringApi
 */
class Client {

    /**
     * @var array Define the behavior of each command that may
     *   be issued to the client. Each element is keyed by the
     *   name of the command, and each contains:
     *   - A string for the HTTP method
     *   - A string or callable($args) for the resource URI
     *   - An array or callable($args) for processing query data
     *   - A string or callable($args, $data) for processing successful results
     */
    protected $_commands;

    /**
     * @var string The endpoint where requests are sent.
     *   In our case, 'v1/' and 'v2/'.
     */
    protected $_path = '';

    /**
     * Set up the default commands array
     */
    public function __construct($host) {
        $this->_path = $host . $this->_path;
        $this->_commands = array(
            // GET /v1/key?key=$key
            'get' => array(
                'get',
                'key',
                function($args) { return array('key'=>$args[0]); },
                function($args, $data) {
                    return $data[$args[0]];
                }
            ),
            // PUT /v1/key?key=$key&value=$value
            'set' => array(
                'put',
                'key',
                function($args) {
                    return array ('key' => $args[0], 'value' => $args[1]);
                },
                'ok'
            ),
            // DELETE /v1/key?key=$key
            'delete' => array(
                'delete',
                'key',
                function($args) {
                    return array('key' => $args[0]);
                },
                'ok'
            ),
            // GET /v1/list
            'list' => array(
                'get',
                'list',
                array(),
                function($args, $data) {
                    return implode(' ', $data['keys']);
                }
            ),
        );
    } // end __construct()



    /**
     * Public function calls are passed to _request().
     * Output is processed and a string is returned.
     */
    public function __call($name, $arguments) {

        // if a bad action was requested, return an error message.
        if (!isset($this->_commands[$name])) {
            throw new Exception('An invalid command was provided.');
        }

        // figure out which HTTP method to use
        $method = $this->_commands[$name][0];
        if (is_object($method) && is_callable($method)) {
            $method = $method($arguments);
        }
        // build a query from the given arguments
        $query = $this->_query($name, $arguments);
        // get a url prefix
        $resource = $this->_commands[$name][1];
        if (is_object($resource) && is_callable($resource)) {
            $resource = $resource($arguments);
        }
        // get the handler for a successful response
        $handler = $this->_commands[$name][3];

        // make the request
        $request = $this->_request($method, $resource, $query);

        // retrieve and process data. check for errors.
        $raw = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        $decoded = json_decode($raw, true);
        $result = '';
        // curl error
        if (!$raw) {
            throw new Exception('Couldn\'t connect to API.');
        }
        // json not correctly formed
        elseif (!$decoded || !isset($decoded['status'])) {
            throw new Exception('Response contained corrupt data.');
        }
        // json reports error
        elseif ($decoded['status'] != 'ok') {
            $result = isset($decoded['msg'])
                ? ('error '.$status.' '.$decoded['msg'])
                : ('error '.$status.' an error occurred.');
        }
        // no errors occurred.
        else {
            // if a handler was defined as a closure, run it
            if (is_object($handler) && is_callable($handler)) {
                $result = $handler($arguments, $decoded, $this);
            }
            // if a handler was defined as a strong, return it
            else $result = $handler;
        }

        return $result;

    } // end __call()



    /**
     * Create a query string for the given action and parameters.
     * @param string $name The action requested by the user
     * @param array $arguments The arguments provided with the request
     * @return array A list of parameters for the query.
     */
    protected function _query($name, $arguments) {
        $handler = $this->_commands[$name][2];
        return is_callable($handler) ? $handler($arguments) : $handler;
    } // end _query()


    /**
     * @param $method
     * @param $resource
     * @param array $query
     * @return resource
     */
    protected function _request($method, $resource, array $query = array()) {

        $path = $this->_path . $resource;
        if ($query) $path .= '?'.http_build_query($query);

        // init curl
        $ch = curl_init($path);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPHEADER => array (
                'Accept: application/json',
            ),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT=> 3,
            CURLOPT_CUSTOMREQUEST=>strtoupper($method),
        ));
        return $ch;

    } // end _request()

} // end Client