<?php

namespace App\Entity;

/**
 * @table payrollScheme
 */
class PayrollSchemeEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var char(36)
     */
    public $payrollSchemeId;
    /**
     * @var char(36)
     */
    public $companyId;
    /**
     * @var char(36)
     */
    public $workplaceId;
    /**
     * @var varchar(255)
     */
    public $schemeName;
    /**
     * @var varchar(255)
     */
    public $schemeDescription;
    /**
     * @var date
     */
    public $startDate;
    /**
     * @var date
     */
    public $endDate;
}
