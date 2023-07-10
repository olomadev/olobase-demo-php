<?php

namespace App\Entity;

/**
 * @table salaries
 */
class SalariesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(36)
     */
    public $salaryId;
    /**
     * @var char(36)
     */
    public $employeeId;
    /**
     * @var char(4)
     */
    public $yearId;
    /**
     * @var char(2)
     */
    public $monthId;
    /**
     * @var varchar(10)
     */
    public $paymentTypeParamId;
    /**
     * @var decimal(10,2)
     */
    public $amount;
    /**
     * @var varchar(2)
     */
    public $day;
}
