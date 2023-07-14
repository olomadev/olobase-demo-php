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

class SalaryModel
{
    private $conn;
    private $adapter;
    private $salaries;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $salaries,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $salaries->getAdapter();
        $this->salaries = $salaries;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'salaryId',
            'monthId' => new Expression("JSON_OBJECT('id', s.monthId, 'name', ml.monthName)"),
            'employeeId' => new Expression("JSON_OBJECT('id', s.employeeId, 'name', CONCAT(e.name, ' ', e.surname))"),
            'paymentTypeParamId' => new Expression("JSON_OBJECT('id', s.paymentTypeParamId, 'name', pl.description)"),
            'amount',
            'day',
        ]);
        $select->from(['s' => 'salaries']);
        $select->join(['e' => 'employees'], 's.employeeId = e.employeeId', [], $select::JOIN_LEFT);
        $select->join(['el' => 'employeeList'], 's.employeelistId = el.employeeListId', 
            [
                'yearId' => new Expression("JSON_OBJECT('id', el.yearId, 'name', el.yearId)"),
                'listName',
                'employeeListId' => new Expression("JSON_OBJECT('id', el.employeeListId, 'name', el.listName)"),
            ], 
            $select::JOIN_LEFT
        );
        $expressionSql = $platform->quoteIdentifierInFragment(
            's.clientId = ml.clientId AND s.monthId = ml.monthId AND ml.langId = ?',
            ['AND','=','?']
        );
        $expression = new Expression($expressionSql, [LANG_ID]);
        $select->join(['ml' => 'monthLang'], 
            $expression, 
            [
                'monthName'
            ],
        $select::JOIN_LEFT);

        $expressionSql = $platform->quoteIdentifierInFragment(
            's.clientId = pl.clientId AND s.paymentTypeParamId = pl.paymentTypeParamId AND pl.langId = ?',
            ['AND','=','?']
        );
        $expression = new Expression($expressionSql, [LANG_ID]);
        $select->join(['pl' => 'paymentTypeLang'], 
            $expression, 
            [
                'description'
            ],
        $select::JOIN_LEFT);

        $select->where(['s.clientId' => CLIENT_ID]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('monthId', 's.monthId');
        $this->columnFilters->setAlias('yearId', 's.yearId');
        $this->columnFilters->setAlias('employeeId', 's.employeeId');
        $this->columnFilters->setColumns([
            'monthId',
            'yearId',
            'employeeId',
        ]);
        $this->columnFilters->setWhereColumns(
            [
                'monthId',
                'yearId',
                'employeeId',
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
            $data['salaries']['clientId'] = CLIENT_ID;
            $data['salaries']['salaryId'] = createGuid();
            $this->salaries->insert($data['salaries']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $salaryId = $data['salaryId'];
        $yearId = $data['salaries']['yearId'];
        $monthId = $data['salaries']['monthId'];
        try {
            $this->conn->beginTransaction();
            $this->salaries->update(
                $data['salaries'], 
                [
                    'salaryId' => $salaryId, 
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

    public function delete(string $salaryId)
    {
        try {
            $this->conn->beginTransaction();
            $this->salaries->delete(['salaryId' => $salaryId, 'clientId' => CLIENT_ID]);
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
