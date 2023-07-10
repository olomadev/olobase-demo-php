<?php

declare(strict_types=1);

require '../vendor/autoload.php';

use Laminas\Db\Adapter\AdapterInterface;

$container = require '../config/container.php';
$adapter = $container->get(AdapterInterface::class);

$tempFile = '/tmp/' . time() . '.xls';
file_put_contents($tmpFile, $_POST["fileContent"]);

// $inputFileName = PROJECT_ROOT.'/data/tmp/'.time().';

/** Create a new Xls Reader  **/
$reader = new PhpOffice\PhpSpreadsheet\Reader\Xls();
$spreadsheet = $reader->load($inputFileName);

$worksheet = $spreadsheet->getActiveSheet();

// Get the highest row and column numbers referenced in the worksheet
$highestRow = $worksheet->getHighestRow(); // e.g. 10
$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
$highestColumnIndex = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5

$expenseCount = intval($worksheet->getCell('G7')->getValue()); // Quantity of Attached Invoices
$pernetNumber = trim($worksheet->getCell('G2')->getValue());
$paymentTypeId = "644365f5-46de-47b1-afb3-aa7ed786bc5e"; // havale
$tax = "18";
$docNumber= str_replace("Expense Application", "", $worksheet->getCell('A1')->getValue());
$whoPaidId = "employee";
$isEmployeePaid = 0;
$isSellerPaid = 0;
$confirmStatus = 0;
$binaryContent = "";  // dosya binary content
$fileId = "";
die;

// echo $worksheet->getCell('A16')->getValue().PHP_EOL; // Start Date
$expenseStartCol = 15;
for ($e=1;$e<=$expenseCount;$e++) {

    $currentCol = $expenseStartCol + $e;

    echo $expenseDate = trim($worksheet->getCell('A'.$currentCol)->getValue()).PHP_EOL;
    echo $currencyId = trim($worksheet->getCell('E'.$currentCol)->getValue()).PHP_EOL;
    echo $amount = $worksheet->getCell('F'.$currentCol)->getValue().PHP_EOL;
    echo $totalAmount = $worksheet->getCell('H'.$currentCol)->getValue().PHP_EOL;
    echo $description = trim($worksheet->getCell('J'.$currentCol)->getValue()).PHP_EOL;
}


// for ($row = 2; $row <= $highestRow; ++$row) {

//         // echo $worksheet->getCellByColumnAndRow(1, $row)->getValue().PHP_EOL;
//         // echo $worksheet->getCellByColumnAndRow(2, $row)->getValue().PHP_EOL;
//         echo $worksheet->getCellByColumnAndRow(5, $row)->getValue().PHP_EOL;

// }