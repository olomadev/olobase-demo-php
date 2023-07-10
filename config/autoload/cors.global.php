<?php
// In config/autoload/cors.global.php
declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

// https://docs.mezzio.dev/mezzio-cors/v1/middleware/#preflight-request
//
// https://www.dotkernel.com/dotkernel/adding-a-cors-implementation-to-zend-expressive/
// https://stackoverflow.com/questions/7564832/how-to-bypass-access-control-allow-origin
//
// $origins = [];
// if (isset($_SERVER['HTTP_ORIGIN'])) { // && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)
//     $origins = [(string)$_SERVER['HTTP_ORIGIN']];
// }
// $cors = [
//     'allowed_origins' => $origins,
//     'allowed_headers' => ['*'], // No custom headers allowed
//     'allowed_max_age' => '3600', // 1 hour
//     'credentials_allowed' => true, // Allow cookies,
//     'exposed_headers' => ['Token-Expired'], // Tell client that the API will always return this header
// ];
return [
    // ConfigurationInterface::CONFIGURATION_IDENTIFIER => $cors,
];
