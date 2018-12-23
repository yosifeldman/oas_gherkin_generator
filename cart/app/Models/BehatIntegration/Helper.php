<?php

namespace App\Models\BehatIntegration;

/**
 * Class Helper
 * @package App\Models\BehatIntegration
 */
class Helper
{

    /**
     * Check if class uses given trait
     * @param $class
     * @param $traitName should include namespace
     * @return bool
     */
    public static function classUsesTrait($class, $traitName)
    {
        $traitsUsed = class_uses($class);
        if (in_array($traitName, $traitsUsed)) {
            return true;
        }

        return false;
    }

    /**
     * Decode JSON string.
     *
     * @param string $string A JSON string.
     * @return mixed
     * @throws \Exception
     * @see http://www.php.net/json_last_error
     */
    public static function decodeJson($string)
    {
        $json = json_decode($string, true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $json;
                break;
            case JSON_ERROR_DEPTH:
                $message = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $message = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $message = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $message = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $message = 'Unknown error';
                break;
        }

        throw new \Exception('JSON decoding error: ' . $message);
    }
}