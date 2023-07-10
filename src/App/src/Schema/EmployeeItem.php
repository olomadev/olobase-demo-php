<?php

namespace App\Schema;

/**
 * @OA\Schema()
 */
class EmployeeItem
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
