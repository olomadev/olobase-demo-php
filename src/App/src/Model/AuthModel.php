<?php
declare(strict_types=1);

namespace App\Model;

use function generateRandomNumber;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Cache\Storage\StorageInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class AuthModel
{
    private $conn;
    private $users;
    private $adapter;
    private $simpleCache;

    public function __construct(
        AdapterInterface $adapter,
        SimpleCacheInterface $sipmleCache,
        TableGatewayInterface $users
    )
    {
        $this->users = $users;
        $this->adapter = $adapter;
        $this->simpleCache = $sipmleCache;
        $this->conn = $this->adapter->getDriver()->getConnection();
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

    /**
     * Generate reset password code
     * 
     * @param  string $username email
     * @return string
     */
    public function generateResetPassword(string $username) : string
    {
        $resetPasswordCode = generateRandomNumber(6);
        $this->simpleCache->set((string)$resetPasswordCode, $username, 600);
        return $resetPasswordCode;
    }

    /**
     * Find one user by username to create reset password template
     * 
     * @param  string $username email
     * @return array
     */
    public function findOneByUsername(string $username)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'userId',
                'userId',
                'firstname',
                'lastname',
                'email',
                'active',
                'themeColor',
            ]
        );
        $select->from('users');
        $select->where(['email' => $username]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();
        return $row;
    }

    /**
     * Reset user password with reset code
     * 
     * @param  string $resetCode   the reset code emailed to user
     * @param  string $newPassword new user password
     * @return void
     */
    public function resetPassword(string $resetCode, string $newPassword)
    {
        $username = $this->simpleCache->get($resetCode);
        if ($username) {
            $password = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 10]);
            try {
                $this->conn->beginTransaction();
                $this->users->update(['password' => $password, 'emailActivation' => 1], ['username' => $username]);
                $this->conn->commit();
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        }
    }

}
