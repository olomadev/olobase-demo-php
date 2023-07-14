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

class PayrollSchemeModel
{
    private $conn;
    private $adapter;
    private $payrollScheme;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $payrollScheme,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $payrollScheme->getAdapter();
        $this->payrollScheme = $payrollScheme;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'payrollSchemeId',
            'schemeName',
            'schemeDescription',
            'startDate',
            'endDate',
        ]);
        $select->from(['p' => 'payrollScheme']);
        $select->join(['c' => 'companies'], 'c.companyId = p.companyId', 
            [
                'companyId' => new Expression("JSON_OBJECT('id', c.companyId, 'name', c.companyShortName)"),
            ], 
            $select::JOIN_LEFT
        );
        $select->join(['w' => 'workplaces'], 'w.workplaceId = p.workplaceId', 
            [
                'workplaceId' => new Expression("JSON_OBJECT('id', w.workplaceId, 'name', w.workplaceName)"),
            ], 
            $select::JOIN_LEFT
        );
        $select->join(['u' => 'users'], 'u.userId = p.createdBy', 
            [
                'createdBy' => new Expression("JSON_OBJECT('id', u.userId, 'name', u.email)"),
            ], 
            $select::JOIN_LEFT
        );
        $select->where(['p.clientId' => CLIENT_ID]);
        //
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('companyId', 'c.companyId');
        $this->columnFilters->setAlias('workplaceId', 'w.workplaceId');
        $this->columnFilters->setColumns([
            'schemeName',
            'schemeDescription',
            'startDate',
            'endDate',
            'companyId',
            'workplaceId',
        ]);
        $this->columnFilters->setWhereColumns(
            [
                'companyId',
                'workplaceId',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'schemeName',
                'schemeDescription',
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

    public function create(array $data, $user)
    {
        try {
            $this->conn->beginTransaction();
            $data['payrollScheme']['clientId'] = CLIENT_ID;
            $data['payrollScheme']['payrollSchemeId'] = createGuid();
            $data['payrollScheme']['createdBy'] = $user->getId();
            $this->payrollScheme->insert($data['payrollScheme']);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $payrollSchemeId = $data['payrollSchemeId'];
        try {
            $this->conn->beginTransaction();
            $this->payrollScheme->update(
                $data['payrollScheme'], 
                [
                    'payrollSchemeId' => $payrollSchemeId, 
                    'clientId' => CLIENT_ID
                ]
            );
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $payrollSchemeId)
    {
        try {
            $this->conn->beginTransaction();
            $this->payrollScheme->delete(['payrollSchemeId' => $payrollSchemeId, 'clientId' => CLIENT_ID]);
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
