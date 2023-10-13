<?php
declare(strict_types=1);

namespace App\Model;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class FileModel
{
    private $conn;
    private $files;
    private $adapter;

    public function __construct(
        AdapterInterface $adapter,        
        TableGatewayInterface $files
    )
    {
        $this->files = $files;
        $this->adapter = $adapter;
        $this->conn = $this->adapter->getDriver()->getConnection();
    }

    /**
     * Find one file by file id
     * 
     * @param  string $fileId
     * @return array
     */
    public function findOneById(string $fileId, string $tableName)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'data',
            ]
        );
        $select->from(['f' => 'files']);
        $select->join(
            ['x' => $tableName], 'x.fileId = f.fileId',
            [
                'id' => 'fileId',
                'name' => 'fileName',
                'size' => 'fileSize',
                'type' => 'fileType',
            ],
            $select::JOIN_INNER
        );
        $select->where(['f.fileId' => $fileId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        return  $row;
    }

}