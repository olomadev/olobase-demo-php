<?php

namespace App\Model;

use Exception;
use App\Utils\ColumnFilters;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class EmployeeListModel
{
    private $conn;
    private $adapter;
    private $cache;
    private $employeeList;
    private $columnFilters;
    private $concatFunction;

    public function __construct(
        TableGatewayInterface $employeeList,
        StorageInterface $cache,
        ColumnFilters $columnFilters
    ) {
        $this->adapter = $employeeList->getAdapter();
        $this->employeeList = $employeeList;
        $this->cache = $cache;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'employeeListId',
            'name' => 'listName',
        ]);
        $select->from(['el' => 'employeeList']);

        // autocompleter search query support
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
            $select->where(['el.employeeListId' => $get['id']]);
        }
        $select->group(['employeeListId', 'listName']);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
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
            'id' => 'employeeListId',
            'employeeListId',
            'listName',
            'yearId' => new Expression("JSON_OBJECT('id', el.yearId, 'name', el.yearId)"),
        ]);
        $select->from(['el' => 'employeeList']);
        $select->group(['employeeListId', 'listName', 'yearId']);
        $select->where(['clientId' => CLIENT_ID]);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('employeeListId', 'el.employeeListId');
        $this->columnFilters->setAlias('yearId', 'el.yearId');
        $this->columnFilters->setColumns([
            'yearId',
            'employeeListId',
            'listName',
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'listName',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'employeeListId',
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
        $employeeListId = $data['employeeListId'];
        try {
            $this->conn->beginTransaction();
            $this->employeeList->update($data['employeeList'], ['employeeListId' => $employeeListId, 'clientId' => CLIENT_ID]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $employeeListId)
    {
        try {
            $this->conn->beginTransaction();
            $this->employeeList->delete(['employeeListId' => $employeeListId]);
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
