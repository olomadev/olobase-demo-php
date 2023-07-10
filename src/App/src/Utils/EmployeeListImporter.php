<?php

namespace App\Utils;

use function createGuid;
use Exception;
use App\Model\CommonModel;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Predis\ClientInterface as Predis;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;

class EmployeeListImporter
{
    protected $cache;
    protected $conn;
    protected $predis;
    protected $translator;
    protected $employees;

    public function __construct($container)
    {
        $this->translator = $container->get(TranslatorInterface::class);
        $this->translator->setLocale('tr');
        $this->predis = $container->get(Predis::class);
        $this->commonModel = $container->get(CommonModel::class);
        $this->cache = $container->get(StorageInterface::class);

        $this->adapter = $container->get(AdapterInterface::class);
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->employees = new TableGateway('employees', $this->adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
        $this->employeeList = new TableGateway('employeeList', $this->adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
    }

    public function import($data)
    {
        $fileKey = "";
        if (! empty($data['yearId']) && ! empty($data['listName'])) {

            $fileKey = $data['fileKey'];            
            $import = $this->cache->getItem($fileKey);

            if (! empty($import['data'][0])) {
                unset($import['data'][0]); // remove header

                $companies = $this->commonModel->findCompanyShortNamesByKey();
                $workplaces = $this->commonModel->findWorkplaceNamesByKey();
                $jobTitles = $this->commonModel->findJobTitleNamesByKey();
                $grades = $this->commonModel->findEmployeeGradeNamesByKey();
                $departments = $this->commonModel->findDepartmentNamesByKey();
                $costCenters = $this->commonModel->findCostCenterNamesByKey();
                $disabilities = $this->commonModel->findDisabilityNamesByKey();
                $employeeTypes = $this->commonModel->findEmployeeTypeNamesByKey();
                $employeeProfiles = $this->commonModel->findEmployeeProfileNamesByKey();

                $this->conn->beginTransaction();
                $insertData = array();
                $employeeListId = createGuid();
                //
                // create list data
                // 
                $this->employeeList->insert(
                    [
                        'clientId' => $data['clientId'],
                        'employeeListId' => $employeeListId,
                        'yearId' => $data['yearId'],
                        'listName' => $data['listName'],
                    ]
                );
                //
                // create employee data
                // 
                foreach ($import['data'] as $row) {
                    $insertData['clientId'] = $data['clientId'];
                    $insertData['employeeListId'] = $employeeListId;
                    $insertData['employeeId'] = createGuid();
                    $insertData['employeeNumber'] = $row['employeeNumber']['value'];
                    $insertData['companyId'] = Self::getCompanyId($row['companyId']['value'], $companies);
                    $insertData['workplaceId'] = Self::getWorkplaceId($row['workplaceId']['value'], $workplaces);
                    $insertData['name'] = $row['name']['value'];
                    $insertData['surname'] = $row['surname']['value'];
                    $insertData['tckn'] = $row['tckn']['value'];
                    $insertData['jobTitleId'] = Self::getJobTitleId($row['jobTitleId']['value'], $jobTitles);
                    $insertData['gradeId'] = Self::getGradeId($row['gradeId']['value'], $grades);
                    $insertData['departmentId'] = Self::getDepartmentId($row['departmentId']['value'], $departments);
                    $insertData['costCenterId'] = Self::getCostCenterId($row['costCenterId']['value'], $costCenters);
                    $insertData['employmentStartDate'] = Self::getFormattedDate($row['employmentStartDate']['value']);
                    $insertData['employmentEndDate'] = Self::getFormattedDate($row['employmentEndDate']['value']);
                    $insertData['disabilityId'] = Self::getDisabilityId($row['disabilityId']['value'], $disabilities);
                    $insertData['employeeTypeId'] = Self::getEmployeeTypeId($row['employeeTypeId']['value'], $employeeTypes);
                    $insertData['employeeProfile'] = Self::getEmployeeProfile($row['employeeProfile']['value'], $employeeProfiles);
                    $insertData['createdAt'] = date("Y-m-d H:i:s");
                    $this->employees->insert($insertData);
                }
                $this->conn->commit();

                $this->cache->removeItem($fileKey);
                $this->cache->removeItem("employeelist_parse");
                $this->cache->removeItem("employeelist_save");

                $this->cache->setItem($fileKey.'_status2', ['status' => true, 'error' => null]);
                $this->predis->expire($fileKey.'_status2', 200);
            }
        }               
        
    } // end func

    public static function getCompanyId($key, $companies)
    {
        if (! empty($companies[$key])) {
            return $companies[$key];
        }
        return null;
    }

    public static function getWorkplaceId($key, $workplaces)
    {
        if (! empty($workplaces[$key])) {
            return $workplaces[$key];
        }
        return null;
    }

    public static function getJobTitleId($key, $jobTitles)
    {
        if (! empty($jobTitles[$key])) {
            return $jobTitles[$key];
        }
        return null;
    }

    public static function getGradeId($key, $grades)
    {
        if (! empty($grades[$key])) {
            return $grades[$key];
        }
        return null;
    }

    public static function getDepartmentId($key, $departments)
    {
        if (! empty($departments[$key])) {
            return $departments[$key];
        }
        return null;
    }

    public static function getCostCenterId($key, $costCenters)
    {
        if (! empty($costCenters[$key])) {
            return $costCenters[$key];
        }
        return null;
    }

    public static function getDisabilityId($key, $disabilities)
    {
        if (! empty($disabilities[$key])) {
            return $disabilities[$key];
        }
        return null;
    }

    public static function getEmployeeTypeId($key, $employeeTypes)
    {
        if (! empty($employeeTypes[$key])) {
            return $employeeTypes[$key];
        }
        return null;
    }

    public static function getEmployeeProfile($key, $employeeProfiles)
    {
        if (! empty($employeeProfiles[$key])) {
            return $employeeProfiles[$key];
        }
        return null;
    }

    public static function getFormattedDate($date)
    {
        return date("Y-m-d", strtotime($date));
    }

    
} // end class
