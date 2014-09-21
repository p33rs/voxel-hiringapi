<?php

namespace p33rs\HiringApi\Client;
use p33rs\HiringApi\Client as BaseClient;

/**
 * Class HiringV2
 * @package p33rs\HiringApi\Client
 */
class HiringV2 extends BaseClient {

    public $_token;
    protected $_path = 'v2/';

    /**
     * Add the auth method to the list of commands.
     * @see Client::_commands
     */
    public function __construct($host) {
        parent::__construct($host);
        // GET /v2/auth?user=xxx&pass=yyy
        $this->_commands += array('auth' => array(
            'get',
            'auth',
            function($args) {
                return array ('user' => $args[0],'pass' => $args[1]);
            },
            function($args, $data, $self) {
                $self->_token = $data['token'];
                return 'ok';
            }
        ));
    } // end __construct()

    /**
     * The auth token is attached to all queries.
     * @see Client::_query()
     */
    protected function _query($name, $arguments) {
        $result = parent::_query($name, $arguments);
        return $result + array('token' => $this->_token);
    }

    public function __call($name, $arguments) {
        if (!$this->_token && $name != 'auth') {
            throw new Exception('Call required authentication');
        }
        return parent::__call($name, $arguments);
    }

}