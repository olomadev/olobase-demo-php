<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateListItem
{
    /**
     * @var string
     * @OA\Property()
     */
    public $exchangeRateDate;
    /**
     * @var number
     * @OA\Property()
     */
    public $usdExchangeRate;
    /**
     * @var number
     * @OA\Property()
     */
    public $euroExchangeRate;
}
