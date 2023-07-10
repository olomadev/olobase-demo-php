<?php

namespace App\Entity;

/**
 * @table salaryList
 */
class SalaryListEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(36)
     */
    public $salaryListId;
    /**
     * @var char(4)
     */
    public $yearId;
    /**
     * @var varchar(150)
     */
    public $listName;
}
