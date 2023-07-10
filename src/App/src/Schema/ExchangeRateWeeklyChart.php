<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateWeeklyChart
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
}
