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

class DisabilityModel
{
    private $conn;
    private $adapter;
    private $disabilities;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $disabilities,
        ColumnFilters $columnFilters
    ) {
        $this->adapter = $disabilities->getAdapter();
        $this->disabilities = $disabilities;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'disabilityId',
            'yearId' => new Expression("JSON_OBJECT('id', d.yearId, 'name', d.yearId)"),
            'degree',
            'description',
            'discountAmount',
        ]);
        $select->from(['d' => 'disabilities']);
        $select->where(['d.clientId' => CLIENT_ID]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('yearId', 'd.yearId');
        $this->columnFilters->setColumns([
            'yearId',
            'degree',
            'description',
        ]);
        $this->columnFilters->setWhereColumns(
            [
                'yearId',
                'degree',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'description',
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
            $data['disabilities']['clientId'] = CLIENT_ID;
            $data['disabilities']['disabilityId'] = createGuid();
            $this->disabilities->insert($data['disabilities']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $disabilityId = $data['disabilityId'];
        $yearId = $data['disabilities']['yearId'];
        try {
            $this->conn->beginTransaction();
            $this->disabilities->update(
                $data['disabilities'], 
                [
                    'disabilityId' => $disabilityId,
                    'yearId' => $yearId,
                    'clientId' => CLIENT_ID
                ]
            );
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $disabilityId)
    {
        try {
            $this->conn->beginTransaction();
            $this->disabilities->delete(['disabilityId' => $disabilityId, 'clientId' => CLIENT_ID]);
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
