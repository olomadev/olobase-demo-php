<?php

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

require dirname(__DIR__)."/vendor/autoload.php";
$container = require dirname(__DIR__).'/config/container.php';

use App\Utils\EmployeeListParser;
use App\Utils\EmployeeListImporter;
use App\Utils\JobTitleListParser;
use App\Utils\JobTitleListImporter;
use App\Utils\SalaryListParser;
use App\Utils\SalaryListImporter;
use Predis\ClientInterface as Predis;
use Laminas\Cache\Storage\StorageInterface;
try {
    $predis = $container->get(Predis::class);
    // employee list
    //------------------------------------------------------------
    //
    $data = array();
    $job = $predis->lpop('employeelist_parse');
    if (! empty($job)) {
        $data = json_decode($job, true);    
        $employeeParser = new EmployeeListParser($container);
        $employeeParser->parse($data);
    }
    $job = $predis->lpop('employeelist_save');
    if (! empty($job)) {
        $data = json_decode($job, true);    
        $employeeImporter = new EmployeeListImporter($container);
        $employeeImporter->import($data);
    }
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
    // salary list
    //------------------------------------------------------------
    //
    $job = $predis->lpop('salarylist_parse');
    if (! empty($job)) {
        $data = json_decode($job, true);    
        $salaryListParser = new SalaryListListParser($container);
        $salaryListParser->parse($data);
    }
    $job = $predis->lpop('salarylist_save');
    if (! empty($job)) {
        $data = json_decode($job, true);    
        $salaryListImporter = new SalaryListImporter($container);
        $salaryListImporter->import($data);
    }
} catch (Exception $e) {
    if (! empty($data['fileKey'])) { // set error 
        $fileKey = $data['fileKey'];
        $cache = $container->get(StorageInterface::class);
        $cache->setItem($fileKey.'_status', ['status' => false, 'error' => $e->getMessage()]);
        $predis->expire($fileKey.'_status', 600);
    }
    file_put_contents(PROJECT_ROOT."/data/tmp/error-output.txt", $e->getMessage()." Error Line: ".$e->getLine(), FILE_APPEND | LOCK_EX);
}