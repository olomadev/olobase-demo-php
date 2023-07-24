<?php
declare(strict_types=1);

namespace App\Model;

use function array_column;
use function iterator_to_array;

use Exception;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Sql\Predicate\IsNotNull;

class CommonModel
{
    private $cache;
    private $config;
    private $adapter;

    public function __construct(
        AdapterInterface $adapter,
        StorageInterface $cache,
        array $config
    )
    {
        $this->cache = $cache;
        $this->adapter = $adapter;
        $this->config = $config;
    }
    
    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

    public function findRoleIds()
    {
        $rows = $this->findRoles();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findRoles()
    {
        // $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'roleId',
                'name' => 'roleName'
            ]
        );
        $select->from(['r' => 'roles']);
        $select->order(['roleLevel ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // $this->cache->setItem($key, $results);
        return $results;
    }

    public function findCurrencyIds()
    {
        $rows = $this->findCurrencies();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findCurrencies()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'currencyId',
                'name' => 'currencyName'
            ]
        );
        $select->from(['c' => 'currencies']);
        // $select->order(['countryName ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findWorkplaceNamesByKey()
    {
        $rows = $this->findWorkplaces();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['name']] = $val['id'];
        }
        return $result;
    }

    public function findWorkplaceNames()
    {
        $rows = $this->findWorkplaces();
        $results = array_column($rows, 'name');
        return $results;
    }

    public function findWorkplaceIds()
    {
        $rows = $this->findWorkplaces();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findWorkplaces()
    {
        // $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'workplaceId',
                'name' => 'workplaceName'
            ]
        );
        $select->from(['w' => 'workplaces']);
        $select->order(['workplaceName ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // $this->cache->setItem($key, $results);
        return $results;
    }

    public function findCompanyShortNamesByKey()
    {
        $rows = $this->findCompanies();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['companyShortName']] = $val['id'];
        }
        return $result;
    }

    public function findCompanyShortNames()
    {
        $rows = $this->findCompanies();
        $results = array_column($rows, 'companyShortName');
        return $results;
    }

    public function findCompanyIds()
    {
        $rows = $this->findCompanies();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findCompanies()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'companyId',
                'name' => 'companyName',
                'companyShortName'
            ]
        );
        $select->from(['c' => 'companies']);
        $select->order(['companyName ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findCountryIds()
    {
        $rows = $this->findCountries();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findCountries()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'countryId',
                'name' => 'countryName'
            ]
        );
        $select->from(['c' => 'countries']);
        $select->order(['countryName ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findCityIds(string $countryId)
    {
        $rows = $this->findCitiesByCountryId($countryId);
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findCitiesByCountryId(string $countryId)
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__.':'.$countryId;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'cityId',
                'name' => 'cityName'
            ]
        );
        $select->from(['c' => 'cities']);
        $select->where(['c.countryId' => $countryId]);
        $select->order(['cityName ASC']);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findEmployeeTypeNamesByKey()
    {
        $rows = $this->findEmployeeTypes();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['name']] = $val['id'];
        }
        return $result;
    }

    public function findEmployeeTypeIds()
    {
        $rows = $this->findEmployeeTypes();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findEmployeeTypeNames()
    {
        $rows = $this->findEmployeeTypes();
        $results = array_column($rows, 'name');
        return $results;
    }

    public function findEmployeeTypes()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'employeeTypeId',
                'name' => 'employeeTypeName'
            ]
        );
        $select->from(['emt' => 'employeeTypes']);
        // $select->order(['countryName ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findEmployeeGradeNamesByKey()
    {
        $rows = $this->findEmployeeGrades();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['name']] = $val['id'];
        }
        return $result;
    }

    public function findEmployeeGradeNames()
    {
        $rows = $this->findEmployeeGrades();
        $results = array_column($rows, 'name');
        return $results;
    }

    public function findEmployeeGradeIds()
    {
        $rows = $this->findEmployeeGrades();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findEmployeeGrades()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'gradeId',
                'name' => 'gradeName'
            ]
        );
        $select->from(['gr' => 'employeeGrades']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findEmployeeProfileNamesByKey()
    {
        $rows = $this->findEmployeeProfiles();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['name']] = $val['id'];
        }
        return $result;
    }

    public function findEmployeeProfileNames()
    {
        $rows = $this->findEmployeeProfiles();
        $results = array_column($rows, 'name');
        return $results;
    }

    public function findEmployeeProfileIds()
    {
        $rows = $this->findEmployeeProfiles();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findEmployeeProfiles()
    {
        // $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'profileId',
                'name' => 'profileName'
            ]
        );
        $select->from(['ef' => 'employeeProfiles']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        // $this->cache->setItem($key, $results);
        return $results;
    }

    public function findEmployeeListIds()
    {
        $rows = $this->findEmployeeList();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findEmployeeLists($years = false)
    {
        // $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'listId',
                'name' => 'listName',
                'yearId'
            ]
        );
        $select->from(['e' => 'employees']);
        $select->group(['listId', 'listName', 'yearId']);
        // $select->where(['yearId' => date('Y')]);
        $select->where(['clientId' => CLIENT_ID]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);

        $newResults = [];
        foreach ($results as $row) {
            if ($years) {
                $newResults[] = ['id' => $row['id'], 'name' => $row['yearId'].' - '.$row['name']];
            } else  {
                $newResults[] = ['id' => $row['id'], 'name' => $row['name']];
            }
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        // $this->cache->setItem($key, $results);
        return $newResults;
    }

    public function findYearIds()
    {
        $rows = $this->findYears();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findYears()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'yearId',
                'name' => 'yearName'
            ]
        );
        $select->from(['y' => 'years']);
        $select->order(['yearId ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $this->cache->setItem($key, $results);
        return $results;
    }

    public function findMonthIds()
    {
        $rows = $this->findMonths();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findMonths()
    {
        // $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $platform = $this->adapter->getPlatform();
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'monthId',
            ]
        );
        $select->from(['m' => 'months']);

        // language join
        // 
        $expressionSql = $platform->quoteIdentifierInFragment(
            'm.clientId = ml.clientId AND m.monthId = ml.monthId AND ml.langId = ?',
            ['AND','=','?']
        );
        $expression = new Expression($expressionSql, [LANG_ID]);
        $select->join(['ml' => 'monthLang'], 
            $expression, 
            [
                'name' => 'monthName'
            ],
        $select::JOIN_LEFT);

        $select->order(['ml.monthId ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        
        // $this->cache->setItem($key, $results);
        return $results;
    }

    public function findJobTitleNamesByKey()
    {
        $rows = $this->findJobTitles();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['name']] = $val['id'];
        }
        return $result;
    }

    public function findJobTitleIds()
    {
        $rows = $this->findJobTitles();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findJobTitleNames()
    {
        $rows = $this->findJobTitles();
        $results = array_column($rows, 'name');
        return $results;
    }

    public function findJobTitles()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'jobTitleId',
                'name' => 'jobTitleName'
            ]
        );
        $select->from('jobTitles');
        $select->order('jobTitleName ASC');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $this->cache->setItem($key, $results);
        return $results;
    }
    
    public function findDepartmentNamesByKey()
    {
        $rows = $this->findDepartments();
        $result = array();
        foreach ($rows as $val) {
            $result[$val['name']] = $val['id'];
        }
        return $result;
    }

    public function findDepartmentIds()
    {
        $rows = $this->findDepartments();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findDepartmentNames()
    {
        $rows = $this->findDepartments();
        $results = array_column($rows, 'name');
        return $results;
    }

    public function findDepartments()
    {
        // $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        // if ($this->cache->hasItem($key)) {
        //     return $this->cache->getItem($key);
        // }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'departmentId',
                'name' => 'departmentName'
            ]
        );
        $select->from('departments');
        $select->order('departmentName ASC');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // $this->cache->setItem($key, $results);
        return $results;
    }

    public function findAreaCodeIds()
    {
        $rows = $this->findAreaCodes();
        $results = array_column($rows, 'id');
        return $results;
    }

    public function findAreaCodes()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $concatSql = "CONCAT_WS('-' , countryName , ";
            $concatSql.= " areaCode";
        $concatSql.= ")";
        $platform = $this->adapter->getPlatform();
        $concatName = $platform->quoteIdentifierInFragment($concatSql, 
            ['(',')','CONCAT_WS','\'',',','IFNULL',' ', '-']
        );
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'areaCodeId',
                'name' => new Expression($concatName),
                'phoneMask',
                'mobileMask',
            ]
        );
        $select->from(['a' => 'areaCodes']);
        $select->join(['c' => 'countries'], 'c.countryId = a.areaCodeId', 
            [],
        $select::JOIN_LEFT);

        $select->order('a.areaCodeId ASC');
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        // $this->cache->setItem($key, $results);
        return $results;
    }

}