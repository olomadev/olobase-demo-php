<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateSave
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
     * @var number
     * @OA\Property()
     */
    public $poundExchangeRate;
}
