<?php

namespace App\Entity;

/**
 * @table employees
 */
class EmployeesEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'employees';
    /**
     * @var char(36)
     */
    public $employeeId;
    /**
     * @var varchar(20)
     */
    public $employeeNumber;
    /**
     * @var varchar(36)
     */
    public $companyId;
    /**
     * @var varchar(60)
     */
    public $name;
    /**
     * @var varchar(60)
     */
    public $surname;
    /**
     * @var char(36)
     */
    public $jobTitleId;
    /**
     * @var char(36)
     */
    public $gradeId;
    /**
     * @var date
     */
    public $employmentStartDate;
    /**
     * @var date
     */
    public $employmentEndDate;
    /**
     * @var datetime
     */
    public $createdAt;
}
