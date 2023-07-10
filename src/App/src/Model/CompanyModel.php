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

class CompanyModel
{
    private $conn;
    private $adapter;
    private $companies;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $companies,
        ColumnFilters $columnFilters
    ) {
        $this->adapter = $companies->getAdapter();
        $this->companies = $companies;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findOptions(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'companyId',
            'name' => 'companyShortName',
        ]);
        $select->from(['c' => 'companies']);
        
        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('companyName', '%'.$str.'%');
                    $nest->or->like('companyShortName', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if (! empty($get['id'])) {
            $select->where(['c.companyId' => $get['id']]);
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
            'id' => 'companyId',
            'companyName',
            'companyShortName',
            'taxOffice',
            'taxNumber',
            'address',
            'createdAt'
        ]);
        $select->from(['c' => 'companies']);
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
                'id' => 'companyId',
                'companyName',
                'companyShortName',
                'taxNumber',
                'taxOffice',
                'address'
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'companyName',
                'taxNumber',
                'taxOffice',
                'address'
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

    public function findOneById(string $companyId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'companyId',
                'companyName',
                'companyShortName',
                'taxOffice',
                'taxNumber',
                'address',
                'createdAt'
            ]
        );
        $select->from(['c' => 'companies']);
        $select->where(['c.companyId' => $companyId]);

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
        try {
            $this->conn->beginTransaction();
            $data['companies']['clientId'] = CLIENT_ID;
            $data['companies']['companyId'] = $data['companyId'];
            $data['companies']['createdAt'] = date('Y-m-d H:i:s');
            $this->companies->insert($data['companies']);
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
            $this->companies->update($data['companies'], ['companyId' => $data['companyId']]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $companyId)
    {
        try {
            $this->conn->beginTransaction();
            $this->companies->delete(['companyId' => $companyId]);
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
