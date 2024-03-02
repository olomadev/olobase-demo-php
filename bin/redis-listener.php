<?php
## bin/redis-listener.php
#
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(60);  // set a specific time - prevent to server crashes
ini_set('memory_limit', '1024M');

set_error_handler(function($errno, $errstr, $errfile, $errline ){
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});
$args = $_SERVER['argv'];
putenv("APP_ENV=$args[1]"); // set environment
//
// WARNING !
// 
// config container must be declared after putenv("APP_ENV=$args[1]")
// functions.
//
require dirname(__DIR__)."/vendor/autoload.php";
$container = require dirname(__DIR__).'/config/container.php';

use App\Utils\JobTitleListParser;
use App\Utils\JobTitleListImporter;
use Predis\ClientInterface as Predis;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
try {
    $predis = $container->get(Predis::class);
    //
    // jobtitle list
    //------------------------------------------------------------
    //
    $job = $predis->lpop('jobtitlelist_parse');
    if (! empty($job)) {
        $data = json_decode($job, true);    
        $jobTitleParser = new JobTitleListParser($container);
        $jobTitleParser->parse($data);
    }
    $job = $predis->lpop('jobtitlelist_save');
    if (! empty($job)) {
        $data = json_decode($job, true);    
        $jobTitleImporter = new JobTitleListImporter($container);
        $jobTitleImporter->import($data);
    }
} catch (Exception $e) {
    if (! empty($data['fileKey'])) { // set error 
        $fileKey = $data['fileKey'];
        $simpleCache = $container->get(SimpleCacheInterface::class);
        $simpleCache->set(
            $fileKey.'_status', 
            ['status' => false, 'error' => $e->getMessage()],
            600
        );
    }
    $errorStr = $e->getMessage()." Error Line: ".$e->getLine();
    echo $errorStr.PHP_EOL;
    file_put_contents(PROJECT_ROOT."/data/tmp/error-output.txt", $errorStr, FILE_APPEND | LOCK_EX);
}