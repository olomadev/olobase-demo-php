<?php

namespace App\Entity;

/**
 * @table disabilities
 */
class DisabilitiesEntity
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
    public $disabilityId;
    /**
     * @var varchar(2)
     */
    public $degree;
    /**
     * @var varchar(15)
     */
    public $description;
    /**
     * @var decimal(10,2)
     */
    public $discountAmount;
}
