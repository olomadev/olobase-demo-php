<?php

// https://www.escarcega.gob.mx/web-files/phpexcel/readme.html
// https://learncodeweb.com/web-development/phpexcel-use-to-read-excel-file-and-insert-into-database/

require '../vendor/autoload.php';

// use function createGuid;

// $servername = "91.194.54.219";
// $username = "omega_prod";
// $password = "LkA.ejbUR/Nx8Q[Q";
// $database = "aninda_doktor_prod";

// // Create connection
// $conn = new mysqli($servername, $username, $password);

// // Check connection
// if ($conn->connect_error) {
//   die("Connection failed: " . $conn->connect_error);
// }
// // echo "Connected successfully";
// $conn->query("SELECT * FROM campaign_users");
// die;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx; 

// reader
$reader = new Xlsx;
$spreadsheet = $reader->load(PROJECT_ROOT."/data/tmp/d6bc8f92-d043-2952-9d61-64752f0ce486.xlsx");

$sheetData = $spreadsheet->getActiveSheet()->toArray();
$i=1;
unset($sheetData[0]);

$i = 0;
foreach ($sheetData as $row) {
  echo print_r($row).PHP_EOL;
  ++$i;
}
echo "TOTAL:".$i;

die;
// writer
// 
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet(); 

$cleanData = array(); // unique record filter
foreach ($sheetData as $row) {
    $title = filter($row[0]);
    if (! isset($cleanData[$title])) {
        $cleanData[$title] = $row;
    }
}
$sheet->setCellValue('A1', 'jobTitleId');
$sheet->setCellValue('B1', 'jobTitleName'); 

// NAVICAT DE İÇERİ ALIRKEN EXCEL I TARIH FORMATI "YMD" OLARAK AYARLAMAYI UNUTMA !!!
// 
// DATE FORMAT "YMD"
// DATE DELIMITER "/"

$i = 1;
$data = array();
foreach ($cleanData as $key => $row) {
    $jobTitle = filter($row[0]);
    $sheet->setCellValue('A'.$i, createGuid()); 
    $sheet->setCellValue('B'.$i, $jobTitle); 
    // $sheet->setCellValue('C'.$i, $tcno); 
    // $sheet->setCellValue('D'.$i, $email);
    // $sheet->setCellValue('E'.$i, 'tr');
    // $sheet->setCellValue('F'.$i, 'a9735f59-73db-48f6-9cd6-bc50af48219c');
    // $sheet->setCellValue('G'.$i, '1');
    // $sheet->setCellValue('H'.$i, '2021/03/31');
    // $sheet->setCellValue('I'.$i, '2022/06/02');
    // $sheet->getStyle('H'.$i)
    //     ->getNumberFormat()
    //     ->setFormatCode('YYYY/MM/DD');
    // $sheet->getStyle('I'.$i)
    //     ->getNumberFormat()
    //     ->setFormatCode('YYYY/MM/DD');

    ++$i;
}

// Write an .xlsx file  
$writer = new Xlsx($spreadsheet); 
  
// Save .xlsx file to the files directory 
$writer->save('output.xlsx'); 

// file_put_contents("output.txt", $sql);

function normalizeEmail($email) {
    $value = trim($email);
    $str = str_replace(['I','İ','ı'], ['i','i','i'], $value);
    $str = mb_strtolower($str);
    $str = str_replace(
        ["ğ", "Ğ", "ç", "Ç", "ş", "Ş", "ü", "Ü", "ö", "Ö", "ı", "İ"],
        ['g',"g",'c','c','s','s','u','u','ö','Ö','i','i'],
        $str
    );
    return $str;
}

// https://github.com/epigra/tckimlik/blob/master/src/TcKimlik.php
// 
function verifyTc($input)
{
    $tcno = $input;
    if (is_array($input) && !empty($input['tcno'])) {
        $tcno = $input['tcno'];
    }

    if (is_array($tcno)) {
        $inputKeys = array_keys($tcno);
        $tcno = $input[$inputKeys[0]];
    }

    if (!preg_match('/^[1-9]{1}[0-9]{9}[0,2,4,6,8]{1}$/', $tcno)) {
        return false;
    }
    
    $odd = $tcno[0] + $tcno[2] + $tcno[4] + $tcno[6] + $tcno[8];
    $even = $tcno[1] + $tcno[3] + $tcno[5] + $tcno[7];
    $digit10 = ($odd * 7 - $even) % 10;
    $total = ($odd + $even + $tcno[9]) % 10;

    if ($digit10 != $tcno[9] ||  $total != $tcno[10]) {
        return false;
    }

    return true;
}


function filter($value)
{
    $exp = explode(" ", trim($value));
    if (is_array($exp) && count($exp) > 0) {
        $words = array();
        foreach ($exp as $word) {
            $str = str_replace(['I','İ'], ['ı','i'], $word);
            $str = mb_strtolower($str);
            $words[] = ucFirstWord($str, 'utf-8');
        }
        return implode(" ", $words);
    }
    $str = str_replace(['I','İ'], ['ı','i'], $value);
    $str = mb_strtolower($str);
    $last = ucFirstWord($str, 'utf-8');
    return $last;
}
function ucFirstWord($string, $encoding)
{
    $firstChar = mb_substr($string, 0, 1, $encoding);
    if ($firstChar == 'i') {
        $firstChar = 'İ';
    }
    $then = mb_substr($string, 1, null, $encoding);
    $result = mb_strtoupper($firstChar, $encoding) . $then;
    return $result;
}