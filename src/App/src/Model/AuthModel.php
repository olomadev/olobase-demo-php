<?php
declare(strict_types=1);

namespace App\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class AuthModel
{
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Find all permissions before the login 
     * 
     * @return array
     */
    public function findAllPermissions() : array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['p' => 'permissions']);
        $select->columns([
            'resource',
            'action',
        ]);
        $select->join(['rp' => 'rolePermissions'], 'rp.permId = p.permId', [], $select::JOIN_LEFT);
        $select->join(['r' => 'roles'], 'r.roleId = rp.roleId', ['name' => 'roleKey'], $select::JOIN_LEFT);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        // echo $select->getSqlString($adapter->getPlatform());
        // die;
        $permissions = array();
        foreach ($resultSet as $row) {
            if (! empty($row['resource']) && ! empty($row['action'])) {
                $permissions[$row['resource']][$row['name']][] = $row['action'];    
            }
        }
        return $permissions;
    }

    /**
     * Find user roles after login
     * 
     * @param  string $userId user id
     * @return array
     */
    public function findRolesById(string $userId) : array
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(['r' => 'roles']);
        $select->columns([
            'roleKey',
        ]);
        $select->join(['ru' => 'userRoles'], 'r.roleId = ru.roleId', ['userId'], $select::JOIN_LEFT);
        $select->where(['ru.userId' => $userId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        // echo $select->getSqlString($adapter->getPlatform());
        // die;
        $roles = array();
        foreach ($resultSet as $row) {
            $roles[] = $row['roleKey'];
        }
        return $roles;
    }


}
