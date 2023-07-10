<?php

namespace App\Entity;

/**
 * @table jobTitles
 */
class JobTitlesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var varchar(36)
     */
    public $jobTitleId;
    /**
     * @var varchar(255)
     */
    public $jobTitleName;
}
