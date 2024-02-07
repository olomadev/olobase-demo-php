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
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Olobase\Mezzio\Authorization\PermissionModelInterface;

class PermissionModel implements PermissionModelInterface
{
    private $conn;
    private $cache;
    private $adapter;
    private $permissions;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $permissions,
        StorageInterface $cache,
        ColumnFiltersInterface $columnFilters
    )
    {
        $this->cache = $cache;
        $this->permissions = $permissions;
        $this->adapter = $permissions->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllPermissions()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'permId',
            'moduleName',
            'resource',
            'action',
            'route',
            'method',
        ]);
        $select->from(['p' => 'permissions']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $permissions = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        return $permissions;
    }

    public function findAll()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'permId',
            // 'groupName' => 'moduleName',
            'action' => new Expression("JSON_OBJECT('id', p.action, 'name', p.action)"),
            'method' => new Expression("JSON_OBJECT('id', p.method, 'name', p.method)"),
            'moduleName',
            'resource',
            'route',
        ]);
        $select->from(['p' => 'permissions']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'moduleName',
            'resource',
            'action',
            'route',
            'method',
        ]);
        $this->columnFilters->setSelect($select);
        $this->columnFilters->setData($get);

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
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order(new Expression($order));
            }
        } else {
            $select->order(['moduleName ASC', 'route ASC']);
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

    public function findOneById(string $roleId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'roleId',
            'roleName',
        ]);
        $select->from(['r' => 'roles']);
        $select->where(['r.roleId' => $roleId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // role permissions
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            'permId',
            'moduleName',
            'resource',
            'action',
            'route',
            'method',
        );
        $select->from(['p' => 'permissions']);
        $select->join(['rp' => 'rolePermissions'], 'p.permId = rp.permId',
            [],
        $select::JOIN_LEFT);
        $select->where(['rp.roleId' => $roleId]);
         
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $rolePermissions = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();

        $row['rolePermissions'] = $rolePermissions;
        return $row;
    }    

    public function findPermissions() : array
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $adapter = $this->permissions->getAdapter();
        $select  = $this->permissions->getSql()->select();
        $select->columns([
            'permId',
            'route',
            'method',
            'action',
        ]);
        $select->join(
            ['rp' => 'rolePermissions'],
            'permissions.permId = rp.permId', [], $select::JOIN_INNER);
        $select->join(
            ['r' => 'roles'],
            'r.roleId = rp.roleId', ['roleKey','roleLevel'], $select::JOIN_LEFT);
        
        // echo $select->getSqlString($adapter->getPlatform());
        // die;
        $resultSet = $this->permissions->selectWith($select);
        $results = array();
        foreach ($resultSet as $row) {
            $results[$row['roleKey']][] = $row['route'].'^'.$row['method'];
        }
        if (! empty($results)) {
            $this->cache->setItem($key, $results);
        }
        return $results;
    }

    public function create(array $data)
    {
        try {
            $this->conn->beginTransaction();
            $data['permissions']['permId'] = $data['id'];
            $this->permissions->insert($data['permissions']);
            $this->deleteCache();
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
            $this->permissions->update($data['permissions'], ['permId' => $data['id']]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function copy(string $permId) : array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['r' => 'permissions']);
        $select->where(['permId' => $permId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        $post = [];
        if ($row) {
            $post = [
                'id' => createGuid(), // create new id
                'moduleName' => $row['moduleName'],
                'resource' => $row['resource'],
                'action' => ['id' => $row['action']],
                'route' => $row['route'],
                'method' => ['id' => $row['method']],
            ];
        }
        return $post;
    }

    public function delete(string $permId)
    {
        try {
            $this->conn->beginTransaction();
            $this->permissions->delete(['permId' => $permId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function deleteCache()
    {
        $this->cache->removeItem(CACHE_ROOT_KEY.Self::class.':findPermissions');
    }

}
