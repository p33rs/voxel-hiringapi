<?php
namespace p33rs\HiringApi;
use \p33rs\HiringApi\Exception as ClientException;
/**
 * Class FileReader
 * @package p33rs\HiringApi
 */
class FileReader {

    /**
     * Return the input as an array exploded on \n.
     * @param $filename
     * @return array
     */
    public static function read($filename) {
        try {
            if (!is_readable($filename)) {
                throw new ClientException('The input file could not be found.');
            }
            $file = fopen($filename, 'r');
            $input = fread($file, (filesize($filename)?:1));
            $input = explode("\n", $input);
            fclose($file);
            return $input;
        } catch (\Exception $e) {
            return ['error 000 ' . $e->getMessage()];
        }
    }

}
