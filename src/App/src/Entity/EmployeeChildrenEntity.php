<?php

namespace App\Entity;

/**
 * @table employeeChildren
 */
class EmployeeChildrenEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'employeeChildren';
    /**
     * @var char(36)
     */
    public $employeeId;
    /**
     * @var char(36)
     */
    public $childId;
    /**
     * @var varchar(120)
     */
    public $childName;
    /**
     * @var date
     */
    public $childBirthdate;
}
