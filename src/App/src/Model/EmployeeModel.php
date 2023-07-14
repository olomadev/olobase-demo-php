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
use Laminas\Db\Sql\Ddl;
use Laminas\Db\Sql\Ddl\Column;
use Laminas\Db\Metadata\Metadata;
use Laminas\Db\Sql\TableIdentifier;

class EmployeeModel
{
    private $conn;
    private $adapter;
    private $employees;
    private $employeeGroups;
    private $cache;
    private $columnFilters;
    private $concatFunction;

    public function __construct(
        TableGatewayInterface $employees,
        TableGatewayInterface $employeeGroups,
        StorageInterface $cache,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $employees->getAdapter();
        $this->employees = $employees;
        $this->employeeGroups = $employeeGroups;
        $this->cache = $cache;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptionsById(array $get)
    {
        if (! empty($get['id'])) {
            return $this->findOptions($get);    
        }
        if (empty($get['employeeListId'])) {
            return false;
        }
        return $this->findOptions($get);
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $concat = "CONCAT_WS(' ', ";
            $concat.= " NULLIF( e.name , '' ) ,";
            $concat.= " NULLIF( e.surname , '' ) ";
        $concat.= ")";
        $concatFunction = $platform->quoteIdentifierInFragment($concat, 
            ['(',')','CONCAT_WS','\'',',','IFNULL','-']
        );
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'employeeId',
            'name' => new Expression($concatFunction),
        ]);
        $select->from(['e' => 'employees']);
        $select->join(['el' => 'employeeList'], 'el.employeeListId = e.employeeListId', 
            [
                'employeeListId' => new Expression("JSON_OBJECT('id', el.employeeListId, 'name', el.listName)"),
            ],
        $select::JOIN_LEFT);

        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('name', '%'.$str.'%');
                    $nest->or->like('surname', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        // filter by employeeNumber
        // 
        if (! empty($get['employeeNumber'])) {
            $select->where(['e.employeeNumber' => $get['employeeNumber']]);
        }
        if (! empty($get['employeeListId'])) {
            $select->where(['e.employeeListId' => $get['employeeListId']]);    
        }
        if (! empty($get['id'])) {
            if (is_array($get['id'])) {
                $values = array();
                foreach ($get['id'] as $val) {
                    if (!empty($val['id'])) {
                        $values[] = $val['id'];
                    }
                }
                $select->where(['e.employeeId' => $values]);
            } else {
                $select->where(['e.employeeId' => $get['id']]);    
            }
        }
        $select->limit(50); // default limit for auto completer

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findAllBySearch(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $concat = "CONCAT_WS(' - ' , ";
            $concat.= " NULLIF( e.name , '' ) ,";
            $concat.= " NULLIF( e.middleName , '' ) ,";
            $concat.= " NULLIF( e.surname , '' ) ,";
            $concat.= " NULLIF( e.secondSurname , '' )";
        $concat.= ")";
        $concatFunction = $platform->quoteIdentifierInFragment($concat, 
            ['(',')','CONCAT_WS','\'',',','NULLIF','-']
        );
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'employeeId',
            'employeeId',
            'name' => new Expression($concatFunction),
            'tckn',
            'pernetNumber',
        ]);
        $select->from(['e' => 'employees']);
        $select->join(['c' => 'customers'], 'e.customerId = c.customerId', 
            [
                'customerShortName',
            ],
        $select::JOIN_LEFT);

        if (! empty($get['sowId'])) {
            $select->join(['se' => 'sowEmployees'], 'se.employeeId = e.employeeId', 
                [],
            $select::JOIN_LEFT);
            $select->where(['se.sowId' => $get['sowId']]);
        }
        if (! empty($get['customerId'])) {
            $select->where(['c.customerId' => $get['customerId']]);
        }
        // autocompleter search query
        //
        if (! empty($get['q'])) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('tckn', '%'.$str.'%');
                    $nest->or->like('pernetNumber', '%'.$str.'%');
                    $nest->or->like('customerShortName', '%'.$str.'%');
                    $nest->or->like(new Expression($concatFunction), '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
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
        $group = "JSON_ARRAYAGG(";
        $group.= "JSON_OBJECT(";
        $group.= "'id' , grp.groupId , ";
        $group.= "'name' , grp.groupName  ";
        $group.= "))";
        $this->groupFunction = $platform->quoteIdentifierInFragment(
            "(SELECT $group FROM groups grp LEFT JOIN employeeGroups eg ON eg.groupId = grp.groupId WHERE eg.employeeId = e.employeeId)",
            [
                '(',
                ')',
                'SELECT',
                'FROM',
                'AS',
                'eg',
                'grp',
                'e',
                ',',
                '[',
                ']',
                'JSON_ARRAYAGG',
                'JSON_OBJECT',
                'WHERE',
                ';',
                'CONCAT',
                'id',
                'name',
                '"',
                '\'',
                '\"', '=', '?', 'JOIN', 'ON', 'AND', 'LEFT', ','
            ]
        );
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'employeeId',
            'employeeNumber',
            'name',
            'surname',
            // 'fullname' => new Expression($this->concatFunction),
            'tckn',
            'employmentStartDate',
            'employmentEndDate',
            'groups' => new Expression($this->groupFunction),
        ]);
        $select->from(['e' => 'employees']);
        $select->join(['el' => 'employeeList'], 'el.employeeListId = e.employeeListId', 
            [
                'yearId' => new Expression("JSON_OBJECT('id', el.yearId, 'name', el.yearId)"),
                'employeeListId' => new Expression("JSON_OBJECT('id', el.employeeListId, 'name', el.listName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['c' => 'companies'], 'c.companyId = e.companyId', 
            [
                'companyId' => new Expression("JSON_OBJECT('id', c.companyId, 'name', c.companyShortName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['w' => 'workplaces'], 'w.workplaceId = e.workplaceId', 
            [
                'workplaceId' => new Expression("JSON_OBJECT('id', w.workplaceId, 'name', w.workplaceName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['j' => 'jobTitles'], 'j.jobTitleId = e.jobTitleId', 
            [
                'jobTitleId' => new Expression("JSON_OBJECT('id', j.jobTitleId, 'name', j.jobTitleName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['g' => 'employeeGrades'], 'g.gradeId = e.gradeId', 
            [
                'gradeId' => new Expression("JSON_OBJECT('id', g.gradeId, 'name', g.gradeName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['ep' => 'employeeProfiles'], 'ep.profileId = e.employeeProfile', 
            [
                'employeeProfile' => new Expression("JSON_OBJECT('id', ep.profileId, 'name', ep.profileName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['d' => 'departments'], 'd.departmentId = e.departmentId', 
            [
                'departmentId' => new Expression("JSON_OBJECT('id', d.departmentId, 'name', d.departmentName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['t' => 'employeeTypes'], 't.employeeTypeId = e.employeeTypeId', 
            [
                'employeeTypeId' => new Expression("JSON_OBJECT('id', t.employeeTypeId, 'name', t.employeeTypeName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['cc' => 'costCenters'], 'cc.costCenterId = e.costCenterId', 
            [
                'costCenterId' => new Expression("JSON_OBJECT('id', cc.costCenterId, 'name', cc.costCenterName)"),
            ],
        $select::JOIN_LEFT);

        $leftJoinExpression = new Expression($platform->quoteIdentifierInFragment(
                'dd.disabilityId = e.disabilityId AND dd.yearId = ?',
                ['AND','=','?']
            ),
            [date('Y')]
        ); 
        $select->join(['dd' => 'disabilities'], $leftJoinExpression, 
            [
                'disabilityId' => new Expression("JSON_OBJECT('id', dd.disabilityId, 'name', dd.description)"),
            ],
        $select::JOIN_LEFT);
        $select->where(['e.clientId' => CLIENT_ID]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;

        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('companyId', 'c.companyId');
        $this->columnFilters->setAlias('workplaceId', 'w.workplaceId');
        $this->columnFilters->setAlias('jobTitleId', 'j.jobTitleId');
        $this->columnFilters->setAlias('gradeId', 'g.gradeId');
        $this->columnFilters->setAlias('yearId', 'el.yearId');
        $this->columnFilters->setAlias('employeeListId', 'e.employeeListId');
        $this->columnFilters->setAlias('departmentId', 'd.departmentId');
        $this->columnFilters->setAlias('employeeTypeId', 't.employeeTypeId');
        $this->columnFilters->setAlias('disabilityId', 'dd.disabilityId');
        $this->columnFilters->setAlias('employeeProfile', 'ep.profileId');
        $this->columnFilters->setColumns([
            'companyId',
            'listName',
            'employeeNumber',
            'name',
            'surname',
            'tckn',
            'yearId',
            'employeeListId',
            'companyId',
            'workplaceId',
            'jobTitleId',
            'gradeId',
            'departmentId',
            'costCenterId',
            'employeeProfile',
            'disabilityId',
            'employeeTypeId',
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'employeeNumber',
                'listName',
                'fullname',
                'tckn',   
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'employeeListId',
                'yearId',
                'companyId',
                'workplaceId',
                'jobTitleId',
                'gradeId',
                'departmentId',
                'costCenterId',
                'employeeProfile',
                'disabilityId',
                'employeeTypeId',
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

    // public function findOneById(string $employeeId)
    // {
    //     $platform = $this->adapter->getPlatform();
    //     $sql = new Sql($this->adapter);
    //     $select = $sql->select();
    //     $select->columns([
    //         'id' => 'employeeId',
    //         'employeeNumber',
    //         'workplaceId',
    //         'customerId',
    //         'name',
    //         'surname',
    //         'tckn',
    //         'createdAt'
    //     ]);
    //     $select->from(['e' => 'employees']);
    //     $select->join(['b' => 'bloodTypes'], 'b.bloodTypeId = e.bloodTypeId', 
    //         [
    //             'bloodTypeName',
    //         ],
    //     $select::JOIN_LEFT);
    //     $select->join(['w' => 'workplaces'], 'w.workplaceId = e.workplaceId', 
    //         [
    //             'workplaceName',
    //             'workplaceRegistrationNumber' => 'registrationNumber',
    //         ],
    //     $select::JOIN_LEFT);
    //     $select->join(['cu' => 'customers'], 'e.customerId = cu.customerId', 
    //         [
    //             'customerName',
    //             'customerShortName',
    //         ],
    //     $select::JOIN_LEFT);
    //     $select->join(['c' => 'countries'], 'c.countryId = e.countryId', 
    //         [
    //             'countryName',
    //         ],
    //     $select::JOIN_LEFT);
    //     $select->join(['g' => 'genders'], 'g.genderId = e.genderId', 
    //         [
    //             'genderName',
    //         ],
    //     $select::JOIN_LEFT);

    //     // employee education
    //     // 
    //     $select->join(['ee' => 'employeeEducation'], 'ee.employeeId = e.employeeId', 
    //         ['*'],
    //     $select::JOIN_LEFT);

    //     // employee details
    //     // 
    //     $select->join(['ep' => 'employeePersonal'], 'ep.employeeId = e.employeeId', 
    //         ['*'],
    //     $select::JOIN_LEFT);

    //     $select->where(['e.employeeId' => $employeeId]);

    //     // echo $select->getSqlString($this->adapter->getPlatform());
    //     // die;
    //     $statement = $sql->prepareStatementForSqlObject($select);
    //     $resultSet = $statement->execute();
    //     $row = $resultSet->current();
    //     $statement->getResource()->closeCursor();
    //     return $row;
    // }

    public function create(array $data)
    {
        $employeeId = $data['employeeId'];
        try {
            $this->conn->beginTransaction();
            $data['employees']['clientId'] = CLIENT_ID;
            $data['employees']['employeeId'] = $employeeId;
            $data['employees']['createdAt'] = date('Y-m-d H:i:s');
            $this->employees->insert($data['employees']);

            if (! empty($data['employeeGroups'])) {
                foreach ($data['employeeGroups'] as $val) {
                    $this->employeeGroups->insert(['employeeId' => $employeeId, 'groupId' => $val['id']]);
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
        $employeeId = $data['employeeId'];
        try {
            $this->conn->beginTransaction();
            $this->employees->update($data['employees'], ['employeeId' => $employeeId, 'clientId' => CLIENT_ID]);

            // groups
            $this->employeeGroups->delete(['employeeId' => $employeeId]);
            if (! empty($data['employeeGroups'])) {
                foreach ($data['employeeGroups'] as $val) {
                    $this->employeeGroups->insert(['employeeId' => $employeeId, 'groupId' => $val['id']]);
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $employeeId)
    {
        try {
            $this->conn->beginTransaction();
            $this->employees->delete(['employeeId' => $employeeId]);
            $this->employeeGroups->delete(['employeeId' => $employeeId]);
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
