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

class CustomerModel
{
    private $conn;
    private $adapter;
    private $customers;
    private $customerJobTitles;
    private $customerAllowances;
    private $customerExpenseTypes;
    private $customerDepartments;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $customers,
        TableGatewayInterface $customerJobTitles,
        TableGatewayInterface $customerAllowances,
        TableGatewayInterface $customerExpenseTypes,
        TableGatewayInterface $customerDepartments,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $customers->getAdapter();
        $this->customers = $customers;
        $this->customerJobTitles = $customerJobTitles;
        $this->customerAllowances = $customerAllowances;
        $this->customerExpenseTypes = $customerExpenseTypes;
        $this->customerDepartments = $customerDepartments;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'customerId',
            'name' => 'customerName',
        ]);
        $select->from(['c' => 'customers']);

        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('customerName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['c.customerId' => $get['id']]);
        }
        $select->limit(200); // default limit for autocompleter
                
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'customerId',
            'customerShortName',
            'customerColor',
            'customerName',
            'taxOffice',
            'taxNumber',
            'createdAt'
        ]);
        $select->join(
            ['co' => 'countries'],
            'co.countryId = c.countryId',
            [
                'countryId',
                'countryName',
            ],
            $select::JOIN_LEFT
        );
        $select->join(
            ['ci' => 'cities'],
            'ci.cityId = c.cityId',
            [
                'cityId',
                'cityName',
            ],
            $select::JOIN_LEFT
        );
        $select->from(['c' => 'customers']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        // $this->columnFilters->setAlias('name', $this->concatFunction);
        // $this->columnFilters->setAlias('area_code_name', 'cl.country_name');
        $this->columnFilters->setColumns(
            [
                'customerName',
                'customerShortName',
                'taxOffice',
                'taxNumber',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'customerName',
                'customerShortName',
                'taxOffice',
                'taxNumber',
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

    public function findOneById(string $customerId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'customerId',
                'customerShortName',
                'customerName',
                'customerColor',
                'address1',
                'address2',
                'zipCode',
                'taxOffice',
                'taxNumber',
                'createdAt'
            ]
        );
        $select->from(['c' => 'customers']);
        $select->join(
            ['co' => 'countries'],
            'co.countryId = c.countryId',
            [
                'countryId',
                'countryName',
            ],
            $select::JOIN_LEFT
        );
        $select->join(
            ['ci' => 'cities'],
            'ci.cityId = c.cityId',
            [
                'cityId',
                'cityName',
            ],
            $select::JOIN_LEFT
        );
        $select->where(['c.customerId' => $customerId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // customer job titles
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'jobTitleId',
            ]
        );
        $select->from(['cjt' => 'customerJobTitles']);
        $select->join(
            ['jt' => 'jobTitles'], 'cjt.jobTitleId = jt.jobTitleId',
            [
                'jobTitleName',
            ],
            $select::JOIN_LEFT
        );
        $select->where(['customerId' => $customerId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $jobTitles = iterator_to_array($resultSet);

        // customer allowances
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'allowanceId',
            ]
        );
        $select->from(['eca' => 'employeeCostAllowances']);
        $select->join(
            ['ecl' => 'employeeCostAllowanceSheetLabel'], 'eca.allowanceId = ecl.allowanceId',
            [
                'allowanceName' => 'allowanceLabel',
            ],
            $select::JOIN_LEFT
        );
        $select->where(['customerId' => $customerId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $allowances = iterator_to_array($resultSet);

        // customer expense types
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'expenseTypeId',
            ]
        );
        $select->from(['cet' => 'customerExpenseTypes']);
        $select->join(
            ['et' => 'expenseTypes'], 'cet.expenseTypeId = et.expenseTypeId',
            [
                'expenseTypeName',
            ],
            $select::JOIN_LEFT
        );
        $select->where(['customerId' => $customerId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $expenseTypes = iterator_to_array($resultSet);

        $row['customerJobTitles'] = $jobTitles;
        $row['customerAllowances'] = $allowances;
        $row['customerExpenseTypes'] = $expenseTypes;
        return $row;
    }
    
    public function create(array $data)
    {
        try {
            $this->conn->beginTransaction();

            $customerId = $data['customerId'];
            $data['customers']['customerId'] = $customerId;
            $data['customers']['createdAt'] = date('Y-m-d H:i:s');
            $this->customers->insert($data['customers']);
            // job titles
            //
            if (! empty($data['customerJobTitles'])) {
                foreach ($data['customerJobTitles'] as $val) {
                    $val['customerId'] = $customerId;
                    $this->customerJobTitles->insert($val);
                }
            }
            // allowances
            //
            if (! empty($data['customerAllowances'])) {
                foreach ($data['customerAllowances'] as $val) {
                    $val['customerId'] = $customerId;
                    $this->customerAllowances->insert($val);
                }
            }
            // expense types
            //
            if (! empty($data['customerExpenseTypes'])) {
                foreach ($data['customerExpenseTypes'] as $val) {
                    $val['customerId'] = $customerId;
                    $this->customerExpenseTypes->insert($val);
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
        try {
            $this->conn->beginTransaction();
            $customerId = $data['customerId'];
            $this->customers->update($data['customers'], ['customerId' => $customerId]);
            // job titles
            //
            $this->customerJobTitles->delete(['customerId' => $customerId]);
            if (! empty($data['customerJobTitles'])) {
                foreach ($data['customerJobTitles'] as $val) {
                    $val['customerId'] = $customerId;
                    $this->customerJobTitles->insert($val);
                }
            }
            // allowances
            //
            $this->customerAllowances->delete(['customerId' => $customerId]);
            if (! empty($data['customerAllowances'])) {
                foreach ($data['customerAllowances'] as $val) {
                    $val['customerId'] = $customerId;
                    $this->customerAllowances->insert($val);
                }
            }
            // expense types
            // 
            $this->customerExpenseTypes->delete(['customerId' => $customerId]);
            if (! empty($data['customerExpenseTypes'])) {
                foreach ($data['customerExpenseTypes'] as $val) {
                    $val['customerId'] = $customerId;
                    $this->customerExpenseTypes->insert($val);
                }
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $customerId)
    {
        try {
            $this->conn->beginTransaction();
            $this->customers->delete(['customerId' => $customerId]);
            $this->customerDepartments->delete(['customerId' => $customerId]);
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
