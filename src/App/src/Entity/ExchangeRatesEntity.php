<?php

namespace App\Entity;

/**
 * @table settings
 */
class ExchangeRatesEntity
{
    const ENTITY_TYPE = 'null';
    /**
     * @var int(5) unsigned zerofill
     */
    public $clientId;
    /**
     * @var datetime
     */
    public $exchangeRateDate;
    /**
     * @var decimal(10,4)
     */
    public $usdExchangeRate;
    /**
     * @var decimal(10,4)
     */
    public $euroExchangeRate;
    /**
     * @var decimal(10,4)
     */
    public $poundExchangeRate;
}
