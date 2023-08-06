<?php

namespace App\Entity;

/**
 * @table jobTitleList
 */
class JobTitleListEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'jobTitleList';
    /**
     * @var char(36)
     */
    public $jobTitleListId;
    /**
     * @var char(4)
     */
    public $yearId;
    /**
     * @var varchar(150)
     */
    public $listName;
}
