<?php

namespace App\Entity;

/**
 * @table employeeGrades
 */
class EmployeeGradesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(36)
     */
    public $gradeId;
    /**
     * @var varchar(150)
     */
    public $gradeName;
}
