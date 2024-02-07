<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Olobase\Mezzio\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class JobTitleListModel
{
    private $conn;
    private $adapter;
    private $jobTitles;
    private $jobTitleList;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $jobTitles,
        TableGatewayInterface $jobTitleList,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $jobTitleList->getAdapter();
        $this->jobTitles = $jobTitles;
        $this->jobTitleList = $jobTitleList;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'jobTitleListId',
            'name' => 'listName',
        ]);
        $select->from(['jl' => 'jobTitleList']);

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
            $select->where(['jl.jobTitleListId' => $get['id']]);
        }
        $select->group(['jobTitleListId', 'listName']);

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
            'id' => 'jobTitleListId',
            'jobTitleListId',
            'listName',
            'yearId' => new Expression("JSON_OBJECT('id', jl.yearId, 'name', jl.yearId)"),
        ]);
        $select->from(['jl' => 'jobTitleList']);
        $select->group(['jobTitleListId', 'listName', 'yearId']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('jobTitleListId', 'jl.jobTitleListId');
        $this->columnFilters->setAlias('yearId', 'jl.yearId');
        $this->columnFilters->setColumns([
            'yearId',
            'jobTitleListId',
            'listName',
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'listName',
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
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order(new Expression($order));
            }
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
        $jobTitleListId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->jobTitleList->update($data['jobTitleList'], ['jobTitleListId' => $jobTitleListId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $jobTitleListId)
    {
        try {
            $this->conn->beginTransaction();
            $this->jobTitles->delete(['jobTitleListId' => $jobTitleListId]);
            $this->jobTitleList->delete(['jobTitleListId' => $jobTitleListId]);
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
