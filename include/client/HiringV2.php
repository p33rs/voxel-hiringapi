<?php

  namespace api\client;
  
  class HiringV2 extends \api\Client {
  
    public $_token;
    protected $_path = 'v2/';
  
    /**
     * Add the auth method to the list of commands.
     * @see Client::_commands
     */
    public function __construct() {
      parent::__construct();
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
    } // end _query()
    
    public function __call($name, $arguments) {
      if (!$this->_token && $name != 'auth') return 'error 000 You haven\'t authenticated.';
      return parent::__call($name, $arguments);
    } // end __call()
    
  } // end HiringV2