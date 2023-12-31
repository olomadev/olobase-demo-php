<?php

declare(strict_types=1);

use App\Exception\JsonDecodeException;

define('PROJECT_ROOT', dirname(__DIR__));
define('PROJECT_DOMAIN', 'demo.oloma.dev');
define('CACHE_ROOT_KEY', 'demoApp:');
define('CACHE_TMP_FILE_KEY', 'tmp_file_');
define('SESSION_KEY', CACHE_ROOT_KEY.'sessions:');
/**
 * Removes image prefix "data:image/png;base64, ...."
 * 
 * @param  string $value image string with prefix
 * @return string
 */
function cleanBase64Image($value) {
    if (strpos($value, ",") > 0) {
        $exp = explode(",", $value);
        return trim($exp[1]);
    }
    return $value;
}
/**
 * Paginator json decode
 *
 * @param  array  $data   data
 * @param  array  $fields fields
 * @return array
 */
function paginatorJsonDecode($items)
{
    if (empty($items)) {
        return array();
    }
    $jsonErrors = array(
        JSON_ERROR_DEPTH => 'Maximum heap size exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
    );
    $newData = array();
    foreach ($items as $key => $row) {
        foreach ($row as $field => $value) {
            if (is_string($value) && (strpos($value, '[{"') === 0 || strpos($value, '{"') === 0)) {  // if json encoded value
                $decodedValue = json_decode($value, true);
                $lastError = json_last_error();
                $newData[$key][$field] = empty($jsonErrors[$lastError]) ? $decodedValue : $jsonErrors[$lastError].': '.$value;
            } else {
                $newData[$key][$field] = $value;
            }
        }
    }
    return $newData;
}
/**
 * Debugable json decode
 * 
 * @param  string $data data
 * @return mixed
 */
function jsonDecode($data)
{
    if (empty($data)) {
        return array();
    }
    $decodedValue = json_decode($data, true);
    $lastError = json_last_error();
    $jsonErrors = array(
        JSON_ERROR_DEPTH => 'Maximum heap size exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
    );
    if (! empty($jsonErrors[$lastError])) {
        throw new JsonDecodeException(
            sprintf($jsonErrors[$lastError].'. Related data : '.print_r($data, true))
        );
    }
    return $decodedValue;
}
/**
 * Encode json 
 * @param  mixed $value val
 * @return string
 */
function jsonEncode($value)
{
    // We need to use JSON_UNESCAPED_SLASHES because javascript native 
    // json stringify function use this feature by default
    // 
    // https://stackoverflow.com/questions/10314715/why-is-json-encode-adding-backslashes
    // 
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
/**
 * Generate random alfabetic string
 *
 * @param  integer $length length
 * @return string
 */
function generateRandomAlpha($length = 10)
{
    return generateRandom('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length);
}
/**
 * Generate random string
 *
 * @param  integer $length length
 * @return string
 */
function generateRandomStringUpperCase($length = 10)
{
    return generateRandom('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', $length);
}
/**
 * Generate random string
 *
 * @param  integer $length length
 * @return string
 */
function generateRandomString($length = 10)
{
    return generateRandom('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length);
}
/**
 * Returns to random numbers
 *
 * @return string
 */
function generateRandomNumber($length = 10)
{
    return generateRandom('0123456789', $length);
}
/**
 * Private function to generate characters
 *
 * @param  string $characters characters
 * @param  integer $length
 * @return string
 */
function generateRandom(string $characters, int $length)
{
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
/**
 * Front end hiçbir zaman para değerlerini 20,7 gibi bir rakam göndermemeli,
 * yoksa yanlış hesaplanır doğrusu 20,70 gibi formatlayıp göndermeli
 * ya da 20.70
 */
function convertToMoney($value)
{
    $value = (string)$value;
    if (strpos($value, ',') > 0 OR strpos($value, '.') > 0) {
        $value = str_replace([',','.'], '', $value);
        if (is_numeric($value)) {
            $value = $value / 100;
        }
    }
    return (float)$value;
}
/**
 * GUID (aka UUID) is an acronym for 'Globally Unique Identifier' (or 'Universally Unique Identifier').
 * It is a 128-bit integer number used to identify resources.
 */
function createGuid($uppercase = false)
{
    $randomString = generateRandomString(6);
    if ($uppercase) {
        $randomStr = strtoupper($randomString);    
    } else {
        $randomStr = $randomString;
    }
    // Create a token
    $token      = $randomStr;
    $token     .= $randomStr;
    $randomStr  = generateRandomString(36);
    $token     .= uniqid((string)$randomStr, true);
    // GUID is 128-bit hex
    $hash       = strtolower(md5($token));
    // Create formatted GUID
    $guid       = '';
    // GUID format is XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX for readability
    $guid .= substr($hash, 0, 8) .
         '-' .
         substr($hash, 8, 4) .
         '-' .
         substr($hash, 12, 4) .
         '-' .
         substr($hash, 16, 4) .
         '-' .
         substr($hash, 20, 12);

    return $guid;
}
/**
 * isCommandLineInterface
 * 
 * @return bool
 */
function isCli()
{
    return (php_sapi_name() === 'cli');
}
/**
 * Format money
 * @param  money $value    string
 * @return float
 */
function formatMoney($value) {
    if (empty($value)) {
        return 0;
    }
    return (float)$value;
}
/**
 * Format date
 * @param date $value string
 * @return string
 */
function formatDate($value) {
    if (empty($value)) {
        return null;
    }
    return (string)$value;
}