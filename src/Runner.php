<?php
namespace p33rs\HiringApi;
class Runner {

    public function __invoke($filename, $endpoint) {
        $input = FileReader::read($filename);
        $client = preg_match('/^auth /', reset($input)) ? new Client\HiringV2($endpoint) : new Client\HiringV1($endpoint);
        return implode("\n", $this->_execute($input, $client));
    }

    /**
     * @param string[] $input
     * @param Client $client
     * @return string[]
     */
    private function _execute(Array $input, Client $client) {
        $results = array();
        foreach ($input as $line) {
            // get an array. trim the elements.
            $params = explode(' ', $line);
            $params = array_map(function($value) {
                return trim($value);
            }, $params);
            // pull the action off the end
            $action = trim(array_shift($params));
            // call the client method
            try {
                $results[] = call_user_func_array([$client, $action], $params);
            } catch (\Exception $e) {
                $results[] = 'error 000 ' . $e->getMessage();
            }
            // if an error occurred, halt.
            if (substr(end($results), 0, '5') == 'error') break;
        }
        return $results;
    }

}