<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class CountryItem
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
