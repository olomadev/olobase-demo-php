<?php

namespace App\Model;

use Exception;
use Oloma\Php\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class DepartmentModel
{
    private $conn;
    private $adapter;
    private $departments;
    private $subDepartments;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $departments,
        TableGatewayInterface $subDepartments,
        ColumnFiltersInterface $columnFilters
    )
    {
        $this->adapter = $departments->getAdapter();
        $this->departments = $departments;
        $this->subDepartments = $subDepartments;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'departmentId',
            'name' => 'departmentName',
        ]);
        $select->from(['d' => 'departments']);

        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('departmentName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['d.departmentId' => $get['id']]);
        }
        $select->limit(50); // default limit for autocompleter
        
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findSubOptions(array $get)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'subDepartmentId',
            'name' => 'subDepartmentName',
        ]);
        $select->from(['d' => 'subDepartments']);

        // filter by departmentId
        // 
        if (! empty($get['departmentId'])) {
            $select->where(['d.departmentId' => $get['departmentId']]);
        }
        $select->limit(50); // default limit for autocompleter

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $this->concatFunction = $platform->quoteIdentifierInFragment(
            "CONCAT(d.managerName ,' ', d.managerSurname)",
            ['(',')','CONCAT','\'',',']
        );
        $subDepartment = "JSON_ARRAYAGG(JSON_OBJECT('subDepartmentName', sd.subDepartmentName , 'subDepartmentManagerName' , sd.subDepartmentManagerName , 'subDepartmentManagerSurname' , sd.subDepartmentManagerSurname , 'subDepartmentManagerPhone' , CONCAT(sd.subDepartmentManagerPhoneAreaCodeId ,' ', sd.subDepartmentManagerPhone) , 'subDepartmentManagerMobile' , CONCAT(sd.subDepartmentManagerMobileAreaCodeId ,' ', sd.subDepartmentManagerMobile) , 'subDepartmentManagerEmail' , sd.subDepartmentManagerEmail))";
        $this->subDepartmentFunction = $platform->quoteIdentifierInFragment(
            "(SELECT $subDepartment FROM subDepartments AS sd WHERE sd.departmentId = d.departmentId)",
            [
                '(',')',
                'SELECT',
                'FROM',
                'AS',
                'ssd',
                'sd',
                'd',
                'a',
                'aa',
                ',',
                '[',
                ']',
                'JSON_ARRAYAGG',
                'JSON_OBJECT',
                'WHERE',
                'subDepartmentName',
                'subDepartmentManagerName',
                'subDepartmentManagerSurname',
                'subDepartmentManagerPhone',
                'subDepartmentManagerMobile',
                'subDepartmentManagerEmail',
                '"',
                '\'',
                '\"', '=', '?', 'JOIN', 'ON', 'AND', ','
            ]
        );
        $departmentPhoneConcat = "CONCAT( a.areaCode ,' ', cd.managerPhone )";
        $this->departmentPhoneConcatFunction = $platform->quoteIdentifierInFragment(
            "(SELECT $departmentPhoneConcat FROM departments AS cd JOIN areaCodes AS a ON cd.managerPhoneAreaCodeId = a.areaCodeId)",
            [
                '(',')',
                'SELECT',
                'CONCAT',
                'FROM',
                'AS',
                ',',
                '[',
                ']',
                '"',
                '\'',
                '\"', '=', '?', 'JOIN', 'ON', 'AND', ','
            ]
        );
        $departmentMobileConcat = "CONCAT( a.areaCode ,' ', cd.managerMobile )";
        $this->departmentMobileConcatFunction = $platform->quoteIdentifierInFragment(
            "(SELECT $departmentMobileConcat FROM departments AS cd JOIN areaCodes AS a ON cd.managerMobileAreaCodeId = a.areaCodeId)",
            [
                '(',')',
                'SELECT',
                'CONCAT',
                'FROM',
                'AS',
                ',',
                '[',
                ']',
                '"',
                '\'',
                '\"', '=', '?', 'JOIN', 'ON', 'AND', ','
            ]
        );
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'departmentId',
            'departmentName',
            'subDepartments' => new Expression($this->subDepartmentFunction),
            'managerName' => new Expression($this->concatFunction),
            'managerPhone' => new Expression($this->departmentPhoneConcatFunction),
            'managerMobile' => new Expression($this->departmentMobileConcatFunction),
            'managerEmail',
        ]);
        $select->from(['d' => 'departments']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('subDepartments', $this->subDepartmentFunction);
        $this->columnFilters->setAlias('managerName', $this->concatFunction);
        $this->columnFilters->setParentColumns(
            'subDepartments',
            [
                'subDeparmentName',
            ]
        );
        $this->columnFilters->setColumns(
            [
                'departmentName',
                'subDepartments',
                'managerName',
                'managerSurname',
                'managerPhone',
                'managerMobile',
                'managerEmail',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'departmentName',
                'subDepartments',
                'managerName',
                'managerSurname',
                'managerPhone',
                'managerMobile',
                'managerEmail',
            ]
        );
        $this->columnFilters->setData($get);
        $this->columnFilters->setSelect($select);

        if ($this->columnFilters->searchDataIsNotEmpty()) {
            $nest = $select->where->nest();
            foreach ($this->columnFilters->getSearchData() as $col => $words) {
                $nest = $nest->or->nest();
                foreach ($words as $str) {
                    $nest->or->like(new Expression($col), '%'.$str.'%');
                }
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if ($this->columnFilters->likeDataIsNotEmpty()) {
            foreach ($this->columnFilters->getLikeData() as $column => $value) {
                if (is_array($value)) {
                    $nest = $select->where->nest();
                    foreach ($value as $val) {
                        $nest->or->like(new Expression($column), '%'.$val.'%');
                    }
                    $nest->unnest();
                } else {
                    $select->where->like(new Expression($column), '%'.$value.'%');
                }
            }   
        }
        if ($this->columnFilters->whereDataIsNotEmpty()) {
            foreach ($this->columnFilters->getWhereData() as $column => $value) {
                if (is_array($value)) {
                    $nest = $select->where->nest();
                    foreach ($value as $val) {
                        $nest->or->equalTo(new Expression($column), $val);
                    }
                    $nest->unnest();
                } else {
                    $select->where->equalTo(new Expression($column), $value);
                }
            }
        }
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            $select->order($this->columnFilters->getOrderData());
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect(
            $select,
            $this->adapter
        );
        $paginator = new Paginator($paginatorAdapter);
        return $paginator;
    }

    public function findOneById(string $departmentId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'departmentId',
            'departmentName',
            'managerName',
            'managerSurname',
            'managerPhoneAreaCodeId',
            'managerPhone',
            'managerMobileAreaCodeId',
            'managerMobile',
            'managerEmail',
        ]);
        $select->from(['d' => 'departments']);
        $select->where(['d.departmentId' => $departmentId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // sub departments
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'departmentId',
                'subDepartmentId',
                'subDepartmentName',
                'subDepartmentManagerName',
                'subDepartmentManagerSurname',
                'subDepartmentManagerPhoneAreaCodeId',
                'subDepartmentManagerPhoneAreaCodeName' => 'subDepartmentManagerPhoneAreaCodeId',
                'subDepartmentManagerPhone',
                'subDepartmentManagerMobileAreaCodeId',
                'subDepartmentManagerMobileAreaCodeName' => 'subDepartmentManagerMobileAreaCodeId',
                'subDepartmentManagerMobile',
                'subDepartmentManagerEmail',
            ]
        );
        $select->from(['sd' => 'subDepartments']);
        $select->where(['sd.departmentId' => $departmentId]);
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $subDepartments = iterator_to_array($resultSet);
        // $statement->getResource()->closeCursor();

        $row['subDepartments'] = $subDepartments;
        return $row;
    }
    
    public function create(array $data)
    {
        $departmentId = $data['departmentId'];
        try {
            $this->conn->beginTransaction();
            $data['departments']['departmentId'] = $departmentId;
            $this->departments->insert($data['departments']);
            if (! empty($data['subDepartments'])) {
                foreach ($data['subDepartments'] as $val) {
                    $val['departmentId'] = $departmentId;
                    if (empty($val['subDepartmentId'])) {
                        $val['subDepartmentId'] = createGuid();
                    }
                    $this->subDepartments->insert($val);
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $departmentId = $data['departmentId'];
        try {
            $this->conn->beginTransaction();
            $this->departments->update($data['departments'], ['departmentId' => $departmentId]);
            if (! empty($data['subDepartments'])) {
                $this->subDepartments->delete(['departmentId' => $departmentId]);
                foreach ($data['subDepartments'] as $val) {
                    $val['departmentId'] = $departmentId;
                    $this->subDepartments->insert($val);
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $departmentId)
    {
        try {
            $this->conn->beginTransaction();
            $this->departments->delete(['departmentId' => $departmentId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

}