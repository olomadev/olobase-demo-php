<?php

namespace App\Traits;

trait ExportSqlTrait
{  
    /**
     * Added for excel export feature
     */
    protected function setListSql($select)
    {
        $sqlString = $select->getSqlString($this->adapter->getPlatform());
        $this->cache->setItem(__CLASS__.'_listSql', $sqlString);
    }

    /**
     * Added for excel export feature
     */
    public function getListSql() : string
    {
        return $this->cache->getItem(__CLASS__.'_listSql');
    }

    /**
     * Added for excel export feature
     */
    public function executeListSql()
    {
        $sql = $this->getListSql();
        $statement = $this->adapter->createStatement($sql);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }
  
}