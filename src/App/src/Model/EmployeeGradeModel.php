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

class EmployeeGradeModel
{
    private $conn;
    private $adapter;
    private $employeeGrades;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $employeeGrades,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $employeeGrades->getAdapter();
        $this->employeeGrades = $employeeGrades;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'gradeId',
            'name' => 'gradeName',
        ]);
        $select->from(['g' => 'employeeGrades']);
        
        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('gradeName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['g.gradeId' => $get['id']]);
        }
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
            'id' => 'gradeId',
            'gradeName',
        ]);
        $select->from(['g' => 'employeeGrades']);
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
                'id' => 'gradeId',
                'gradeName',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'gradeName',
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
    
    public function create(array $data)
    {
        try {
            $this->conn->beginTransaction();
            $data['employeeGrades']['clientId'] = CLIENT_ID;
            $data['employeeGrades']['gradeId'] = $data['gradeId'];
            $this->employeeGrades->insert($data['employeeGrades']);
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
            $this->employeeGrades->update($data['employeeGrades'], ['gradeId' => $data['gradeId']]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $gradeId)
    {
        try {
            $this->conn->beginTransaction();
            $this->employeeGrades->delete(['gradeId' => $gradeId]);
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
