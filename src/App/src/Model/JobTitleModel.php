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

class JobTitleModel
{
    private $conn;
    private $adapter;
    private $jobtitles;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $jobtitles,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $jobtitles->getAdapter();
        $this->jobtitles = $jobtitles;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'jobTitleId',
            'name' => 'jobTitleName',
        ]);
        $select->from(['j' => 'jobTitles']);

        // autocompleter search query support
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('jobTitleName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['j.jobTitleId' => $get['id']]);
        }
        // $select->limit(50); // default limit for autocompleter
                
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
            'id' => 'jobTitleId',
            'companyId' => new Expression("JSON_OBJECT('id', j.companyId, 'name', c.companyShortName)"),
            'jobTitleName',
        ]);
        $select->from(['j' => 'jobTitles']);
        $select->join(['jl' => 'jobTitleList'], 'jl.jobTitleListId = j.jobTitleListId', 
            [
                'yearId' => new Expression("JSON_OBJECT('id', jl.yearId, 'name', jl.yearId)"),
                'jobTitleListId' => new Expression("JSON_OBJECT('id', jl.jobTitleListId, 'name', jl.listName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['c' => 'companies'], 'j.companyId = c.companyId', 
            [
                'companyShortName'
            ],
        $select::JOIN_LEFT);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('jobTitleListId', 'jl.jobTitleListId');
        // $this->columnFilters->setAlias('area_code_name', 'cl.country_name');
        $this->columnFilters->setColumns(
            [
                'jobTitleListId',
                'jobTitleName',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'jobTitleName',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'jobTitleListId',
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

    public function findOneById(string $jobTitleId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'jobTitleId',
                'jobTitleName',
            ]
        );
        $select->from(['j' => 'jobTitles']);
        $select->where(['j.jobTitleId' => $jobTitleId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        return $row;
    }
    
    public function create(array $data)
    {
        try {
            $this->conn->beginTransaction();
            $data['jobTitles']['jobTitleId'] = $data['jobTitleId'];
            $this->jobtitles->insert($data['jobTitles']);
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
            $this->jobtitles->update($data['jobTitles'], ['jobTitleId' => $data['jobTitleId']]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $jobTitleId)
    {
        try {
            $this->conn->beginTransaction();
            $this->jobtitles->delete(['jobTitleId' => $jobTitleId]);
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
