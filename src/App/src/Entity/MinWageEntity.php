<?php

namespace App\Entity;

/**
 * @table minWage
 */
class MinWageEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(4)
     */
    public $yearId;
    /**
     * @var char(36)
     */
    public $wageId;
    /**
     * @var varchar(2)
     */
    public $monthId;
    /**
     * @var decimal(10,2)
     */
    public $daily;
    /**
     * @var decimal(10,2)
     */
    public $monthly;
}
