<?php

declare(strict_types=1);

use Laminas\Db\Adapter\AdapterInterface;

require '../vendor/autoload.php';

// putenv("APP_ENV=local");

$container = require '../config/container.php';
$adapter = $container->get(AdapterInterface::class);

$args = $_SERVER['argv'];
if (empty($args[1])) {
    echo("\033[31mTablename cannot be empty ! \033[0m");
    echo PHP_EOL;
    exit(1);
}
$tablename = $args[1];
try {
	$statement = $adapter->createStatement('DESCRIBE `'.$tablename.'`');
	$resultSet = $statement->execute();
} catch (Exception $e) {
	echo("\033[31m".$e->getMessage()."\033[0m");
	echo PHP_EOL;
	exit(1);
}
$schemaType = 'null';
if (! empty($args[2])) {
    $schemaType = (string)$args[2];
}
$entityClassName = str_replace(' ', '', ucwords(str_replace('_', ' ',$tablename))).'Entity';
$file = PROJECT_ROOT.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Entity'.DIRECTORY_SEPARATOR.$entityClassName.'.php';

if (file_exists($file)) {
    printf("\033[31mEntity class %s already exists ! \033[0m", $entityClassName);
    echo PHP_EOL;
    exit(1);	
}
$fileContents = "<?php

namespace App\Entity;

/**
 * @table ".$tablename."
 */
class ".$entityClassName."
{";
    $fileContents.="
    const ENTITY_TYPE = '".$schemaType."';\n";
foreach ($resultSet as $row) {
$variableString = "    /**
     * @var ".$row['Type']."
     */";
$variableString.="
    public $".$row['Field'].";\n";
    $fileContents.= $variableString;
}
$fileContents.= "}\n";
file_put_contents($file, $fileContents);
chmod($file, 0644);
chown($file, "ersin");