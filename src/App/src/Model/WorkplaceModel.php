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

class WorkplaceModel
{
    private $conn;
    private $adapter;
    private $cache;
    private $workplaces;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $workplaces,
        ColumnFilters $columnFilters,
        StorageInterface $cache
    ) {
        $this->adapter = $workplaces->getAdapter();
        $this->cache = $cache;
        $this->workplaces = $workplaces;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'workplaceId',
            'name' => 'workplaceName',
        ]);
        $select->from(['w' => 'workplaces']);
        
        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('workplaceName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['w.workplaceId' => $get['id']]);
        }
        $select->limit(50); // default limit for autocompleter

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
            'id' => 'workplaceId',
            'workplaceName',
            'registrationNumber',
            'address',
            'createdAt'
        ]);
        $select->from(['w' => 'workplaces']);
        $select->join(['c' => 'companies'], 'c.companyId = w.companyId', 
            [
                'companyId' => new Expression("JSON_OBJECT('id', c.companyId, 'name', c.companyName)"),
            ],
        $select::JOIN_LEFT);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('address', 'w.address');
        // $this->columnFilters->setAlias('area_code_name', 'cl.country_name');
        $this->columnFilters->setColumns(
            [
                'id' => 'workplaceId',             
                'companyName',
                'workplaceName',
                'registrationNumber',
                'address',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'companyName',
                'workplaceName',
                'registrationNumber',
                'address',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'companyId',
                'workplaceId',
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

    public function findOneById(string $workplaceId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'workplaceId',
                'workplaceName',
                'registrationNumber',
                'address',
                'createdAt'
            ]
        );
        $select->from(['w' => 'workplaces']);
        $select->join(['c' => 'companies'], 'c.companyId = w.companyId', 
            [
                'companyId',
                'companyName',
            ],
        $select::JOIN_LEFT);
        $select->where(['w.workplaceId' => $workplaceId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return $row;
    }
    
    public function create(array $data)
    {
        $className = \App\Model\CommonModel::class;
        $cacheKey  = $className.':findWorkplaces';
        $this->cache->removeItem($cacheKey);
        try {
            $this->conn->beginTransaction();
            $data['workplaces']['clientId'] = CLIENT_ID;
            $data['workplaces']['workplaceId'] = $data['workplaceId'];
            $data['workplaces']['createdAt'] = date('Y-m-d H:i:s');
            $this->workplaces->insert($data['workplaces']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $className = \App\Model\CommonModel::class;
        $cacheKey  = $className.':findWorkplaces';
        $this->cache->removeItem($cacheKey);
        try {
            $this->conn->beginTransaction();
            $this->workplaces->update($data['workplaces'], ['workplaceId' => $data['workplaceId']]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $workplaceId)
    {
        $className = \App\Model\CommonModel::class;
        $cacheKey  = $className.':findWorkplaces';
        $this->cache->removeItem($cacheKey);
        try {
            $this->conn->beginTransaction();
            $this->workplaces->delete(['workplaceId' => $workplaceId]);
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

