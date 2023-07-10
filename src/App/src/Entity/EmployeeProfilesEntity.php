<?php

namespace App\Entity;

/**
 * @table employeeProfiles
 */
class EmployeeProfilesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $cleintId;
    /**
     * @var char(36)
     */
    public $profileId;
    /**
     * @var varchar(40)
     */
    public $profileName;
}
