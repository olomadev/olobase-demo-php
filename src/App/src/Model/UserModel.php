<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Oloma\Php\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class UserModel
{
    private $conn;
    private $adapter;
    private $users;
    private $userRoles;
    private $userAvatars;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $users,
        TableGatewayInterface $userRoles,
        TableGatewayInterface $userAvatars,
        ColumnFiltersInterface $columnFilters,
        StorageInterface $cache
    ) {
        $this->adapter = $users->getAdapter();
        $this->users = $users;
        $this->userRoles = $userRoles;
        $this->userAvatars = $userAvatars;
        $this->columnFilters = $columnFilters;
        $this->cache = $cache;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAllBySelect()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'userId',
            'firstname',
            'lastname',
            'email',
            'active',
            'createdAt',
        ]);
        $select->from(['u' => 'users']);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAllBySelect();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'firstname',
            'lastname',
            'email',
            'active',
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'firstname',
                'lastname',
                'email',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'active',
            ]
        );
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
        // start date filters
        // 
        $this->columnFilters->setDateFilter('createdAt');
        // end date filters
        // 
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

    public function findOneById(string $userId)
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
                'emailActivation',
                'active',
                'themeColor',
                'lastLogin',
                'createdAt',
            ]
        );
        $select->from(['u' => 'users']);
        $select->where(['u.userId' => $userId]);
        $select->join(['ua' => 'userAvatars'], 'ua.userId = u.userId',
            [
                'avatarImage'
            ],
        $select::JOIN_LEFT);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // user roles
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'roleId',
            ]
        );
        $select->from('userRoles');
        $select->join(['r' => 'roles'], 'r.roleId = userRoles.roleId',
            [
                'name' => 'roleName'
            ],
        $select::JOIN_LEFT);
        $select->where(['userId' => $userId]);
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $userRoles = iterator_to_array($resultSet);

        $newUserRoles = array();
        foreach ($userRoles as $key => $val) {
            $newUserRoles[$key] = ["id" => $val['id'], "name" => $val['name']];
        }
        $row['userRoles'] = $newUserRoles;

        $statement->getResource()->closeCursor();
        return $row;
    }

    public function create(array $data)
    {
        // decode base64 image if exists
        //
        $avatarImageBlob = null;
        if (! empty($data['users']['avatarImage'])) {
            $avatarImageBlob = base64_decode($data['users']['avatarImage']);
        }
        $userId = $data['users']['userId'] = $data['userId'];
        try {
            $this->conn->beginTransaction();
            $this->users->insert($data['users']);
            if (! empty($data['userRoles'])) {
                foreach ($data['userRoles'] as $val) {
                    $this->userRoles->insert(['userId' => $userId, 'roleId' => $val['id']]);
                }
            }
            if ($avatarImageBlob) {
                $this->userAvatars->insert(['userId' => $userId, 'avatarImage' => $avatarImageBlob]);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        // decode base64 image if exists
        //
        $avatarImageBlob = null;
        if (! empty($data['users']['avatarImage'])) {
            $avatarImageBlob = base64_decode($data['users']['avatarImage']);
        }
        unset($data['users']['avatarImage']); // remove it from insert array

        $userId = $data['userId'];
        try {
            $this->conn->beginTransaction();
            if (! empty($data['users']['password'])) {
                $data['users']['password'] = password_hash($data['users']['password'], PASSWORD_DEFAULT, ['cost' => 10]);
            } else {
                unset($data['users']['password']);
            }
            $data['users']['updatedAt'] = date('Y-m-d H:i:s');
            $this->users->update($data['users'], ['userId' => $userId]);
            if (! empty($data['userRoles'])) {
                $this->userRoles->delete(['userId' => $userId]);
                foreach ($data['userRoles'] as $val) {
                    $this->userRoles->insert(['userId' => $userId, 'roleId' => $val['id']]);
                }
            }
            $this->userAvatars->delete(['userId' => $userId]);
            if ($avatarImageBlob) {
                $this->userAvatars->insert(['userId' => $userId, 'avatarImage' => $avatarImageBlob]);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $userId)
    {
        try {
            $this->conn->beginTransaction();
            $this->users->delete(['userId' => $userId]);
            $this->userRoles->delete(['userId' => $userId]);
            $this->userAvatars->delete(['userId' => $userId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function updatePasswordById(string $userId, string $newPassword)
    {
        $password = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => 10]);
        try {
            $this->conn->beginTransaction();
            $this->users->update(['password' => $password], ['userId' => $userId]);
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
