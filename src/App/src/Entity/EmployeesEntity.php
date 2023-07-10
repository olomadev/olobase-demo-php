<?php

namespace App\Entity;

/**
 * @table employees
 */
class EmployeesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(36)
     */
    public $employeeListId;
    /**
     * @var char(36)
     */
    public $employeeId;
    /**
     * @var char(20)
     */
    public $employeeNumber;
    /**
     * @var varchar(36)
     */
    public $companyId;
    /**
     * @var varchar(36)
     */
    public $workplaceId;
    /**
     * @var varchar(60)
     */
    public $name;
    /**
     * @var varchar(60)
     */
    public $middleName;
    /**
     * @var varchar(60)
     */
    public $surname;
    /**
     * @var varchar(60)
     */
    public $secondSurname;
    /**
     * @var char(11)
     */
    public $tckn;
    /**
     * @var char(36)
     */
    public $jobTitleId;
    /**
     * @var char(36)
     */
    public $gradeId;
    /**
     * @var char(36)
     */
    public $departmentId;
    /**
     * @var char(36)
     */
    public $costCenterId;
    /**
     * @var date
     */
    public $employmentStartDate;
    /**
     * @var date
     */
    public $employmentEndDate;
    /**
     * @var char(36)
     */
    public $disabilityId;
    /**
     * @var char(15)
     */
    public $employeeTypeId;
    /**
     * @var enum('white','blue')
     */
    public $employeeProfile;
}
