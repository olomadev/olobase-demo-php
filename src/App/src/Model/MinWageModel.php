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

class MinWageModel
{
    private $conn;
    private $adapter;
    private $minWage;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $minWage,
        ColumnFilters $columnFilters
    ) {
        $this->adapter = $minWage->getAdapter();
        $this->minWage = $minWage;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'wageId',
            'yearId' => new Expression("JSON_OBJECT('id', w.yearId, 'name', w.yearId)"),
            'monthId' => new Expression("JSON_OBJECT('id', w.monthId, 'name', ml.monthName)"),
            'daily',
            'monthly',
        ]);
        $select->from(['w' => 'minWage']);

        $expressionSql = $platform->quoteIdentifierInFragment(
            'w.clientId = ml.clientId AND w.monthId = ml.monthId AND ml.langId = ?',
            ['AND','=','?']
        );
        $expression = new Expression($expressionSql, [LANG_ID]);
        $select->join(['ml' => 'monthLang'], 
            $expression, 
            [
                'monthName'
            ],
        $select::JOIN_LEFT);
        $select->where(['w.clientId' => CLIENT_ID]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('monthId', 'w.monthId');
        $this->columnFilters->setAlias('yearId', 'w.yearId');
        $this->columnFilters->setColumns([
            'monthId',
            'yearId',
        ]);
        $this->columnFilters->setWhereColumns(
            [
                'monthId',
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

    public function create(array $data)
    {
        try {
            $this->conn->beginTransaction();
            $data['minumumwages']['clientId'] = CLIENT_ID;
            $data['minumumwages']['wageId'] = createGuid();
            $this->minWage->insert($data['minumumwages']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $wageId = $data['wageId'];
        $yearId = $data['minumumwages']['yearId'];
        $monthId = $data['minumumwages']['monthId'];
        try {
            $this->conn->beginTransaction();
            $this->minWage->update(
                $data['minumumwages'], 
                [
                    'wageId' => $wageId, 
                    'yearId' => $yearId, 
                    'monthId' => $monthId, 
                    'clientId' => CLIENT_ID
                ]
            );
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $wageId)
    {
        try {
            $this->conn->beginTransaction();
            $this->minWage->delete(['wageId' => $wageId, 'clientId' => CLIENT_ID]);
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
