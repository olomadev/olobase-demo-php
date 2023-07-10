<?php

namespace App\Entity;

/**
 * @table workplaces
 */
class WorkplacesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var varchar(36)
     */
    public $workplaceId;
    /**
     * @var varchar(36)
     */
    public $companyId;
    /**
     * @var varchar(255)
     */
    public $workplaceName;
    /**
     * @var varchar(100)
     */
    public $registrationNumber;
    /**
     * @var varchar(255)
     */
    public $address;
    /**
     * @var datetime
     */
    public $createdAt;
}
