<?php

namespace App\Model;

use function createGuid;

use Exception;
use Oloma\Php\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class ExchangeRatesModel
{
    private $conn;
    private $adapter;
    private $exchangeRates;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $exchangeRates,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $exchangeRates->getAdapter();
        $this->exchangeRates = $exchangeRates;
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findOne()
    {
        $this->maxExchangeFunction = $this->adapter->getPlatform()->quoteIdentifierInFragment(
            "(SELECT MAX(exchangeRateDate) FROM exchangeRates)",
            [
                '(',')',
                'SELECT',
                'MAX',
                'FROM',
                ',',
                '"',
                '\'',
                '\"', '=', '?', 'JOIN', 'ON', 'AND', ','
            ]
        );

        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'exchangeRateDate',
                'usdExchangeRate',
                'euroExchangeRate',
                'poundExchangeRate',
            ]
        );
        $select->from(['ex' => 'exchangeRates']);
        $select->where(['exchangeRateDate' => new Expression($this->maxExchangeFunction)]);

        $statement = $sql->prepareStatementForSqlObject($select);
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return $row;
    }

    public function create(array $data)
    {
        // print_r($data);
        // die;
        try {
            $this->conn->beginTransaction();
            $data['exchangeRates']['rateId'] = createGuid();
            $data['exchangeRates']['exchangeRateDate'] = date('Y-m-d H:i:s');
            $this->exchangeRates->insert($data['exchangeRates']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(['*']);
        $select->from(['ex' => 'exchangeRates']);
        return $select;
    }

    public function findWeeklyChart() 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(['*']);
        $select->from(['ex' => 'exchangeRates']);
        $select->order('exchangeRateDate DESC');
        $select->limit(7);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'usdExchangeRate',
            'euroExchangeRate',
            'poundExchangeRate',
        ]);
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
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            $select->order($this->columnFilters->getOrderData());
        } else {
            $select->order('exchangeRateDate DESC');
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

    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }
}
