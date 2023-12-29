<?php
declare(strict_types=1);

namespace App\Utils;

use Exception;
use Shuchkin\SimpleXLSX;
use App\Model\CommonModel;
use Laminas\I18n\Translator\TranslatorInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class JobTitleListParser
{
    /**
     * Db table columns
     */
    const COL_COMPANY_ID = 'companyId';

    protected $translator;
    protected $commonModel;
    protected $simpleCache;

    public function __construct($container)
    {
        $this->translator = $container->get(TranslatorInterface::class);
        $this->commonModel = $container->get(CommonModel::class);
        $this->simpleCache = $container->get(SimpleCacheInterface::class);
    }

    public function parse($data)
    {
        $fileKey = "";
        //
        // Array
        // (
        //     [userId]   => "",
        //     [fileId]   => c8911b8f-dd08-5252-d42c-e06e614e7d57
        //     [fileKey]  => 'tmpFile_xxx',
        //     [fileExt]  => 'xls',
        //     [fileName] => JobTitles.xlsx
        //     [fileType] => application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
        //     [fileSize] => 4978518
        //     [status]   => false, // parse operation is finished ?
        //     [data]     => null,  // parsed xls array data
        //     [error]    => null,  // has the operation an error ?
        //     [env]      => 'local',
        //     [locale]   => 'en',
        // )        
        if (! empty($data['fileName'])) {
            // set locale
            $this->translator->setLocale($data['locale']);

            $fileKey  = CACHE_TMP_FILE_KEY.$data['userId'];
            $xlsxFile = PROJECT_ROOT."/data/tmp/".$data['fileId'].".xlsx";
            if (file_exists($xlsxFile)) {
                if ($xlsx = SimpleXLSX::parse($xlsxFile)) {
                    $sheetData = $xlsx->rows();
                } else {
                    file_put_contents(
                        PROJECT_ROOT."/data/tmp/error-output.txt", 
                        SimpleXLSX::parseError()." Error Line: ".__LINE__, 
                        FILE_APPEND | LOCK_EX
                    );
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
                if (! empty($title)) {
                    $headers[$h] = [
                        "title" => $this->translator->translate($title, "labels"),
                        "align" => "start",
                        "sortable" => true,
                        "key" => $title,
                    ];
                    ++$h;
                }
            }
            $data['data'][0] = $headers;
            $headersValidationArray = $sheetData[0];
            $requiredHeaders = [
                'companyId',
                'jobTitleId'
            ];
            unset($sheetData[0]);

            // validate headers
            //
            $arrayIntersect = array_intersect($headersValidationArray, $requiredHeaders);
            if (count($arrayIntersect) != count($requiredHeaders)){
                throw new Exception(
                    $this->translator->translate(
                        "Please make sure the column headings are spelled correctly"
                    )
                );
            }
            $years = $this->commonModel->findYearIds();
            $companyShortNames = $this->commonModel->findCompanyShortNames();
            $i = 1;
            $errorFound = false;
            foreach ($sheetData as $row) {

                array_unshift($row , $i); // add row number

                foreach ($row as $k => $v) {
                    if (empty($headers[$k]['key'])) { // don't store empty keys
                        break;
                    }
                    $headerKey = $headers[$k]['key'];
                    //
                    // Put your validations here !!!
                    // 
                    switch ($headerKey) {
                        case Self::COL_COMPANY_ID:
                            if (empty($v) || ! in_array(trim($v), $companyShortNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = $this->translator->translate(
                                    "No such company is defined in the database"
                                );
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

            $this->simpleCache->set($fileKey, $data, 600);
            $this->simpleCache->set($fileKey.'_status', $statusData, 600);
            
        } // end empty sheetdata
    }
}

