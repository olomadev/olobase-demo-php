<?php

namespace App\Utils;

use Exception;
use Shuchkin\SimpleXLSX;
use Laminas\Cache\Storage\StorageInterface;
use App\Model\CommonModel;
use Laminas\I18n\Translator\TranslatorInterface;
use Predis\ClientInterface as Predis;

class EmployeeListParser
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
        $sheetData = array();
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
            $requiredHeaders = ['employeeNumber', 'companyId', 'workplaceId', 'name', 'surname', 'jobTitleId'];
            unset($sheetData[0]);

            // validate headers
            //
            $arrayIntersect = array_intersect($headersValidationArray, $requiredHeaders);
            if (count($arrayIntersect) != count($requiredHeaders)){
                throw new Exception(
                    $this->translator->translate("Please make sure the column headings are spelled correctly")
                );
            }
            $companyShortNames = $this->commonModel->findCompanyShortNames();
            $workplaceNames = $this->commonModel->findWorkplaceNames();
            $jobTitleNames = $this->commonModel->findJobTitleNames();
            $gradeNames = $this->commonModel->findEmployeeGradeNames();
            $departmentNames = $this->commonModel->findDepartmentNames();
            $costCenterNames = $this->commonModel->findCostCenterNames();
            $disabilityNames = $this->commonModel->findDisabilityNames();
            $employeeTypeNames = $this->commonModel->findEmployeeTypeNames();
            $employeeProfileNames = $this->commonModel->findEmployeeProfileNames();
            //
            // Array
            // (
            //     [0] => 8000001
            //     [1] => İK
            //     [2] => İnsan Kaynakları Merkez
            //     [3] => Ali-8000001
            //     [4] => Cin-8000001
            //     [5] => 31348000001
            //     [6] => İşe Alım Uzman Yardımcısı
            //     [7] => G1
            //     [8] => Test
            //     [9] => Masraf Merkezi 1
            //     [10] => 2021-10-23 00:00:00
            //     [11] => 2023-10-01 00:00:00
            //     [12] => Yok
            //     [13] => Çalışan
            //     [14] => Beyaz
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
                        case 'employeeNumber':
                            if ($v == "" || strlen($v) > 20 || strlen($v) < 1) {
                                $data['data'][$i][$headerKey]['errors'][] = "Bu alan 1 - 20 karakter aralığında olmalıdır";
                                $errorFound = true;
                            }
                        break;
                        case 'companyId':
                            if (! in_array(trim($v), $companyShortNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir şirket tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'workplaceId':
                            if (! in_array(trim($v), $workplaceNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir iş yeri tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'name':
                            if ($v == "" || strlen($v) > 60 || strlen($v) < 2) {
                                $data['data'][$i][$headerKey]['errors'][] = "Bu alan 2 - 60 karakter aralığında olmalıdır";
                                $errorFound = true;
                            }
                        break;
                        case 'surname':
                            if ($v == "" || strlen($v) > 60 || strlen($v) < 2) {
                                $data['data'][$i][$headerKey]['errors'][] = "Bu alan 2 - 20 karakter aralığında olmalıdır";
                                $errorFound = true;
                            }
                        break;
                        case 'tckn':
                            if ($v == "" || strlen($v) !== 11) {
                                $data['data'][$i][$headerKey]['errors'][] = "Bu alan tam olarak 11 karakter olmalıdır";
                                $errorFound = true;
                            }
                        break;
                        case 'jobTitleId':
                            if (! in_array(trim($v), $jobTitleNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir görev veritabanında tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'gradeId':
                            if (! in_array(trim($v), $gradeNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir seviye veritabanında tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'departmentId':
                            if (! in_array(trim($v), $departmentNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir departman veritabanında tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'costCenterId':
                            if (! in_array(trim($v), $costCenterNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir masraf merkezi veritabanında tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'employmentStartDate':
                            $exp = explode("-", $v);
                            if (is_array($exp) && count($exp) === 3) {
                                if (! checkdate((int)$exp[1], (int)$exp[2], (int)$exp[0])) {
                                    $data['data'][$i][$headerKey]['errors'][] = "İşe giriş tarihi geçerli bir tarih olarak gözükmüyor";    
                                    $errorFound = true;
                                }
                            }
                        break;
                        case 'employmentEndDate':
                            $exp = explode("-", $v);
                            if (is_array($exp) && count($exp) === 3) {
                                if (! checkdate((int)$exp[1], (int)$exp[2], (int)$exp[0])) {
                                    $data['data'][$i][$headerKey]['errors'][] = "Çıkış tarihi geçerli bir tarih olarak gözükmüyor";    
                                    $errorFound = true;
                                }
                            }
                        break;
                        case 'disabilityId':
                            if (! in_array(trim($v), $disabilityNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir engellilik derecesi veritabanında tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'employeeTypeId':
                            if (! in_array(trim($v), $employeeTypeNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir çalışan tipi veritabanında tanımlı değil";
                                $errorFound = true;
                            }
                        break;
                        case 'employeeProfile':
                            if (! in_array(trim($v), $employeeProfileNames)) {
                                $data['data'][$i][$headerKey]['errors'][] = "Böyle bir çalışan profili tanımlı değil";
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

        }



    }

}