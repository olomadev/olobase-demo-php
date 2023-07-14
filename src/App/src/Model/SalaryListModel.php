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

class SalaryListModel
{
    private $conn;
    private $adapter;
    private $salaryList;
    private $cache;
    private $columnFilters;
    private $concatFunction;

    public function __construct(
        TableGatewayInterface $salaryList,
        StorageInterface $cache,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $salaryList->getAdapter();
        $this->salaryList = $salaryList;
        $this->cache = $cache;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'salaryListId',
            'name' => 'listName',
        ]);
        $select->from(['sl' => 'salaryList']);

        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('listName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['sl.salaryListId' => $get['id']]);
        }
        $select->group(['salaryListId', 'listName']);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findPaymentTypesByYearId($yearId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'paymentTypeParamId',
            'calculationTypeId',
        ]);
        $select->from(['p' => 'paymentTypeParams']);
        $select->where(['yearId' => $yearId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findAll()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();        
        $select->columns([
            'id' => 'salaryListId',
            'salaryListId',
            'listName',
            'yearId' => new Expression("JSON_OBJECT('id', sl.yearId, 'name', sl.yearId)"),
        ]);
        $select->from(['sl' => 'salaryList']);
        $select->group(['salaryListId', 'listName', 'yearId']);
        $select->where(['sl.clientId' => CLIENT_ID]);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('salaryListId', 'sl.salaryListId');
        $this->columnFilters->setAlias('yearId', 'sl.yearId');
        $this->columnFilters->setColumns([
            'yearId',
            'salaryListId',
            'listName',
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'listName',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'salaryListId',
                'yearId',
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

    public function update(array $data)
    {
        $salaryListId = $data['salaryListId'];
        try {
            $this->conn->beginTransaction();
            $this->salaryList->update($data['salaryList'], ['salaryListId' => $salaryListId, 'clientId' => CLIENT_ID]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $salaryListId)
    {
        try {
            $this->conn->beginTransaction();
            $this->salaryList->delete(['salaryListId' => $salaryListId]);
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
