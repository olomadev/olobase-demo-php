<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ExchangeRateFindOneResultVM
{
    /**
     * @var object
     * @OA\Property(
     *     ref="#/components/schemas/ExchangeRateFindOne",
     * )
     */
    public $data;
}
