<?php

namespace App\Utils;

use Exception;
use Shuchkin\SimpleXLSX;
use Laminas\Cache\Storage\StorageInterface;
use App\Model\CommonModel;
use Laminas\I18n\Translator\TranslatorInterface;
use Predis\ClientInterface as Predis;

class JobTitleListParser
{
    protected $cache;
    protected $predis;
    protected $translator;
    protected $commonModel;

    public function __construct($container)
    {
        $this->translator = $container->get(TranslatorInterface::class);
        $this->translator->setLocale('tr');
        $this->predis = $container->get(Predis::class);
        $this->commonModel = $container->get(CommonModel::class);
        $this->cache = $container->get(StorageInterface::class);
    }

    public function parse($data)
    {
        $fileKey = "";
        //
        // Array
        // (
        //     [userId] => "",
        //     [fileId] => c8911b8f-dd08-5252-d42c-e06e614e7d57
        //     [fileName] => Employees.xlsx
        //     [fileType] => application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        //     [fileSize] => 4978518
        //     [status]   => false, // parse operation is finished ?
        //     [data]     => null,  // parsed xls array data
        //     [error]    => null,  // has the operation an error ?
        // )
        if (! empty($data['fileName'])) {
            $fileKey  = CACHE_TMP_FILE_KEY.$data['userId'];
            $xlsxFile = PROJECT_ROOT."/data/tmp/".$data['fileId'].".xlsx";
            if (file_exists($xlsxFile)) {
                if ($xlsx = SimpleXLSX::parse($xlsxFile)) {
                    $sheetData = $xlsx->rows();
                } else {
                    file_put_contents(PROJECT_ROOT."/data/tmp/error-output.txt", SimpleXLSX::parseError()." Error Line: ".__LINE__, FILE_APPEND | LOCK_EX);
                }
            }
        }
        if (! empty($sheetData)) {
          $headers = [];
            $h = 1;
            $headers[0] = [
                "title" => $this->translator->translate("no", "labels"),
                "align" => "start",
                "sortable" => true,
                "key" => "no",
            ];
            foreach ($sheetData[0] as $head => $title) {
                $headers[$h] = [
                    "title" => $this->translator->translate($title, "labels"),
                    "align" => "start",
                    "sortable" => true,
                    "key" => $title,
                ];
                ++$h;
            }
            $data['data'][0] = $headers;
            $headersValidationArray = $sheetData[0];
            $requiredHeaders = ['companyId','jobTitleId'];
            unset($sheetData[0]);

            // validate headers
            //
            $arrayIntersect = array_intersect($headersValidationArray, $requiredHeaders);
            if (count($arrayIntersect) != count($requiredHeaders)){
                throw new Exception(
                    $this->translator->translate("Please make sure the column headings are spelled correctly")
                );
            }
            $years = $this->commonModel->findYearIds();
            $companyShortNames = $this->commonModel->findCompanyShortNames();
            //
            // Array
            // (
            //     [0] => 2023
            //     [1] => İK
            //     [2] => İşe Alım Uzmanı
            // )
            $i = 1;
            $errorFound = false;
            foreach ($sheetData as $row) {

                array_unshift($row , $i); // add row number

                foreach ($row as $k => $v) {

                    $headerKey = $headers[$k]['key'];
                    // validations
                    // 
                    switch ($headerKey) {
                        case 'yearId':
                            if (empty($v) || ! in_array(trim($v), $years)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir yıl tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'companyId':
                            if (empty($v) || ! in_array(trim($v), $companyShortNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir şirket tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                    }
                    // write values
                    $data['data'][$i][$headerKey]['value'] = ($headerKey == "no") ? $i : $v;  
                }
                ++$i;
            }
            $statusData = array();
            $statusData['status'] = true;
            $statusData['error'] = null;
            $statusData['validationError'] = $errorFound;

            $this->cache->setItem($fileKey, $data);
            $this->predis->expire($fileKey, 600);

            $this->cache->setItem($fileKey.'_status', $statusData);
            $this->predis->expire($fileKey.'_status', 600);
            
        } // end empty sheetdata
    }
}

