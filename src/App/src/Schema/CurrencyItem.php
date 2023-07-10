<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class CurrencyItem
{
    /**
     * @var string
     * @OA\Property()
     */
    public $id;
    /**
     * @var string
     * @OA\Property()
     */
    public $name;
}
