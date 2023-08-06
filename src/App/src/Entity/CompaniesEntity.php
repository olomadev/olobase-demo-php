<?php

namespace App\Entity;

/**
 * @table companies
 */
class CompaniesEntity
{
    const ENTITY_TYPE = 'null';
    const TABLE_NAME = 'companies';
    /**
     * @var char(36)
     */
    public $companyId;
    /**
     * @var varchar(160)
     */
    public $companyName;
    /**
     * @var varchar(160)
     */
    public $companyShortName;
    /**
     * @var varchar(100)
     */
    public $taxOffice;
    /**
     * @var varchar(60)
     */
    public $taxNumber;
    /**
     * @var varchar(255)
     */
    public $address;
    /**
     * @var datetime
     */
    public $createdAt;
}
