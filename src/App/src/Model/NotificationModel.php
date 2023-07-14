<?php

namespace App\Model;

use Exception;
use Oloma\Php\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class NotificationModel
{
    private $conn;
    private $adapter;
    private $notifications;
    private $notificationUsers;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $notifications,
        TableGatewayInterface $notificationUsers,
        ColumnFiltersInterface $columnFilters
    )
    {
        $this->notifications = $notifications;
        $this->notificationUsers = $notificationUsers;
        $this->adapter = $notifications->getAdapter();
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function findAll()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'notifyId',
            'notifyName',
            'dateId',
            'days',
            'dayType',
            'sameDay',
            'atTime',
            'notifyType',
            'active',
            'createdAt',
        ]);
        $select->from(['n' => 'notifications']);
        $select->join(['nm' => 'notificationModules'], 'n.moduleId = nm.moduleId',
            [
                'moduleName'
            ],
        $select::JOIN_LEFT);
        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setColumns([
            'notifyName',
            'dateId',
            'days',
            'dayType',
            'atTime',
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
            $select->order($this->columnFilters->getOrderData());
        } else {
            $select->order(['notifyName ASC']);
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

    public function findOneById(string $notifyId)
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'notifyId',
            'notifyName',
            'moduleId',
            'dateId',
            'days',
            'dayType',
            'sameDay',
            'atTime',
            'notifyType',
            'active',
            'message',
            'createdAt',
        ]);
        $select->from(['n' => 'notifications']);
        $select->where(['n.notifyId' => $notifyId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // notification users
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(['id' => 'userId']);
        $select->from(['nu' => 'notificationUsers']);
        $select->where(['nu.notifyId' => $notifyId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row['users'] = iterator_to_array($resultSet);
        return $row;
    }    

    public function create(array $data)
    {
        $notifyId = $data['notifyId'];
        if ($data['notifications']['sameDay']) {
            $data['notifications']['days'] = 0;
        }
        try {
            $this->conn->beginTransaction();
            $data['notifications']['notifyId'] = $notifyId;
            $data['notifications']['createdAt'] = date('Y-m-d H:i:s');
            $this->notifications->insert($data['notifications']);
            foreach($data['users'] as $user) {
                $this->notificationUsers->insert(['userId' => $user['id'], 'notifyId' => $notifyId]);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $notifyId = $data['notifyId'];
        if ($data['notifications']['sameDay']) {
            $data['notifications']['days'] = 0;
        }
        try {
            $this->conn->beginTransaction();
            $this->notifications->update($data['notifications'], ['notifyId' => $notifyId]);
            $this->notificationUsers->delete(['notifyId' => $notifyId]);
            foreach ($data['users'] as $user) {
                $this->notificationUsers->insert(['userId' => $user['id'], 'notifyId' => $notifyId]);
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $notifyId)
    {
        try {
            $this->conn->beginTransaction();
            $this->notifications->delete(['notifyId' => $notifyId]);
            $this->notificationUsers->delete(['notifyId' => $notifyId]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

}
