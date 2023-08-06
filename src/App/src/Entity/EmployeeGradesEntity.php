<?php

namespace App\Entity;

/**
 * @table employeeGrades
 */
class EmployeeGradesEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'employeeGrades';
    /**
     * @var char(36)
     */
    public $gradeId;
    /**
     * @var varchar(150)
     */
    public $gradeName;
}
