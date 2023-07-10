<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class CompanyItem
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
