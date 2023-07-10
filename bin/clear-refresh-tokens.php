<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use App\Utils\ErrorMailer;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\I18n\Translator\TranslatorInterface;
/**
 * Günde 1 defa çalışsın
 */
set_error_handler(function($errno, $errstr, $errfile, $errline ){
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});
$args = $_SERVER['argv'];
putenv("APP_ENV=$args[1]"); // set environment

$container = require dirname(__DIR__).'/config/container.php';

$mailer = $container->get(Mailer::class);
$adapter = $container->get(AdapterInterface::class);
$platform = $adapter->getPlatform();
$connection = $adapter->getDriver()->getConnection();

set_exception_handler(function($e) use($mailer) {
	// echo $e->getMessage();
    $errorMailer = new ErrorMailer($mailer);
    $errorMailer->setException($e);
    $errorMailer->send();
});
try {
	// begin transaction
	// 
	$connection->beginTransaction();
	$sql = new Sql($adapter);
	$delete = $sql->delete();
	$delete->from('refresh_tokens');
	$delete->where->lessThan('expires_at', date('Y-m-d', strtotime('-1 week')));
	       
	// echo $delete->getSqlString($adapter->getPlatform());
	// die;
	$statement = $sql->prepareStatementForSqlObject($delete);
	$resultSet = $statement->execute();
	$statement->getResource()->closeCursor();
	$connection->commit();
} catch (Exception $e) {
    $connection->rollback();
	echo $e->getMessage().PHP_EOL;
    $errorMailer = new ErrorMailer($mailer);
    $errorMailer->setException($e);
    $errorMailer->send();
}