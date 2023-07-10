<?php

declare(strict_types=1);

use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\AdapterInterface;

/**
 * Works for every day
 */
require dirname(__DIR__).'/vendor/autoload.php';

$args = $_SERVER['argv'];
putenv("APP_ENV=$args[1]"); // set environment

$container = require dirname(__DIR__).'/config/container.php';
$adapter = $container->get(AdapterInterface::class);

// SSL Error Fix
// error:0A000152:SSL routines::unsafe legacy renegotiation disabled
// https://github.com/Kong/insomnia/issues/4543
// 
// vim /etc/ssl/openssl.cnf
// 
// etc
/*
  [openssl_init]
  # providers = provider_sect  # commented out
  
  # added
  ssl_conf = ssl_sect
  
  # added
  [ssl_sect]
  system_default = system_default_sect
  
  # added
  [system_default_sect]
  Options = UnsafeLegacyRenegotiation
  
  # List of providers to load
  [provider_sect]
  default = default_sect
*/

//  'Host: tcmb.gov.tr',
$headers = [ 
    'Accept: */*',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: en-US,en;q=0.5',
    'Connection: keep-alive',
    'Cache-Control: no-cache',
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
];
$url = 'http://www.tcmb.gov.tr/kurlar/today.xml';
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1'); 
$xmlResponse = curl_exec($ch); 
curl_close($ch); 

// $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// var_dump($status);

$xml = new SimpleXMLElement($xmlResponse);

$usdSelling = $xml->Currency[0]->BanknoteSelling;
$euroSelling = $xml->Currency[3]->BanknoteSelling;
$poundSelling = $xml->Currency[4]->BanknoteSelling;

$usdExchangeRate = (float)$usdSelling;
$euroExchangeRate = (float)$euroSelling;
$poundExchangeRate = (float)$poundSelling;

$sql = new Sql($adapter);
$insert = $sql->insert('exchangeRates');
$insert->columns(['usdExchangeRate', 'euroExchangeRate', 'poundExchangeRate']);
$insert->values([
    'rateId' => createGuid(),
    'usdExchangeRate' => $usdExchangeRate,
    'euroExchangeRate' => $euroExchangeRate,
    'poundExchangeRate' => $poundExchangeRate,
    'exchangeRateDate' => date('Y-m-d H:i:s'),
]);
$statement = $sql->prepareStatementForSqlObject($insert);
$statement->execute();
$statement->getResource()->closeCursor();