<?php

declare(strict_types=1);

require '../vendor/autoload.php';

use Laminas\Cache\Storage\StorageInterface;

$args = $_SERVER['argv'];
putenv("APP_ENV=$args[1]");
//
// WARNING !
// 
// config container must be declared after putenv("APP_ENV=$args[1]")
// functions.
//
$container = require '../config/container.php';

$decodedString = base64_decode($args[2]);
if (false == $decodedString) {
    echo("\033[31mBase64 decode error ! \033[0m");
    echo PHP_EOL;
    exit(1);
}
parse_str($decodedString, $params);

$requestedClassArray = $params['requestedClass'];
$requestedFuncArray = $params['requestedFuncArray'];

$cache = $container->get(StorageInterface::class);
$options = $cache->getOptions();
$resourceManager = $options->getResourceManager();
$resourceId = $options->getResourceId();
$redis = $resourceManager->getResource($resourceId);

$allKeys = $redis->keys('*'); // get all keys
$removalKeys = array();
foreach ($allKeys as $key) {  // match operations
    $exp = explode(':', $key);
    $class = $exp[0];
    $function = isset($exp[1]) ? $exp[1] : null;
    if (in_array($class, $requestedClassArray) && in_array($function, $requestedFuncArray)) {
        $removalKeys[] = $key;
    }
}
$redis->delete($removalKeys); // delete founded specific keys