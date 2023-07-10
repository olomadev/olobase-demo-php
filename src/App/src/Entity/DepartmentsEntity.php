<?php

namespace App\Entity;

/**
 * @table departments
 */
class DepartmentsEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(36)
     */
    public $departmentId;
    /**
     * @var char(36)
     */
    public $companyId;
    /**
     * @var char(4)
     */
    public $yearId;
    /**
     * @var varchar(100)
     */
    public $departmentName;
    /**
     * @var varchar(100)
     */
    public $subDepartmentName;
    /**
     * @var varchar(100)
     */
    public $managerName;
    /**
     * @var varchar(100)
     */
    public $managerSurname;
    /**
     * @var varchar(2)
     */
    public $managerPhoneAreaCodeId;
    /**
     * @var varchar(16)
     */
    public $managerPhone;
    /**
     * @var varchar(2)
     */
    public $managerMobileAreaCodeId;
    /**
     * @var varchar(16)
     */
    public $managerMobile;
    /**
     * @var varchar(100)
     */
    public $managerEmail;
}
