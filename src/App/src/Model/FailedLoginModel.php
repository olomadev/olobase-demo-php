<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Laminas\Db\Sql\Sql;
use Oloma\Php\ColumnFiltersInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Predis\ClientInterface as Predis;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class FailedLoginModel
{
    private $conn;
    private $predis;
    private $adapter;
    private $message;
    private $failedLogins;
    private $columnFilters;

    public function __construct(
        TableGatewayInterface $failedLogins,
        Predis $predis,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->predis = $predis;
        $this->adapter = $failedLogins->getAdapter();
        $this->failedLogins = $failedLogins;
        $this->columnFilters = $columnFilters;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    public function checkUsername(string $username)
    {        
        $key = CACHE_ROOT_KEY.Self::class.':'.__FUNCTION__.':'.$username;
        if ($banMessage = $this->predis->get($key)) {
            $this->setMessage($banMessage);
            return true;
        }
        // find number of daily failed attempts of username 
        // 
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('failedLogins');
        $select->where(
            [
                'username' => $username,
                'attemptedAt' => date("Y-m-d"),
            ]
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $numberOfDailyAttempts = count($results);
        $statement->getResource()->closeCursor();

        $this->blockUsername($key, $numberOfDailyAttempts);
    }

    public function createAttempt(array $data)
    {
        // first check username exists in the database
        // 
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('users');
        $select->where(
            [
                'email' => $data['username'],
            ]
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();

        if (count($results) > 0) { // if it's exists insert attempt data
            try {
                $this->conn->beginTransaction();
                $this->failedLogins->insert($data);
                $this->conn->commit();
            } catch (Exception $e) {
                $this->conn->rollback();
                throw $e;
            }
        }
    }

    /**
     * Delete attempt events:
     *
     * 1- When user do the successful login
     * 2- When the user clicks on the reset link in the email we send
     * 
     * @param  string $username identity
     * @return void
     */
    public function deleteAttempts(string $username)
    {
        try {
            $this->conn->beginTransaction();
            $this->failedLogins->delete(['username' => $username]);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function blockUsername(string $key, $count = 0)
    {
        if ($count > 6) { // block user for 30 seconds
            $this->predis->set($key, 'BLOCK_30_SECONDS');
            $this->predis->expire($key, 30);
        }
        if ($count > 9) { // block user for 60 seconds
            $this->predis->set($key, 'BLOCK_60_SECONDS');
            $this->predis->expire($key, 60);
        }
        if ($count > 12) { // block user for 300 seconds
            $this->predis->set($key, 'BLOCK_300_SECONDS');
            $this->predis->expire($key, 60);
        }
        if ($count > 16) { // block user for 1800 seconds (30 minutes)
            $this->predis->set($key, 'BLOCK_1800_SECONDS');
            $this->predis->expire($key, 1800);
        }
        if ($count > 20) { // block user for 86400 seconds (1 day)
            $this->predis->set($key, 'BLOCK_86400_SECONDS');
            $this->predis->expire($key, 86400);
        }
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'loginId',
            'username',
            'attemptedAt',
            'userAgent',
            'ip',
        ]);
        $select->from(['f' => 'failedLogins']);
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
                'id' => 'loginId',
                'username',
                'attemptedAt',
                'userAgent',
                'ip',
            ]
        );
        $this->columnFilters->setLikeColumns(
            [
                'username',
                'userAgent',
                'ip',
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
        // date filters
        // 
        $this->columnFilters->setDateFilter('attemptedAt');

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

    public function findAllUsernames()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'username',
                'name' => 'username'
            ]
        );
        $select->from('failedLogins');
        $select->group(['username']);
        $select->order(['username ASC']);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findAllIpAdresses()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'ip',
                'name' => 'ip'
            ]
        );
        $select->from('failedLogins');
        $select->group(['ip']);
        $select->order(['ip ASC']);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

}