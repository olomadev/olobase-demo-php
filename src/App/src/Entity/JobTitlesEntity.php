<?php

namespace App\Entity;

/**
 * @table jobTitles
 */
class JobTitlesEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'jobTitles';
    /**
     * @var varchar(36)
     */
    public $jobTitleId;
    /**
     * @var varchar(255)
     */
    public $jobTitleName;
}
