<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class ConfirmedPurchaserItem
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
