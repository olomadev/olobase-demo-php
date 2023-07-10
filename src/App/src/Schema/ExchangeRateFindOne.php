<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateFindOne
{
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
    /**
     * @var string
     * @OA\Property()
     */
    public $exchangeRateDate;
}
