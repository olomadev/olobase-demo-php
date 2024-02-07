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

class RoleModel
{
    private $conn;
    private $roles;
    private $cache;
    private $rolePermissions;
    private $adapter;
    private $columnFilters;

    /**
     * Constructor
     * 
     * @param TableGatewayInterface $roles object
     * @param TableGatewayInterface $rolePermissions object
     * @param StorageInterface $cache object
     * @param ColumnFilters object
     */
    public function __construct(
        TableGatewayInterface $roles,
        TableGatewayInterface $rolePermissions,
        StorageInterface $cache,
        ColumnFiltersInterface $columnFilters
    )
    {
        $this->roles = $roles;
        $this->rolePermissions = $rolePermissions;
        $this->cache = $cache;
        $this->adapter = $roles->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    /**
     * Find roles for role selectbox
     * 
     * @return array
     */
    public function findRoles()
    {
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'roleId',
                'name' => 'roleName'
            ]
        );
        $select->from(['r' => 'roles']);
        $select->order(['roleLevel ASC']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        if (! empty($results)) {
            $this->cache->setItem($key, $results);    
        }
        return $results;
    }

    /**
     * Find one role by key
     * 
     * @param  string $roleKey string
     * @return array
     */
    public function findOneByKey(string $roleKey)
    {
        $key = Self::class.':'.__FUNCTION__.':'.$roleKey;
        if ($this->cache->hasItem($key)) {
            return $this->cache->getItem($key);
        }
        $select = $this->roles->getSql()->select();
        $select->columns(['roleId', 'roleKey', 'roleLevel']);
        $select->where(['roleKey' => $roleKey]);
        $resultSet = $this->roles->selectWith($select);
        $row = $resultSet->current();
        $this->cache->setItem($key, $row);
        return $row;
    }

    public function findAllKeys()
    {
        $select = $this->roles->getSql()->select();
        $resultSet = $this->roles->selectWith($select);
        $data = array();
        foreach ($resultSet as $row) {
            $data[] = ['id' => $row['roleKey'], 'name' => $row['roleKey']];
        }
        return $data;
    }

    /**
     * Find all roles with levels
     *
     * @return array
     */
    public function findAllLevels() : array
    {
        $select = $this->roles->getSql()->select();
        $resultSet = $this->roles->selectWith($select);
        $levels = array();
        foreach ($resultSet as $row) {
            $levels[$row['roleKey']] = $row['roleLevel'];
        }
        return $levels;
    }

    /**
     * Find all roles (Do cache)
     *
     * @return array
     */
    public function findAll() : array
    {
        $select = $this->roles->getSql()->select();
        $resultSet = $this->roles->selectWith($select);
        $result = array();
        foreach ($resultSet as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'roleId',
            'roleKey',
            'roleName',
            'roleLevel',
        ]);
        $select->from(['r' => 'roles']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'roleKey',
            'roleName',
            'roleLevel',
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

    public function findOneById(string $roleId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'roleId',
            'roleKey',
            'roleName',
            'roleLevel',
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
            [
                'permId',
                'route',
                'action',
                'resource',
                'moduleName',
                'method',
            ]
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

    public function create(array $data)
    {
        $roleId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $data['roles']['roleId'] = $roleId;
            $this->roles->insert($data['roles']);

            $this->rolePermissions->delete(['roleId' => $roleId]);
            if (! empty($data['rolePermissions'])) {
                foreach ($data['rolePermissions'] as $val) {
                    $val['roleId'] = $roleId;
                    $this->rolePermissions->insert($val);
                }
            }
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $roleId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->roles->update($data['roles'], ['roleId' => $roleId]);
            $this->rolePermissions->delete(['roleId' => $roleId]);
            if (! empty($data['rolePermissions'])) {
                foreach ($data['rolePermissions'] as $val) {
                    $val['roleId'] = $roleId;
                    $this->rolePermissions->insert($val);
                }
            }
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $roleId)
    {
        try {
            $this->conn->beginTransaction();
            $this->roles->delete(['roleId' => $roleId]);
            $this->deleteCache();
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function deleteCache()
    {
        $this->cache->removeItem(CACHE_ROOT_KEY.Self::class.':findRoles');
        $this->cache->removeItem(CACHE_ROOT_KEY.\App\Model\PermissionModel::class.':findPermissions');
    }    

}
